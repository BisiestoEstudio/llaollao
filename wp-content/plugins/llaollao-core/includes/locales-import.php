<?php
/**
 * Importador de locales desde el CSV de Perfiles de Empresa de Google.
 *
 * Vive en Locales → Importar CSV y trabaja en dos pasos: primero se sube el
 * archivo y se muestra un plan (qué se creará, qué se actualizará y qué se
 * enviará a la papelera), y solo al confirmar se toca la base de datos.
 *
 * La sincronización NO borra y recrea: reconoce cada local por su "Código de
 * tienda" y actualiza únicamente los campos que manda el CSV, de modo que lo
 * que se rellena desde WordPress (el campo "club", por ejemplo) sobrevive a las
 * importaciones.
 *
 * Para saber qué locales han desaparecido se usa "marcar y barrer": cada fila
 * procesada deja la marca de la ejecución en curso y, al terminar, lo que no
 * lleve esa marca es que ya no venía en el archivo. El barrido se limita a los
 * países presentes en el CSV —si no, subir el archivo de otro país se llevaría
 * por delante los de España— y solo alcanza a locales con código de tienda, es
 * decir, a los que gestiona el importador.
 */

defined( 'ABSPATH' ) || exit;

/** Clave del transitorio donde viaja el plan entre el paso 1 y el 2. */
const LLAO_IMPORT_PLAN = 'llao_locales_import_plan';

/** Si el barrido fuese a llevarse más de este porcentaje, se para y avisa. */
const LLAO_IMPORT_SWEEP_LIMIT = 0.2;

/** Segundos que se dedican como mucho a geocodificar en cada pasada. */
const LLAO_GEOCODE_BUDGET = 20;

/* -------------------------------------------------------------------------
 * Menú
 * ---------------------------------------------------------------------- */

add_action( 'admin_menu', 'llao_locales_import_menu' );

function llao_locales_import_menu() {
	add_submenu_page(
		'edit.php?post_type=local',
		__( 'Importar locales', 'llaollao-core' ),
		__( 'Importar CSV', 'llaollao-core' ),
		'manage_options',
		'llao-locales-import',
		'llao_locales_import_page'
	);
}

/* -------------------------------------------------------------------------
 * Lectura del CSV
 * ---------------------------------------------------------------------- */

/**
 * Localiza una columna por su nombre. Google cambia de vez en cuando el texto
 * exacto de las cabeceras, así que se prueba primero la coincidencia exacta y
 * después "que empiece por".
 */
function llao_csv_col( array $cabeceras, $nombre ) {
	foreach ( $cabeceras as $i => $c ) {
		if ( $c === $nombre ) {
			return $i;
		}
	}
	foreach ( $cabeceras as $i => $c ) {
		if ( 0 === strpos( $c, $nombre ) ) {
			return $i;
		}
	}
	return null;
}

/**
 * Lee el archivo y devuelve array( 'filas' => [...], 'error' => string ).
 */
