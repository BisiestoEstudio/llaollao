<?php
/**
 * Render del bloque "Locales".
 *
 * Dos columnas: a la izquierda el buscador, los botones de país y el listado;
 * a la derecha el mapa de Google con los markers de los locales que queden
 * visibles en el listado. El filtrado (país + buscador) y la sincronización
 * con el mapa los hace view.js sobre el HTML ya pintado, sin recargar.
 *
 * Solo entran locales publicados con coordenadas (lat/lng) y país asignado:
 * sin alguno de los dos no hay forma de listarlos ni de pintarlos en el mapa.
 *
 * Bajo la dirección, si existe, sale el horario de hoy (uno de los siete
 * metacampos llao_local_horario_lunes…domingo). Vacío significa que el local
 * está cerrado ese día, y en ese caso no se pinta la línea.
 *
 * Cabecera del listado: buscador + país seleccionado + botón "Filtros", que
 * despliega un panel a dos columnas con el resto de países. El panel y el
 * listado se superponen (position:absolute dentro de .llao-locales__body,
 * mismo hueco para los dos) para poder animar la entrada/salida del panel con
 * opacidad + transform sin que el listado de debajo cambie de tamaño.
 *
 * $content son los InnerBlocks (p. ej. un encabezado) que se pintan antes de
 * todo lo demás; ver src/index.js (save: InnerBlocks.Content).
 */

defined( 'ABSPATH' ) || exit;

$search_placeholder = trim( (string) ( $attributes['searchPlaceholder'] ?? '' ) );
if ( '' === $search_placeholder ) {
	$search_placeholder = __( 'Barcelona', 'llaollao-core' );
}
$default_pais = sanitize_title( (string) ( $attributes['defaultPais'] ?? '' ) );

// Día actual en la zona horaria del sitio, para leer el horario de hoy
// (llao_local_horario_lunes…domingo). llao_local_dias() los da en ese mismo
// orden (lunes primero), que coincide con el 1=lunes de current_time('N').
$dias_semana = array_keys( llao_local_dias() );
$dia_hoy     = $dias_semana[ (int) current_time( 'N' ) - 1 ];

$query = new WP_Query( array(
	'post_type'           => 'local',
	'post_status'         => 'publish',
	'posts_per_page'      => -1,
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
	'orderby'             => 'title',
	'order'               => 'ASC',
	'meta_query'          => array(
		'relation' => 'AND',
		array( 'key' => 'llao_local_lat', 'compare' => 'EXISTS' ),
		array( 'key' => 'llao_local_lng', 'compare' => 'EXISTS' ),
	),
) );

$locales = array();
$paises  = array(); // slug => nombre.

while ( $query->have_posts() ) {
	$query->the_post();

	$post_id = get_the_ID();
	$lat     = get_post_meta( $post_id, 'llao_local_lat', true );
	$lng     = get_post_meta( $post_id, 'llao_local_lng', true );

	if ( '' === $lat || '' === $lng ) {
		continue;
	}

	// Sin país asignado no hay botón bajo el que listar ni filtrar el local.
	$terms = get_the_terms( $post_id, 'pais' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		continue;
	}
	$term = $terms[0];

	$title   = get_the_title( $post_id );
	$address = (string) get_post_meta( $post_id, 'llao_local_direccion', true );
	$horario = trim( (string) get_post_meta( $post_id, 'llao_local_horario_' . $dia_hoy, true ) );

	$locales[] = array(
		'title'   => $title,
		'address' => $address,
		'horario' => $horario,
		'lat'     => (float) $lat,
		'lng'     => (float) $lng,
		'pais'    => $term->slug,
		'search'  => function_exists( 'mb_strtolower' )
			? mb_strtolower( $title . ' ' . $address )
			: strtolower( $title . ' ' . $address ),
	);

	$paises[ $term->slug ] = $term->name;
}
wp_reset_postdata();

if ( ! $locales ) {
	return;
}

asort( $paises, SORT_NATURAL | SORT_FLAG_CASE );

if ( '' === $default_pais || ! isset( $paises[ $default_pais ] ) ) {
	$default_pais = array_key_first( $paises );
}

// Clave del mapa (frontend, restringida por referente); ver LLAO_GEOCODING_API_KEY
// para la de geocoding, que es otra y no debe usarse aquí.
$maps_key = ( defined( 'LLAO_MAPS_API_KEY' ) && LLAO_MAPS_API_KEY )
	? LLAO_MAPS_API_KEY
	: get_option( 'llao_maps_api_key', '' );

