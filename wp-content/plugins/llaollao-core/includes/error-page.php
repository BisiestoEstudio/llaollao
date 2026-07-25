<?php
/**
 * Página 404 editable desde "Páginas".
 *
 * El tema es de bloques, así que lo normal sería diseñar la 404 en el editor
 * del sitio (templates/404.html). Aquí damos la alternativa que pidió el
 * cliente: elegir una página normal en Ajustes → Llaollao y que sea su
 * contenido el que se pinte al no encontrar una URL. Ventaja: se edita como
 * cualquier página, con los bloques propios y sin entrar al editor del sitio.
 *
 * Importante: NO hay redirección. La URL rota se queda tal cual y la respuesta
 * sigue siendo un 404 real; lo único que cambia es qué contenido se pinta.
 * Para conseguirlo sustituimos la consulta principal justo antes de que
 * WordPress elija plantilla, de modo que la jerarquía vea una página normal.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Marca si el 404 se está pintando con la página configurada. Lo usa el filtro
 * de body_class, porque tras el cambio de consulta is_404() ya devuelve false.
 */
function llao_404_page_is_active( $set = null ) {
	static $active = false;
	if ( true === $set ) {
		$active = true;
	}
	return $active;
}

/**
 * Sustituye la consulta principal de un 404 por la página configurada.
 *
 * Prioridad 1 y enganchado a template_redirect: es el último punto antes de
 * que template-loader.php resuelva la plantilla, así que el cambio de consulta
 * sí influye en qué plantilla se carga (pasa de la rama 404 a la de página).
 */
add_action( 'template_redirect', 'llao_render_404_page', 1 );

function llao_render_404_page() {
	if ( ! is_404() ) {
		return;
	}

	$page_id = llao_get_404_page_id();
	if ( ! $page_id || 'publish' !== get_post_status( $page_id ) ) {
		return; // Sin configurar o despublicada: se queda la plantilla del tema.
	}

	$replacement = new WP_Query( array(
		'page_id'                => $page_id,
		'ignore_sticky_posts'    => true,
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
	) );

	if ( ! $replacement->have_posts() ) {
		return;
	}

	// A partir de aquí is_404() es false e is_page() true, así que la 404 se
	// pinta con la plantilla de esa página (incluida su plantilla propia si la
	// tiene asignada).
	$GLOBALS['wp_query'] = $replacement;
	$GLOBALS['post']     = $replacement->posts[0];
	setup_postdata( $GLOBALS['post'] );

	llao_404_page_is_active( true );

	// Imprescindible: redirect_canonical() corre en este mismo hook con
	// prioridad 10 (nosotros vamos con 1). Al ver una consulta de página cuyo
	// permalink no coincide con la URL pedida, mandaría un 301 a la página
	// elegida. Queremos justo lo contrario: que la URL rota se quede como está.
	remove_action( 'template_redirect', 'redirect_canonical' );

	// De cara al navegador y a los buscadores sigue siendo un 404.
	status_header( 404 );
	nocache_headers();

	// El canónico apuntaría a la página elegida y haría que la URL rota pasara
	// por duplicado suyo. En un 404 no queremos eso.
	remove_action( 'wp_head', 'rel_canonical' );
}

/**
 * Devuelve la clase .error404 al body: tras el cambio de consulta WordPress
 * pondría solo las clases de página, y el CSS que mire el 404 dejaría de valer.
 */
add_filter( 'body_class', 'llao_404_page_body_class' );

function llao_404_page_body_class( $classes ) {
	if ( llao_404_page_is_active() && ! in_array( 'error404', $classes, true ) ) {
		$classes[] = 'error404';
	}
	return $classes;
}