function llao_csv_leer( $ruta ) {

	$contenido = file_get_contents( $ruta );
	if ( false === $contenido || '' === trim( $contenido ) ) {
		return array( 'error' => __( 'El archivo está vacío o no se ha podido leer.', 'llaollao-core' ) );
	}

	// Quitar el BOM que a veces mete Excel, o la primera cabecera no casaría.
	$contenido = preg_replace( '/^\xEF\xBB\xBF/', '', $contenido );

	// Excel en español reescribe estos archivos con punto y coma. Se decide por
	// mayoría en la primera línea.
	$primera   = strtok( $contenido, "\n" );
	$delimitador = substr_count( $primera, ';' ) > substr_count( $primera, ',' ) ? ';' : ',';

	$handle = fopen( 'php://temp', 'r+' );
	fwrite( $handle, $contenido );
	rewind( $handle );

	// El 5.º argumento (escape) va explícito: PHP 8.4 avisa de que su valor por
	// defecto cambia, y la cadena vacía es el comportamiento estándar de CSV.
	$cabeceras = fgetcsv( $handle, 0, $delimitador, '"', '' );
	if ( ! $cabeceras ) {
		fclose( $handle );
		return array( 'error' => __( 'No se han podido leer las cabeceras del archivo.', 'llaollao-core' ) );
	}
	$cabeceras = array_map( 'trim', $cabeceras );

	// Columnas imprescindibles.
	$idx = array(
		'codigo'    => llao_csv_col( $cabeceras, 'Código de tienda' ),
		'estado'    => llao_csv_col( $cabeceras, 'Estado' ),
		'direccion' => llao_csv_col( $cabeceras, 'Dirección (línea 1)' ),
		'municipio' => llao_csv_col( $cabeceras, 'Municipio' ),
		'pais'      => llao_csv_col( $cabeceras, 'País/territorio' ),
	);
	foreach ( $idx as $clave => $pos ) {
		if ( null === $pos ) {
			fclose( $handle );
			return array(
				'error' => sprintf(
					/* translators: %s: nombre interno de la columna que falta. */
					__( 'El archivo no tiene la columna obligatoria «%s». ¿Seguro que es la exportación de Perfiles de Empresa?', 'llaollao-core' ),
					$clave
				),
			);
		}
	}

	// Opcionales: si faltan, ese dato sencillamente no se importa.
	$idx['area']    = llao_csv_col( $cabeceras, 'Área administrativa' );
	$idx['cp']      = llao_csv_col( $cabeceras, 'Código postal' );
	$idx['banos']   = llao_csv_col( $cabeceras, 'Servicios: Aseos (' );
	$idx['terraza'] = llao_csv_col( $cabeceras, 'Opciones de servicio: Terraza' );

	$dias_csv = array(
		'lunes'     => 'Horario de lunes',
		'martes'    => 'Horario de martes',
		'miercoles' => 'Horario de miércoles',
		'jueves'    => 'Horario de jueves',
		'viernes'   => 'Horario de viernes',
		'sabado'    => 'Horario de sábado',
		'domingo'   => 'Horario de domingo',
	);
	foreach ( $dias_csv as $dia => $cabecera ) {
		$idx[ 'h_' . $dia ] = llao_csv_col( $cabeceras, $cabecera );
	}

	$leer = function ( $fila, $pos ) {
		if ( null === $pos || ! isset( $fila[ $pos ] ) ) {
			return '';
		}
		return sanitize_text_field( trim( $fila[ $pos ] ) );
	};

	$filas    = array();
	$saltadas = array();

	while ( false !== ( $fila = fgetcsv( $handle, 0, $delimitador, '"', '' ) ) ) {

		$codigo = $leer( $fila, $idx['codigo'] );
		$estado = $leer( $fila, $idx['estado'] );

		if ( '' === $codigo ) {
			$saltadas[] = array(
				'nombre' => $leer( $fila, $idx['direccion'] ),
				'motivo' => __( 'sin código de tienda', 'llaollao-core' ),
			);
			continue;
		}

		// Los perfiles marcados como duplicados no se importan: son la misma
		// tienda repetida en Google.
		if ( 'Duplicada' === $estado ) {
			$saltadas[] = array(
				'nombre' => $codigo . ' · ' . $leer( $fila, $idx['direccion'] ),
				'motivo' => __( 'marcada como duplicada en Google', 'llaollao-core' ),
			);
			continue;
		}

		$horarios = array();
		foreach ( array_keys( $dias_csv ) as $dia ) {
			$horarios[ $dia ] = $leer( $fila, $idx[ 'h_' . $dia ] );
		}

		$filas[ $codigo ] = array(
			'codigo'    => $codigo,
			'direccion' => $leer( $fila, $idx['direccion'] ),
			'municipio' => $leer( $fila, $idx['municipio'] ),
			'area'      => $leer( $fila, $idx['area'] ),
			'cp'        => $leer( $fila, $idx['cp'] ),
			'pais'      => strtoupper( $leer( $fila, $idx['pais'] ) ),
			'banos'     => $leer( $fila, $idx['banos'] ),
			'terraza'   => $leer( $fila, $idx['terraza'] ),
			'horarios'  => $horarios,
		);
	}

	fclose( $handle );

	if ( ! $filas ) {
		return array( 'error' => __( 'El archivo no contiene ninguna fila aprovechable.', 'llaollao-core' ) );
	}

	return array( 'filas' => $filas, 'saltadas' => $saltadas );
}