$wrapper   = get_block_wrapper_attributes( array( 'class' => 'llao-locales' ) );
$panel_id  = wp_unique_id( 'llao-locales-filtros-' );
?>
<div <?php echo $wrapper; ?> data-maps-key="<?php echo esc_attr( $maps_key ); ?>">

	<?php if ( '' !== trim( (string) $content ) ) : ?>
		<div class="llao-locales__innerblocks">
			<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput -- markup de InnerBlocks, ya sanitizado por el editor. ?>
		</div>
	<?php endif; ?>

	<div class="llao-locales__header">
		<input
			type="search"
			class="llao-locales__search"
			placeholder="<?php echo esc_attr( $search_placeholder ); ?>"
			aria-label="<?php echo esc_attr( $search_placeholder ); ?>"
		>

		<span class="llao-locales__pais-actual"><?php echo esc_html( $paises[ $default_pais ] ); ?></span>

		<button
			type="button"
			class="llao-locales__filtros-toggle button-tag"
			aria-expanded="false"
			aria-controls="<?php echo esc_attr( $panel_id ); ?>"
		><?php esc_html_e( 'Filtros', 'llaollao-core' ); ?></button>
	</div>

	<div class="llao-locales__col-list">

		<div class="llao-locales__body">

			<ul class="llao-locales__list">
				<?php foreach ( $locales as $local ) : ?>
					<li
						class="llao-locales__item<?php echo $local['pais'] === $default_pais ? '' : ' is-hidden'; ?>"
						data-pais="<?php echo esc_attr( $local['pais'] ); ?>"
						data-search="<?php echo esc_attr( $local['search'] ); ?>"
						data-lat="<?php echo esc_attr( $local['lat'] ); ?>"
						data-lng="<?php echo esc_attr( $local['lng'] ); ?>"
						tabindex="0"
						role="button"
					>
						<p class="llao-locales__title"><?php echo esc_html( $local['title'] ); ?></p>
						<?php if ( '' !== $local['address'] ) : ?>
							<p class="llao-locales__address"><?php echo esc_html( $local['address'] ); ?></p>
						<?php endif; ?>
						<?php if ( '' !== $local['horario'] ) : ?>
							<p class="llao-locales__horario">
								<?php echo esc_html( sprintf( __( 'Hoy: %s', 'llaollao-core' ), $local['horario'] ) ); ?>
							</p>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>

			<div
				class="llao-locales__filtros-panel"
				id="<?php echo esc_attr( $panel_id ); ?>"
				role="group"
				aria-label="<?php esc_attr_e( 'Filtrar por país', 'llaollao-core' ); ?>"
			>
				<button type="button" class="llao-locales__filtros-cerrar" aria-label="<?php esc_attr_e( 'Cerrar', 'llaollao-core' ); ?>">
					<svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 30 30" fill="none" aria-hidden="true" focusable="false">
						<path d="M3.75 11.75C3.75 8.94974 3.75 7.54961 4.29497 6.48005C4.77433 5.53924 5.53924 4.77433 6.48005 4.29497C7.54961 3.75 8.94974 3.75 11.75 3.75H18.25C21.0503 3.75 22.4504 3.75 23.52 4.29497C24.4608 4.77433 25.2257 5.53924 25.705 6.48005C26.25 7.54961 26.25 8.94974 26.25 11.75V18.25C26.25 21.0503 26.25 22.4504 25.705 23.52C25.2257 24.4608 24.4608 25.2257 23.52 25.705C22.4504 26.25 21.0503 26.25 18.25 26.25H11.75C8.94974 26.25 7.54961 26.25 6.48005 25.705C5.53924 25.2257 4.77433 24.4608 4.29497 23.52C3.75 22.4504 3.75 21.0503 3.75 18.25V11.75Z" fill="#ED822C"/>
						<path d="M18.75 11.25L11.25 18.75M11.25 11.25L18.7499 18.75" stroke="#F6EECB" stroke-width="1.875" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</button>

				<div class="llao-locales__filtros-grid">
					<?php foreach ( $paises as $slug => $name ) : ?>
						<button
							type="button"
							class="llao-locales__pais<?php echo $slug === $default_pais ? ' is-active' : ''; ?>"
							data-pais="<?php echo esc_attr( $slug ); ?>"
							aria-pressed="<?php echo $slug === $default_pais ? 'true' : 'false'; ?>"
						><?php echo esc_html( $name ); ?></button>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

		<p class="llao-locales__empty" hidden><?php esc_html_e( 'No se han encontrado locales.', 'llaollao-core' ); ?></p>
	</div>

	<div class="llao-locales__col-map">
		<div class="llao-locales__map"></div>
	</div>
</div>
