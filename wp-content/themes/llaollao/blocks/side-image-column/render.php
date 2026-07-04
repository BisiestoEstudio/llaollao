<?php
/**
 * Render del bloque "Side image · columna".
 * Dinámico (server-side), como el Banner: la imagen de fondo y el focal point
 * salen de los atributos; el color de fondo lo aporta el soporte nativo
 * (get_block_wrapper_attributes ya añade sus clases). El contenido son los
 * InnerBlocks ($content), centrados por .side-image-column__inner.
 */

defined( 'ABSPATH' ) || exit;

$image_url = $attributes['imageUrl'] ?? '';
$image_alt = $attributes['imageAlt'] ?? '';
$focal     = $attributes['focalPoint'] ?? [ 'x' => 0.5, 'y' => 0.5 ];

$extra = [ 'class' => 'side-image-column' ];

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
	<div class="side-image-column__inner">
		<?php echo $content; ?>
	</div>
</div>
