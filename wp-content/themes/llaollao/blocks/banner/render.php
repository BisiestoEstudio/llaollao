<?php
/**
 * Render del bloque "Banner".
 * Dinámico (server-side) para que Polylang/DeepL traduzca el alt sin romper
 * la validación del bloque. El contenido es InnerBlocks ($content); la
 * imagen de fondo y el focal point salen de los atributos.
 */

defined( 'ABSPATH' ) || exit;

$image_url  = $attributes['imageUrl'] ?? '';
$image_alt  = $attributes['imageAlt'] ?? '';
$focal      = $attributes['focalPoint'] ?? [ 'x' => 0.5, 'y' => 0.5 ];

$extra = [ 'class' => 'banner' ];

if ( $image_url ) {
	$pos_x          = round( ( $focal['x'] ?? 0.5 ) * 100 );
	$pos_y          = round( ( $focal['y'] ?? 0.5 ) * 100 );
	$extra['style'] = sprintf(
		'background-image:url(%s);background-position:%d%% %d%%;',
		esc_url( $image_url ),
		$pos_x,
		$pos_y
	);
	$extra['role'] = 'img';
	if ( '' !== $image_alt ) {
		$extra['aria-label'] = $image_alt;
	}
}

$wrapper = get_block_wrapper_attributes( $extra );
?>
<div <?php echo $wrapper; ?>>
	<?php echo $content; ?>
</div>
