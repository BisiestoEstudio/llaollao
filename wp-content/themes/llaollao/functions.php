<?php
/**
 * Bisiesto Theme Functions
 */

// Block registration
require_once get_theme_file_path( 'includes/blocks.php' );
require_once get_theme_file_path( 'includes/lang-switcher.php' );
require_once get_theme_file_path( 'includes/menu-item-fields.php' );
require_once get_theme_file_path( 'includes/menu-panels.php' );

/**
 * Enqueue block styles (frontend)
 */
function bisiesto_enqueue_block_styles() {
	wp_enqueue_style(
		'bisiesto-blocks',
		get_theme_file_uri( 'assets/css/blocks.css' ),
		array( 'global-styles' ),
		wp_get_theme()->get( 'Version' )
	);
}
add_action( 'wp_enqueue_scripts', 'bisiesto_enqueue_block_styles' );

/**
 * Load block styles inside the editor iframe
 */
function bisiesto_editor_styles() {
	// editor.css va después de blocks.css para poder sobrescribir estilos solo
	// en el editor (p. ej. el heading "Transparente", que en el front es invisible).
	add_editor_style( array( 'assets/css/blocks.css', 'assets/css/editor.css' ) );
}
add_action( 'after_setup_theme', 'bisiesto_editor_styles' );

/**
 * Soporte de logotipo del sitio (lo lee el header en blocks/header/render.php
 * vía get_theme_mod( 'custom_logo' )). El ancho real de visualización lo fija el
 * CSS (.site-header__logo-img); aquí solo declaramos el tamaño de referencia.
 * flex-width a true (sin forzar recorte): con flex-width:false WordPress
 * intenta recortar la imagen a ese ancho exacto al subirla, y un SVG no se
 * puede recortar (es vectorial) — el recorte fallaba con SVG.
 */
add_action( 'after_setup_theme', function () {
	add_theme_support( 'custom-logo', [
		'width'       => 98,
		'flex-width'  => true,
		'flex-height' => true,
	] );
} );

/**
 * Menú del overlay a pantalla completa del header (botón hamburguesa),
 * gestionable en Apariencia → Menús. Independiente del bloque de navegación
 * que ya lleva el header (blocks/header/render.php).
 */
add_action( 'after_setup_theme', function () {
	register_nav_menus( [
		'menu-hamburguesa' => __( 'Menú hamburguesa (pantalla completa)', 'bisiesto' ),
	] );
} );

/**
 * WordPress oculta "Personalizar" bajo Apariencia cuando el tema es de
 * bloques (wp_is_block_theme()). Lo volvemos a añadir a mano: es el mismo
 * código que usa el core para temas clásicos (wp-admin/menu.php), así que
 * el enlace apunta directo a customize.php, no a una página propia.
 */
add_action( 'admin_menu', function () {
	global $submenu;

	if ( ! isset( $submenu['themes.php'] ) || ! current_user_can( 'customize' ) ) {
		return;
	}

	$submenu['themes.php'][6] = array(
		__( 'Personalizar' ),
		'customize',
		add_query_arg( 'return', urlencode( $_SERVER['REQUEST_URI'] ?? '' ), 'customize.php' ),
	);
}, 999 );

/**
 * Permite subir SVG a la biblioteca de medios.
 */
function permitir_svg( $mimes = array() ) {
	$mimes['svg'] = 'image/svg+xml';
	$mimes['svgz'] = 'image/svg+xml';
	return $mimes;
}
add_filter( 'upload_mimes', 'permitir_svg' );

/**
 * Disable Contact Form 7 automatic <p> / <br> wrapping
 */
add_filter( 'wpcf7_autop_or_not', '__return_false' );
