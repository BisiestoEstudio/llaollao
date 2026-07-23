<?php
/**
 * Ajustes de Llaollao (Ajustes → Llaollao).
 *
 * De momento solo la "Página de Carta", que alimenta el primer nivel de las
 * migas de pan de los productos.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Devuelve el ID de la página configurada como "Carta" (0 si no hay).
 */
function llao_get_carta_page_id() {
	return (int) get_option( 'llao_carta_page_id', 0 );
}

add_action( 'admin_menu', 'llao_settings_menu' );

function llao_settings_menu() {
	add_options_page(
		__( 'Ajustes de Llaollao', 'llaollao-core' ),
		__( 'Llaollao', 'llaollao-core' ),
		'manage_options',
		'llaollao-settings',
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
		'llaollao-settings'
	);

	add_settings_field(
		'llao_carta_page_id',
		__( 'Página de Carta', 'llaollao-core' ),
		'llao_field_carta_page',
		'llaollao-settings',
		'llao_settings_paginas'
	);
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
			do_settings_sections( 'llaollao-settings' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}
