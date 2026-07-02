<?php
/**
 * Bisiesto Theme Functions
 */

// Block registration
require_once get_theme_file_path( 'includes/blocks.php' );
require_once get_theme_file_path( 'includes/settings.php' );
require_once get_theme_file_path( 'includes/lang-switcher.php' );

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
 */
add_action( 'after_setup_theme', function () {
	add_theme_support( 'custom-logo', [
		'width'       => 98,
		'flex-width'  => false,
		'flex-height' => true,
	] );
} );

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

/**
 * En la página de entradas, la última entrada fija (sticky) se muestra como
 * destacada arriba (ver home.html), así que la excluimos del grid principal
 * para que no aparezca duplicada.
 */
add_action( 'pre_get_posts', function ( WP_Query $query ): void {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_home() ) {
		return;
	}

	// Evitar que los sticky floten al principio del grid.
	$query->set( 'ignore_sticky_posts', 1 );

	$sticky = get_option( 'sticky_posts' );
	if ( empty( $sticky ) ) {
		return;
	}

	// Excluir solo la última entrada fija (la que se muestra como destacada).
	$latest = get_posts( [
		'post__in'       => $sticky,
		'posts_per_page' => 1,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'fields'         => 'ids',
		'post_status'    => 'publish',
	] );

	if ( ! empty( $latest ) ) {
		$query->set( 'post__not_in', $latest );
	}
} );

// Setear idioma predeterminado en inglés
add_filter( 'pll_rel_hreflang_attributes', function( $hreflangs ) {
    if ( isset( $hreflangs['en'] ) ) {
        $hreflangs['x-default'] = $hreflangs['en'];
    }
    return $hreflangs;
}, 20 );

/*
 * Traduce el prefijo de los idiomas
 */
add_filter('pre_post_link', 'cambiar_prefijo_blog_por_idioma', 10, 3);
function cambiar_prefijo_blog_por_idioma($permalink, $post, $leavename) {
    if (function_exists('pll_get_post_language')) {
        $lang = pll_get_post_language($post->ID);
        $slug = $leavename ? '%postname%' : $post->post_name;
        if ($lang == 'fi') {
            return '/fi/blogi/' . $slug . '/';
        }
        if ($lang == 'sv') {
            return '/sv/blogg/' . $slug . '/';
        }
    }
    return $permalink;
}

add_filter('request', 'resolver_urls_blog_idioma', 1);
function resolver_urls_blog_idioma($query_vars) {
    $uri = trim($_SERVER['REQUEST_URI'], '/');
    if (preg_match('#^(fi/blogi|sv/blogg)/([^/]+)/?$#', $uri, $matches)) {
        $query_vars = array('name' => $matches[2], 'post_type' => 'post');
    }
    return $query_vars;
}