/**
 * Dirección completa en una línea, tal y como se enviará a geocodificar.
 */
function llao_local_direccion_completa( array $fila ) {
	$partes = array_filter( array(
		$fila['direccion'],
		trim( $fila['cp'] . ' ' . $fila['municipio'] ),
		$fila['area'],
		$fila['pais'],
	) );
	return implode( ', ', $partes );
}

/* -------------------------------------------------------------------------
 * Países
 * ---------------------------------------------------------------------- */

/**
 * El CSV trae el país en ISO de dos letras. Los términos se guardan con ese
 * código como slug (estable) y con el nombre en español como etiqueta.
 */
function llao_pais_nombre( $iso ) {
	$mapa = array(
		'ES' => 'España',   'PT' => 'Portugal',    'IT' => 'Italia',
		'FR' => 'Francia',  'GB' => 'Reino Unido', 'DE' => 'Alemania',
		'NL' => 'Países Bajos', 'BE' => 'Bélgica', 'AD' => 'Andorra',
		'MA' => 'Marruecos', 'EG' => 'Egipto',     'AE' => 'Emiratos Árabes Unidos',
		'SA' => 'Arabia Saudí', 'QA' => 'Catar',   'KW' => 'Kuwait',
		'BH' => 'Baréin',   'OM' => 'Omán',        'JO' => 'Jordania',
		'MX' => 'México',   'CO' => 'Colombia',    'PE' => 'Perú',
		'CL' => 'Chile',    'AR' => 'Argentina',   'EC' => 'Ecuador',
		'SV' => 'El Salvador', 'GT' => 'Guatemala', 'PA' => 'Panamá',
		'CR' => 'Costa Rica', 'DO' => 'República Dominicana',
	);
	return $mapa[ $iso ] ?? $iso;
}

/**
 * Devuelve el term_id del país, creándolo si hace falta.
 */
function llao_pais_term_id( $iso ) {
	if ( '' === $iso ) {
		return 0;
	}

	$slug = strtolower( $iso );
	$term = get_term_by( 'slug', $slug, 'pais' );
	if ( $term ) {
		return (int) $term->term_id;
	}

	$nuevo = wp_insert_term( llao_pais_nombre( $iso ), 'pais', array( 'slug' => $slug ) );
	if ( is_wp_error( $nuevo ) ) {
		return 0;
	}
	return (int) $nuevo['term_id'];
}

/* -------------------------------------------------------------------------
 * Locales existentes
 * ---------------------------------------------------------------------- */

/**
 * Mapa de código de tienda => ID de local, para los locales que gestiona el
 * importador (los creados a mano no tienen código y quedan fuera).
 */
function llao_locales_por_codigo() {

	$ids = get_posts( array(
		'post_type'        => 'local',
		'post_status'      => array( 'publish', 'draft', 'pending', 'private' ),
		'numberposts'      => -1,
		'fields'           => 'ids',
		'suppress_filters' => false,
		'meta_query'       => array(
			array(
				'key'     => 'llao_local_codigo',
				'compare' => 'EXISTS',
			),
		),
	) );

	$mapa = array();
	foreach ( $ids as $id ) {
		$codigo = get_post_meta( $id, 'llao_local_codigo', true );
		if ( '' !== $codigo ) {
			$mapa[ $codigo ] = $id;
		}
	}
	return $mapa;
}

/* -------------------------------------------------------------------------
 * Plan (paso 1)
 * ---------------------------------------------------------------------- */

