<?php
/**
 * Ajustes de Llaollao (Ajustes → Llaollao).
 *
 * Sección "Páginas":
 * - "Página de Carta", que alimenta el primer nivel de las migas de pan.
 * - "Página 404", el contenido que se pinta cuando no se encuentra una URL
 *   (ver includes/error-page.php).
 *
 */

defined( 'ABSPATH' ) || exit;

/**
 * Devuelve el ID de la página configurada como "Carta" (0 si no hay).
 */
function llao_get_carta_page_id() {
	return (int) get_option( 'llao_carta_page_id', 0 );
}

/**
 * Devuelve el ID de la página configurada como "404" (0 si no hay).
 */
function llao_get_404_page_id() {
	return (int) get_option( 'llao_404_page_id', 0 );
}

add_action( 'admin_menu', 'llao_settings_menu' );

function llao_settings_menu() {
	add_options_page(
		__( 'Ajustes de Llaollao', 'llaollao-core' ),
		__( 'Llaollao', 'llaollao-core' ),
		'manage_options',
		'llaollao-core-settings',
		'llao_settings_render_page'
	);
}

add_action( 'admin_init', 'llao_settings_init' );

function llao_settings_init() {

	register_setting( 'llao_settings', 'llao_carta_page_id', array(
		'type'              => 'integer',
		'sanitize_callback' => 'absint',
		'default'           => 0,
		'show_in_rest'      => true,
	) );

	add_settings_section(
		'llao_settings_paginas',
		__( 'Páginas', 'llaollao-core' ),
		'__return_false',
		'llaollao-core-settings'
	);

	register_setting( 'llao_settings', 'llao_404_page_id', array(
		'type'              => 'integer',
		'sanitize_callback' => 'absint',
		'default'           => 0,
		'show_in_rest'      => true,
	) );

	add_settings_field(
		'llao_carta_page_id',
		__( 'Página de Carta', 'llaollao-core' ),
		'llao_field_carta_page',
		'llaollao-core-settings',
		'llao_settings_paginas'
	);

	add_settings_field(
		'llao_404_page_id',
		__( 'Página 404', 'llaollao-core' ),
		'llao_field_404_page',
		'llaollao-core-settings',
		'llao_settings_paginas'
	);

	// Integraciones.
	register_setting( 'llao_settings', 'llao_geocoding_api_key', array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
		'default'           => '',
		'show_in_rest'      => false,
	) );

	register_setting( 'llao_settings', 'llao_maps_api_key', array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
		'default'           => '',
		'show_in_rest'      => false,
	) );

	add_settings_section(
		'llao_settings_integraciones',
		__( 'Integraciones', 'llaollao-core' ),
		'__return_false',
		'llaollao-core-settings'
	);

	add_settings_field(
		'llao_geocoding_api_key',
		__( 'Clave de Geocoding API', 'llaollao-core' ),
		'llao_field_geocoding_key',
		'llaollao-core-settings',
		'llao_settings_integraciones'
	);

	add_settings_field(
		'llao_maps_api_key',
		__( 'Clave de Maps (JavaScript API)', 'llaollao-core' ),
		'llao_field_maps_key',
		'llaollao-core-settings',
		'llao_settings_integraciones'
	);
}

function llao_field_geocoding_key() {

	// Si está en wp-config.php manda esa y el campo se muestra bloqueado: así
	// se ve de dónde sale el valor en lugar de parecer que no hay ninguna.
	if ( defined( 'LLAO_GEOCODING_API_KEY' ) && LLAO_GEOCODING_API_KEY ) {
		echo '<input type="text" class="regular-text" value="' . esc_attr( __( '(definida en wp-config.php)', 'llaollao-core' ) ) . '" disabled>';
		echo '<p class="description">'
			. esc_html__( 'La constante LLAO_GEOCODING_API_KEY tiene preferencia sobre este campo.', 'llaollao-core' )
			. '</p>';
		return;
	}

	printf(
		'<input type="text" class="regular-text" name="llao_geocoding_api_key" id="llao_geocoding_api_key" value="%s" autocomplete="off">',
		esc_attr( (string) get_option( 'llao_geocoding_api_key', '' ) )
	);
	echo '<p class="description">'
		. esc_html__( 'Clave de servidor con la Geocoding API activada; se usa al importar locales para obtener sus coordenadas. Restríngela por IP, no por dominio: la llama PHP, no el navegador. Mejor aún, defínela en wp-config.php como LLAO_GEOCODING_API_KEY para que no quede en la base de datos.', 'llaollao-core' )
		. '</p>';
}

function llao_field_maps_key() {

	// Si está en wp-config.php manda esa y el campo se muestra bloqueado: así
	// se ve de dónde sale el valor en lugar de parecer que no hay ninguna.
	if ( defined( 'LLAO_MAPS_API_KEY' ) && LLAO_MAPS_API_KEY ) {
		echo '<input type="text" class="regular-text" value="' . esc_attr( __( '(definida en wp-config.php)', 'llaollao-core' ) ) . '" disabled>';
		echo '<p class="description">'
			. esc_html__( 'La constante LLAO_MAPS_API_KEY tiene preferencia sobre este campo.', 'llaollao-core' )
			. '</p>';
		return;
	}

	printf(
		'<input type="text" class="regular-text" name="llao_maps_api_key" id="llao_maps_api_key" value="%s" autocomplete="off">',
		esc_attr( (string) get_option( 'llao_maps_api_key', '' ) )
	);
	echo '<p class="description">'
		. esc_html__( 'Clave para el mapa con markers en el front-end (la llama el navegador). Restríngela por referente HTTP (dominio), no por IP: es una clave distinta de la de Geocoding, que va por servidor. Mejor aún, defínela en wp-config.php como LLAO_MAPS_API_KEY para que no quede en la base de datos.', 'llaollao-core' )
		. '</p>';
}

function llao_field_carta_page() {
	wp_dropdown_pages( array(
		'name'              => 'llao_carta_page_id',
		'id'                => 'llao_carta_page_id',
		'selected'          => llao_get_carta_page_id(),
		'show_option_none'  => __( '— Selecciona una página —', 'llaollao-core' ),
		'option_none_value' => '0',
	) );
	echo '<p class="description">'
		. esc_html__( 'Primer nivel de las migas de pan en los productos.', 'llaollao-core' )
		. '</p>';
}

function llao_field_404_page() {
	wp_dropdown_pages( array(
		'name'              => 'llao_404_page_id',
		'id'                => 'llao_404_page_id',
		'selected'          => llao_get_404_page_id(),
		'show_option_none'  => __( '— Selecciona una página —', 'llaollao-core' ),
		'option_none_value' => '0',
	) );
	echo '<p class="description">'
		. esc_html__( 'Contenido que se muestra cuando no se encuentra una URL. La dirección rota se mantiene y la respuesta sigue siendo un 404; solo cambia lo que se pinta. Sin seleccionar, se usa la plantilla del tema.', 'llaollao-core' )
		. '</p>';
}

function llao_settings_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'llao_settings' );
			do_settings_sections( 'llaollao-core-settings' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}
