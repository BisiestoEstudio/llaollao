<?php

/**
 * Block registration for Bisiesto theme
 */

/**
 * Register custom blocks
 */
function bisiesto_register_blocks()
{
	register_block_type(get_theme_file_path('blocks/header'));
	register_block_type(get_theme_file_path('blocks/footer'));
	register_block_type(get_theme_file_path('blocks/banner'));
	register_block_type(get_theme_file_path('blocks/video-hero'));
	register_block_type(get_theme_file_path('blocks/video-llaollao'));
	register_block_type(get_theme_file_path('blocks/images'));
	register_block_type(get_theme_file_path('blocks/side-images'));
	register_block_type(get_theme_file_path('blocks/side-image-column'));
	register_block_type(get_theme_file_path('blocks/marquee'));
	register_block_type(get_theme_file_path('blocks/history'));
	register_block_type(get_theme_file_path('blocks/history-item'));
	register_block_type(get_theme_file_path('blocks/related-posts'));
}

/**
 * Pasa la lista de formularios de Contact Form 7 al editor de bloques
 * (para el selector del bloque Newsletter Baygo).
 */
add_action('enqueue_block_editor_assets', function (): void {
	if (! class_exists('WPCF7_ContactForm')) {
		return;
	}

	$forms = get_posts([
		'post_type'   => 'wpcf7_contact_form',
		'numberposts' => -1,
		'orderby'     => 'title',
		'order'       => 'ASC',
		'post_status' => 'publish',
	]);

	$list = array_map(fn($p) => ['id' => $p->ID, 'title' => $p->post_title], $forms);

	wp_add_inline_script(
		'wp-blocks',
		'window.baygoCF7Forms = ' . wp_json_encode(array_values($list)) . ';',
		'before'
	);
});

/**
 * Tiñe de lila el icono de nuestros bloques
 */
add_action('enqueue_block_editor_assets', function (): void {
	$prefixes = wp_json_encode(['bisiesto/', 'reservations-hq-rental/']);
	$color    = '#a855f7'; // lila
	$js = <<<JS
( function ( wp ) {
	if ( ! wp || ! wp.hooks ) { return; }
	var prefixes = {$prefixes};
	wp.hooks.addFilter(
		'blocks.registerBlockType',
		'baygo/lila-block-icons',
		function ( settings, name ) {
			var mine = prefixes.some( function ( p ) { return name.indexOf( p ) === 0; } );
			if ( ! mine || ! settings.icon ) { return settings; }
			var src = settings.icon && settings.icon.src ? settings.icon.src : settings.icon;
			settings.icon = { src: src, foreground: '{$color}' };
			return settings;
		}
	);
} )( window.wp );
JS;
	wp_add_inline_script('wp-blocks', $js, 'after');
});

/**
 * Añade el campo "Ancho máximo" al bloque padre core/columns.
 */
add_action('enqueue_block_editor_assets', function (): void {
	wp_enqueue_script(
		'bisiesto-columns-max-width',
		get_theme_file_uri('assets/js/columns-maxwidth.js'),
		array('wp-blocks', 'wp-block-editor', 'wp-components', 'wp-element', 'wp-hooks', 'wp-compose', 'wp-i18n'),
		'1.0.0',
		true
	);
});

add_action('init', 'bisiesto_register_blocks');

/**
 * Hace traducible el texto del bloque core/read-more (CTA "Read article" del post
 * destacado del blog en home.html). Su contenido va incrustado en la plantilla,
 * así que no es una cadena gettext y Loco no lo detecta. Aquí lo sustituimos por
 * la versión traducida; el literal __() permite que Loco lo escanee y Polylang
 * cambia el locale en /es/ para servir la traducción.
 */
add_filter('render_block_core/read-more', function (string $block_content, array $block): string {
	$orig = $block['attrs']['content'] ?? '';
	if ('Read article' !== $orig) {
		return $block_content; // solo el CTA conocido; respeta textos personalizados
	}
	$label = __('Read article', 'baygo');
	if ($label === $orig) {
		return $block_content; // sin traducción cargada → dejar el original
	}
	// El texto va justo tras la apertura del <a>; le sigue un <span screen-reader>
	// con el título, así que reemplazamos solo el texto del enlace (no '>texto</a>').
	return preg_replace(
		'/(<a\b[^>]*>)' . preg_quote($orig, '/') . '/',
		'$1' . esc_html($label),
		$block_content,
		1
	);
}, 10, 2);

/**
 * Register block styles
 */
function bisiesto_register_block_styles()
{
	register_block_style(
		'core/heading',
		array(
			'name'  => 'transparente',
			'label' => __('Transparente', 'bisiesto'),
		)
	);

	register_block_style(
		'core/columns',
		array(
			'name'  => 'centered',
			'label' => __('Contenido centrado', 'bisiesto'),
		)
	);

	register_block_style(
		'core/media-text',
		array(
			'name'  => 'detalle',
			'label' => __('Detalle', 'bisiesto'),
		)
	);

	register_block_style(
		'core/button',
		array(
			'name'  => 'fill-border',
			'label' => __('Fill border', 'bisiesto'),
		)
	);

	register_block_style(
		'core/button',
		array(
			'name'  => 'fill-collapsed',
			'label' => __('Fill collapsed', 'bisiesto'),
		)
	);

	register_block_style(
		'core/button',
		array(
			'name'  => 'outline-collapsed',
			'label' => __('Outline collapsed', 'bisiesto'),
		)
	);

	register_block_style(
		'core/button',
		array(
			'name'  => 'arrow-up',
			'label' => __('Flecha arriba', 'bisiesto'),
		)
	);
}
add_action('init', 'bisiesto_register_block_styles');