function llao_locales_plan( array $filas, array $saltadas ) {

	$existentes = llao_locales_por_codigo();

	$paises = array_values( array_unique( array_filter( wp_list_pluck( $filas, 'pais' ) ) ) );

	$crear       = array();
	$actualizar  = array();
	foreach ( $filas as $codigo => $fila ) {
		if ( isset( $existentes[ $codigo ] ) ) {
			$actualizar[] = $codigo;
		} else {
			$crear[] = $codigo;
		}
	}

	// Barrido: locales de los países del archivo que no vienen en él.
	$papelera  = array();
	$en_ambito = 0;
	foreach ( $existentes as $codigo => $id ) {

		$terminos = wp_get_object_terms( $id, 'pais', array( 'fields' => 'slugs' ) );
		$de_estos_paises = false;
		foreach ( (array) $terminos as $slug ) {
			if ( in_array( strtoupper( $slug ), $paises, true ) ) {
				$de_estos_paises = true;
				break;
			}
		}
		if ( ! $de_estos_paises ) {
			continue;
		}

		$en_ambito++;
		if ( ! isset( $filas[ $codigo ] ) ) {
			$papelera[] = array(
				'id'        => $id,
				'titulo'    => get_the_title( $id ),
				'direccion' => get_post_meta( $id, 'llao_local_direccion', true ),
			);
		}
	}

	// Tope de seguridad: un CSV recortado por error no debe vaciar el listado.
	$supera_tope = $en_ambito > 0 && ( count( $papelera ) / $en_ambito ) > LLAO_IMPORT_SWEEP_LIMIT;

	return array(
		'filas'       => $filas,
		'saltadas'    => $saltadas,
		'paises'      => $paises,
		'crear'       => $crear,
		'actualizar'  => $actualizar,
		'papelera'    => $papelera,
		'en_ambito'   => $en_ambito,
		'supera_tope' => $supera_tope,
	);
}

/* -------------------------------------------------------------------------
 * Ejecución (paso 2)
 * ---------------------------------------------------------------------- */

function llao_locales_importar( array $plan, $forzar = false ) {

	if ( $plan['supera_tope'] && ! $forzar ) {
		return array( 'error' => __( 'El barrido superaría el tope de seguridad. Revisa el archivo o marca la casilla para continuar de todos modos.', 'llaollao-core' ) );
	}

	$ejecucion = (string) time();
	$creados   = 0;
	$actualizados = 0;
	$pendientes_geo = 0;

	// Se consulta una sola vez y se va completando con lo que se cree: dentro
	// del bucle serían 185 consultas.
	$existentes = llao_locales_por_codigo();

	foreach ( $plan['filas'] as $codigo => $fila ) {

		$direccion = llao_local_direccion_completa( $fila );
		$existente = $existentes[ $codigo ] ?? 0;

		if ( ! $existente ) {
			// El título se compone al crear y no se vuelve a tocar: si alguien
			// lo ajusta a mano desde el escritorio, la importación lo respeta.
			$titulo = trim( $fila['municipio'] . ' — ' . $fila['direccion'] );
			$id = wp_insert_post( array(
				'post_type'   => 'local',
				'post_status' => 'publish',
				'post_title'  => $titulo ?: $codigo,
			), true );

			if ( is_wp_error( $id ) ) {
				continue;
			}
			update_post_meta( $id, 'llao_local_codigo', $codigo );
			$existentes[ $codigo ] = $id;
			$creados++;
		} else {
			$id = $existente;
			$actualizados++;
		}

		// Campos que manda el CSV.
		update_post_meta( $id, 'llao_local_direccion', $direccion );
		foreach ( $fila['horarios'] as $dia => $valor ) {
			update_post_meta( $id, 'llao_local_horario_' . $dia, $valor );
		}

		// Baños y terraza solo se escriben si Google dice sí o no. Un valor
		// vacío o "[NO APLICABLE]" significa "no informado", y machacar con eso
		// borraría lo que se hubiera puesto a mano.
		foreach ( array( 'banos' => 'banos', 'terraza' => 'terraza' ) as $csv => $meta ) {
			if ( 'Sí' === $fila[ $csv ] ) {
				update_post_meta( $id, 'llao_local_' . $meta, true );
			} elseif ( 'No' === $fila[ $csv ] ) {
				update_post_meta( $id, 'llao_local_' . $meta, false );
			}
		}

		// País.
		$term_id = llao_pais_term_id( $fila['pais'] );
		if ( $term_id ) {
			wp_set_object_terms( $id, array( $term_id ), 'pais', false );
		}

		// Geocodificación pendiente solo si es nuevo o si la dirección cambió,
		// para no volver a pagar por lo que ya está resuelto.
		$hash    = md5( $direccion );
		$antiguo = get_post_meta( $id, '_llao_geo_hash', true );
		$lat     = get_post_meta( $id, 'llao_local_lat', true );
		if ( $hash !== $antiguo || '' === $lat ) {
			update_post_meta( $id, '_llao_geo_pending', 1 );
			$pendientes_geo++;
		}

		// La marca de esta ejecución: lo que no la lleve, se barre.
		update_post_meta( $id, '_llao_import_run', $ejecucion );
	}

	// Barrido.
	$a_papelera = 0;
	foreach ( $plan['papelera'] as $item ) {
		if ( wp_trash_post( $item['id'] ) ) {
			$a_papelera++;
		}
	}

	return array(
		'creados'        => $creados,
		'actualizados'   => $actualizados,
		'papelera'       => $a_papelera,
		'saltadas'       => count( $plan['saltadas'] ),
		'pendientes_geo' => $pendientes_geo,
	);
}

