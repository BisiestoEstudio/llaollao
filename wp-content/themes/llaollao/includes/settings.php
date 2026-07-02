<?php
/**
 * Página de opciones "Llaollao"
 *
 * De momento está vacía: registra el menú y la opción del tema, pero todavía no
 * expone ningún campo. Las secciones y campos se irán añadiendo a lo largo del
 * desarrollo con add_settings_section() / add_settings_field() sobre la página
 * 'llaollao-settings'.
 */

defined( 'ABSPATH' ) || exit;

const LLAOLLAO_OPTION_KEY = 'llaollao_settings';

/**
 * Valores por defecto de la configuración del tema. Vacío por ahora; se irá
 * rellenando conforme se añadan opciones.
 */
function llaollao_settings_defaults(): array {
	return [];
}

/**
 * Devuelve la configuración guardada combinada con los valores por defecto.
 */
function llaollao_get_settings(): array {
	return wp_parse_args( get_option( LLAOLLAO_OPTION_KEY, [] ), llaollao_settings_defaults() );
}

add_action( 'admin_menu', function (): void {
	add_menu_page(
		__( 'Llaollao', 'bisiesto' ),
		__( 'Llaollao', 'bisiesto' ),
		'manage_options',
		'llaollao-settings',
		'llaollao_settings_page',
		'dashicons-admin-settings',
		60
	);
} );

add_action( 'admin_init', function (): void {
	register_setting( 'llaollao_settings_group', LLAOLLAO_OPTION_KEY, [
		'sanitize_callback' => 'llaollao_sanitize_settings',
	] );

	// Las secciones y campos se añadirán aquí más adelante.
} );

/**
 * Sanea la configuración antes de guardarla. Sin campos todavía: parte de los
 * valores por defecto para no almacenar datos sueltos.
 */
function llaollao_sanitize_settings( $input ): array {
	$input = is_array( $input ) ? $input : [];
	$clean = llaollao_settings_defaults();

	// La sanitización de cada campo se añadirá aquí más adelante.

	return $clean;
}

function llaollao_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Llaollao', 'bisiesto' ); ?></h1>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'llaollao_settings_group' );
			do_settings_sections( 'llaollao-settings' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}
