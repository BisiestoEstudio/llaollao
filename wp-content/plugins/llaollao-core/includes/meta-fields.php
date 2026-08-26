<?php
/**
 * Custom fields de los CPT "producto" y "local".
 *
 * Se registran como post meta expuestos en REST (para el editor de bloques y
 * el front) y se editan con un panel propio en la barra lateral del editor
 * (assets/js/product-fields.js).
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'llao_register_product_meta' );

function llao_register_product_meta() {

	$can_edit = function () {
		return current_user_can( 'edit_posts' );
	};

	// Descripción: texto de la ficha. Va en un campo propio y no en el contenido
	// del producto a propósito, para que el bloque "Vista producto" pueda
	// insertarse dentro del propio producto sin llamarse a sí mismo.
	register_post_meta( 'producto', 'llao_descripcion', array(
		'type'          => 'string',
		'single'        => true,
		'default'       => '',
		'auth_callback' => $can_edit,
		'show_in_rest'  => true,
	) );

	// Recursos: array de IDs de adjuntos (imágenes o vídeos).
	register_post_meta( 'producto', 'llao_recursos', array(
		'type'          => 'array',
		'single'        => true,
		'default'       => array(),
		'auth_callback' => $can_edit,
		'show_in_rest'  => array(
			'schema' => array(
				'type'  => 'array',
				'items' => array( 'type' => 'integer' ),
			),
		),
	) );

	// Variantes: repeater de texto + enlace opcional.
	register_post_meta( 'producto', 'llao_variantes', array(
		'type'          => 'array',
		'single'        => true,
		'default'       => array(),
		'auth_callback' => $can_edit,
		'show_in_rest'  => array(
			'schema' => array(
				'type'  => 'array',
				'items' => array(
					'type'       => 'object',
					'properties' => array(
						'texto' => array( 'type' => 'string' ),
						'url'   => array( 'type' => 'string' ),
					),
				),
			),
		),
	) );

	// Alérgenos: array de IDs de adjuntos (iconos), no texto. Se muestran en
	// grid de 35x35 (ver render.php de vista-producto y vista-producto-simple).
	register_post_meta( 'producto', 'llao_alergenos', array(
		'type'          => 'array',
		'single'        => true,
		'default'       => array(),
		'auth_callback' => $can_edit,
		'show_in_rest'  => array(
			'schema' => array(
				'type'  => 'array',
				'items' => array( 'type' => 'integer' ),
			),
		),
	) );

	// Oculta el bloque de migas de pan (breadcrumbs) en este producto.
	register_post_meta( 'producto', 'llao_ocultar_breadcrumbs', array(
		'type'          => 'boolean',
		'single'        => true,
		'default'       => false,
		'auth_callback' => $can_edit,
		'show_in_rest'  => true,
	) );
}

/**
 * Días de la semana del horario, en el orden en que se muestran. La clave es el
 * sufijo del meta (llao_local_horario_lunes) y el valor, la etiqueta.
 */
function llao_local_dias() {
	return array(
		'lunes'     => __( 'Lunes', 'llaollao-core' ),
		'martes'    => __( 'Martes', 'llaollao-core' ),
		'miercoles' => __( 'Miércoles', 'llaollao-core' ),
		'jueves'    => __( 'Jueves', 'llaollao-core' ),
		'viernes'   => __( 'Viernes', 'llaollao-core' ),
		'sabado'    => __( 'Sábado', 'llaollao-core' ),
		'domingo'   => __( 'Domingo', 'llaollao-core' ),
	);
}

add_action( 'init', 'llao_register_local_meta' );

function llao_register_local_meta() {

	$can_edit = function () {
		return current_user_can( 'edit_posts' );
	};

	$texto = function ( $key ) use ( $can_edit ) {
		register_post_meta( 'local', $key, array(
			'type'          => 'string',
			'single'        => true,
			'default'       => '',
			'auth_callback' => $can_edit,
			'show_in_rest'  => true,
		) );
	};

	$booleano = function ( $key ) use ( $can_edit ) {
		register_post_meta( 'local', $key, array(
			'type'          => 'boolean',
			'single'        => true,
			'default'       => false,
			'auth_callback' => $can_edit,
			'show_in_rest'  => true,
		) );
	};

	// Código de tienda de Google. Es la clave con la que el importador reconoce
	// un local ya existente, así que no debe tocarse a mano.
	$texto( 'llao_local_codigo' );

	// Dirección completa, ya montada a partir de las columnas del CSV. Es la
	// cadena que se envía a geocodificar.
	$texto( 'llao_local_direccion' );

	// Coordenadas y calidad del resultado que devolvió Google (ROOFTOP,
	// GEOMETRIC_CENTER, APPROXIMATE…). La precisión se guarda para poder
	// listar las dudosas y repasarlas a mano.
	register_post_meta( 'local', 'llao_local_lat', array(
		'type'          => 'number',
		'single'        => true,
		'auth_callback' => $can_edit,
		'show_in_rest'  => true,
	) );
	register_post_meta( 'local', 'llao_local_lng', array(
		'type'          => 'number',
		'single'        => true,
		'auth_callback' => $can_edit,
		'show_in_rest'  => true,
	) );
	$texto( 'llao_local_geo_precision' );

	// Un campo de horario por día. El valor es el texto tal cual lo da Google
	// ("12:30-22:00" o "00:00-00:30, 12:00-24:00"); vacío significa cerrado.
	foreach ( array_keys( llao_local_dias() ) as $dia ) {
		$texto( 'llao_local_horario_' . $dia );
	}

	// Servicios. El importador solo escribe baños y terraza cuando el CSV trae
	// un sí o un no explícito; "club" es siempre propio de WordPress y el
	// importador no lo toca nunca.
	$booleano( 'llao_local_banos' );
	$booleano( 'llao_local_terraza' );
	$booleano( 'llao_local_club' );
}

add_action( 'enqueue_block_editor_assets', 'llao_product_fields_assets' );

function llao_product_fields_assets() {

	// Solo en el editor del CPT producto.
	$screen = get_current_screen();
	if ( ! $screen || 'producto' !== $screen->post_type ) {
		return;
	}

	$rel  = 'assets/js/product-fields.js';
	$path = LLAO_CORE_DIR . $rel;

	wp_enqueue_script(
		'llao-product-fields',
		LLAO_CORE_URL . $rel,
		array(
			'wp-plugins',
			'wp-editor',
			'wp-edit-post',
			'wp-element',
			'wp-components',
			'wp-data',
			'wp-core-data',
			'wp-block-editor',
			'wp-i18n',
		),
		file_exists( $path ) ? filemtime( $path ) : '1.0.0',
		true
	);
}