/* -------------------------------------------------------------------------
 * Geocodificación
 * ---------------------------------------------------------------------- */

/**
 * La clave sale primero de wp-config.php y, si no está, de los ajustes.
 */
function llao_geocoding_key() {
	if ( defined( 'LLAO_GEOCODING_API_KEY' ) && LLAO_GEOCODING_API_KEY ) {
		return LLAO_GEOCODING_API_KEY;
	}
	return (string) get_option( 'llao_geocoding_api_key', '' );
}

function llao_geocode_pendientes() {
	return (int) count( get_posts( array(
		'post_type'   => 'local',
		'post_status' => array( 'publish', 'draft', 'pending', 'private' ),
		'numberposts' => -1,
		'fields'      => 'ids',
		'meta_query'  => array(
			array( 'key' => '_llao_geo_pending', 'compare' => 'EXISTS' ),
		),
	) ) );
}

/**
 * Procesa pendientes hasta agotar el presupuesto de tiempo. Devuelve cuántos ha
 * resuelto, cuántos han fallado y cuántos quedan.
 */
function llao_geocode_lote() {

	$clave = llao_geocoding_key();
	if ( '' === $clave ) {
		return array( 'error' => __( 'Falta la clave de la Geocoding API.', 'llaollao-core' ) );
	}

	$ids = get_posts( array(
		'post_type'   => 'local',
		'post_status' => array( 'publish', 'draft', 'pending', 'private' ),
		'numberposts' => -1,
		'fields'      => 'ids',
		'meta_query'  => array(
			array( 'key' => '_llao_geo_pending', 'compare' => 'EXISTS' ),
		),
	) );

	$inicio    = microtime( true );
	$resueltos = 0;
	$fallidos  = array();

	foreach ( $ids as $id ) {

		if ( ( microtime( true ) - $inicio ) > LLAO_GEOCODE_BUDGET ) {
			break;
		}

		$direccion = get_post_meta( $id, 'llao_local_direccion', true );
		if ( '' === $direccion ) {
			delete_post_meta( $id, '_llao_geo_pending' );
			continue;
		}

		$url = add_query_arg( array(
			'address' => rawurlencode( $direccion ),
			'key'     => $clave,
		), 'https://maps.googleapis.com/maps/api/geocode/json' );

		$respuesta = wp_remote_get( $url, array( 'timeout' => 10 ) );
		if ( is_wp_error( $respuesta ) ) {
			$fallidos[] = get_the_title( $id ) . ' — ' . $respuesta->get_error_message();
			continue;
		}

		$cuerpo = json_decode( wp_remote_retrieve_body( $respuesta ), true );
		$estado = $cuerpo['status'] ?? 'ERROR';

		// Google explica el motivo real en error_message (clave restringida por
		// dominio, API sin activar, facturación sin habilitar…). Sin ese texto,
		// el estado a secas no dice qué hay que arreglar.
		$detalle = $cuerpo['error_message'] ?? '';

		// Si se queja de cuota o de la clave, parar: seguir solo gasta tiempo y
		// repite el mismo error en cada local.
		if ( in_array( $estado, array( 'OVER_QUERY_LIMIT', 'REQUEST_DENIED' ), true ) ) {
			$fallidos[] = sprintf(
				/* translators: 1: estado devuelto por la API, 2: explicación de Google. */
				__( 'Google ha respondido «%1$s»: %2$s Se detiene la geocodificación.', 'llaollao-core' ),
				$estado,
				$detalle ?: __( '(sin detalle)', 'llaollao-core' )
			);
			break;
		}

		if ( 'OK' !== $estado || empty( $cuerpo['results'][0] ) ) {
			$fallidos[] = get_the_title( $id ) . ' — ' . $estado . ( $detalle ? ' — ' . $detalle : '' );
			delete_post_meta( $id, '_llao_geo_pending' );
			continue;
		}

		$r = $cuerpo['results'][0];
		update_post_meta( $id, 'llao_local_lat', (float) $r['geometry']['location']['lat'] );
		update_post_meta( $id, 'llao_local_lng', (float) $r['geometry']['location']['lng'] );

		// Se guarda la calidad del resultado: con direcciones tipo "C.C. Parque
		// Almenara" o "Autovía, Km. 760" Google devuelve un punto aproximado, y
		// así se pueden listar las dudosas para repasarlas.
		$precision = $r['geometry']['location_type'] ?? '';
		if ( ! empty( $r['partial_match'] ) ) {
			$precision .= ' (parcial)';
		}
		update_post_meta( $id, 'llao_local_geo_precision', $precision );
		update_post_meta( $id, '_llao_geo_hash', md5( $direccion ) );
		delete_post_meta( $id, '_llao_geo_pending' );
		$resueltos++;
	}

	return array(
		'resueltos' => $resueltos,
		'fallidos'  => $fallidos,
		'quedan'    => llao_geocode_pendientes(),
	);
}

/* -------------------------------------------------------------------------
 * Pantalla
 * ---------------------------------------------------------------------- */

function llao_locales_import_page() {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$aviso     = '';
	$resultado = null;
	$geo       = null;

	// --- Paso 1: subida y plan ---
	if ( isset( $_POST['llao_import_subir'] ) && check_admin_referer( 'llao_import_subir' ) ) {

		if ( empty( $_FILES['llao_csv']['tmp_name'] ) || ! is_uploaded_file( $_FILES['llao_csv']['tmp_name'] ) ) {
			$aviso = __( 'No se ha recibido ningún archivo.', 'llaollao-core' );
		} else {
			$leido = llao_csv_leer( $_FILES['llao_csv']['tmp_name'] );
			if ( isset( $leido['error'] ) ) {
				$aviso = $leido['error'];
			} else {
				$plan = llao_locales_plan( $leido['filas'], $leido['saltadas'] );
				set_transient( LLAO_IMPORT_PLAN, $plan, HOUR_IN_SECONDS );
			}
		}
	}

	// --- Paso 2: confirmación ---
	if ( isset( $_POST['llao_import_confirmar'] ) && check_admin_referer( 'llao_import_confirmar' ) ) {
		$plan = get_transient( LLAO_IMPORT_PLAN );
		if ( ! $plan ) {
			$aviso = __( 'El plan ha caducado. Vuelve a subir el archivo.', 'llaollao-core' );
		} else {
			$resultado = llao_locales_importar( $plan, ! empty( $_POST['llao_forzar'] ) );
			if ( empty( $resultado['error'] ) ) {
				delete_transient( LLAO_IMPORT_PLAN );
			}
		}
	}

	// --- Geocodificación por lotes ---
	if ( isset( $_POST['llao_geocodificar'] ) && check_admin_referer( 'llao_geocodificar' ) ) {
		$geo = llao_geocode_lote();
	}

	$plan = get_transient( LLAO_IMPORT_PLAN );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Importar locales', 'llaollao-core' ); ?></h1>

		<?php if ( $aviso ) : ?>
			<div class="notice notice-error"><p><?php echo esc_html( $aviso ); ?></p></div>
		<?php endif; ?>

		<?php if ( $resultado && empty( $resultado['error'] ) ) : ?>
			<div class="notice notice-success">
				<p>
					<?php
					printf(
						/* translators: 1: creados, 2: actualizados, 3: enviados a la papelera, 4: saltados. */
						esc_html__( 'Importación terminada: %1$d creados, %2$d actualizados, %3$d a la papelera, %4$d filas saltadas.', 'llaollao-core' ),
						(int) $resultado['creados'],
						(int) $resultado['actualizados'],
						(int) $resultado['papelera'],
						(int) $resultado['saltadas']
					);
					?>
				</p>
			</div>
		<?php elseif ( $resultado && ! empty( $resultado['error'] ) ) : ?>
			<div class="notice notice-error"><p><?php echo esc_html( $resultado['error'] ); ?></p></div>
		<?php endif; ?>

		<?php if ( $geo ) : ?>
			<div class="notice notice-<?php echo empty( $geo['error'] ) ? 'success' : 'error'; ?>">
				<?php if ( ! empty( $geo['error'] ) ) : ?>
					<p><?php echo esc_html( $geo['error'] ); ?></p>
				<?php else : ?>
					<p>
						<?php
						printf(
							/* translators: 1: resueltos, 2: pendientes. */
							esc_html__( 'Geocodificados %1$d. Quedan %2$d pendientes.', 'llaollao-core' ),
							(int) $geo['resueltos'],
							(int) $geo['quedan']
						);
						?>
					</p>
					<?php if ( ! empty( $geo['fallidos'] ) ) : ?>
						<p><strong><?php esc_html_e( 'No resueltos:', 'llaollao-core' ); ?></strong></p>
						<ul style="margin-left:1.5em;list-style:disc;">
							<?php foreach ( $geo['fallidos'] as $f ) : ?>
								<li><?php echo esc_html( $f ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( $plan ) : ?>

			<h2><?php esc_html_e( 'Revisa antes de importar', 'llaollao-core' ); ?></h2>

			<table class="widefat striped" style="max-width:640px;margin-bottom:1em;">
				<tbody>
					<tr>
						<td><?php esc_html_e( 'Se crearán', 'llaollao-core' ); ?></td>
						<td><strong><?php echo count( $plan['crear'] ); ?></strong></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Se actualizarán', 'llaollao-core' ); ?></td>
						<td><strong><?php echo count( $plan['actualizar'] ); ?></strong></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Irán a la papelera', 'llaollao-core' ); ?></td>
						<td><strong><?php echo count( $plan['papelera'] ); ?></strong></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Filas saltadas', 'llaollao-core' ); ?></td>
						<td><strong><?php echo count( $plan['saltadas'] ); ?></strong></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Países del archivo', 'llaollao-core' ); ?></td>
						<td><strong><?php echo esc_html( implode( ', ', $plan['paises'] ) ); ?></strong></td>
					</tr>
				</tbody>
			</table>

			<?php if ( $plan['papelera'] ) : ?>
				<h3><?php esc_html_e( 'Estos locales ya no vienen en el archivo', 'llaollao-core' ); ?></h3>
				<ul style="margin-left:1.5em;list-style:disc;">
					<?php foreach ( $plan['papelera'] as $item ) : ?>
						<li><?php echo esc_html( $item['titulo'] . ' · ' . $item['direccion'] ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php if ( $plan['saltadas'] ) : ?>
				<h3><?php esc_html_e( 'Filas saltadas', 'llaollao-core' ); ?></h3>
				<ul style="margin-left:1.5em;list-style:disc;">
					<?php foreach ( $plan['saltadas'] as $item ) : ?>
						<li><?php echo esc_html( $item['nombre'] . ' — ' . $item['motivo'] ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php if ( $plan['supera_tope'] ) : ?>
				<div class="notice notice-warning inline">
					<p>
						<?php
						printf(
							/* translators: 1: a la papelera, 2: total en ámbito, 3: porcentaje del tope. */
							esc_html__( 'Atención: se enviarían %1$d de %2$d locales a la papelera, más del %3$d%% del total. Suele ser señal de un archivo incompleto.', 'llaollao-core' ),
							count( $plan['papelera'] ),
							(int) $plan['en_ambito'],
							(int) ( LLAO_IMPORT_SWEEP_LIMIT * 100 )
						);
						?>
					</p>
				</div>
			<?php endif; ?>

			<form method="post">
				<?php wp_nonce_field( 'llao_import_confirmar' ); ?>
				<?php if ( $plan['supera_tope'] ) : ?>
					<p>
						<label>
							<input type="checkbox" name="llao_forzar" value="1">
							<?php esc_html_e( 'He revisado la lista y quiero continuar de todos modos.', 'llaollao-core' ); ?>
						</label>
					</p>
				<?php endif; ?>
				<p>
					<button type="submit" name="llao_import_confirmar" value="1" class="button button-primary">
						<?php esc_html_e( 'Confirmar e importar', 'llaollao-core' ); ?>
					</button>
				</p>
			</form>

		<?php else : ?>

			<form method="post" enctype="multipart/form-data">
				<?php wp_nonce_field( 'llao_import_subir' ); ?>
				<p><?php esc_html_e( 'Sube la exportación en CSV de Perfiles de Empresa de Google. No se tocará nada hasta que revises el resumen y confirmes.', 'llaollao-core' ); ?></p>
				<p><input type="file" name="llao_csv" accept=".csv,text/csv" required></p>
				<p>
					<button type="submit" name="llao_import_subir" value="1" class="button button-primary">
						<?php esc_html_e( 'Subir y revisar', 'llaollao-core' ); ?>
					</button>
				</p>
			</form>

		<?php endif; ?>

		<hr>

		<h2><?php esc_html_e( 'Coordenadas', 'llaollao-core' ); ?></h2>
		<?php $pendientes = llao_geocode_pendientes(); ?>
		<?php if ( '' === llao_geocoding_key() ) : ?>
			<p>
				<?php esc_html_e( 'Falta la clave de la Geocoding API. Defínela en wp-config.php como LLAO_GEOCODING_API_KEY o guárdala en Ajustes → Llaollao.', 'llaollao-core' ); ?>
			</p>
		<?php endif; ?>
		<p>
			<?php
			printf(
				/* translators: %d: locales pendientes de geocodificar. */
				esc_html__( 'Locales pendientes de geocodificar: %d', 'llaollao-core' ),
				(int) $pendientes
			);
			?>
		</p>
		<?php if ( $pendientes ) : ?>
			<form method="post">
				<?php wp_nonce_field( 'llao_geocodificar' ); ?>
				<p>
					<button type="submit" name="llao_geocodificar" value="1" class="button">
						<?php esc_html_e( 'Geocodificar pendientes', 'llaollao-core' ); ?>
					</button>
					<span class="description">
						<?php esc_html_e( 'Se procesan por tandas para no agotar el tiempo de ejecución de PHP; si quedan pendientes, vuelve a pulsar.', 'llaollao-core' ); ?>
					</span>
				</p>
			</form>
		<?php endif; ?>
	</div>
	<?php
}
