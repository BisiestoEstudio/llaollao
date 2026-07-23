<?php
/**
 * Render del bloque "Slider · imagen".
 * Dinámico para poder servir la imagen con wp_get_attachment_image() (srcset,
 * sizes y lazy load) a partir del ID del adjunto. La proporción se aplica como
 * aspect-ratio en el <figure>: la elegida en el panel o, si es "Original", la
 * del propio archivo. Con eso el alto lo marca la fila y el ancho sale solo.
 */

defined( 'ABSPATH' ) || exit;

$id     = (int) ( $attributes['id'] ?? 0 );
$url    = (string) ( $attributes['url'] ?? '' );
$alt    = (string) ( $attributes['alt'] ?? '' );
$width  = (int) ( $attributes['width'] ?? 0 );
$height = (int) ( $attributes['height'] ?? 0 );
$ratio  = (string) ( $attributes['ratio'] ?? '' );
$focal  = $attributes['focalPoint'] ?? [ 'x' => 0.5, 'y' => 0.5 ];

if ( ! $id && '' === $url ) {
	return '';
}

// Proporción: la del panel (formato "4/3") o, si no hay, la del archivo.
// Solo aceptamos "número/número" para no colar cualquier cosa en el style.
if ( ! preg_match( '~^\d+(?:\.\d+)?/\d+(?:\.\d+)?$~', $ratio ) ) {
	$ratio = ( $width > 0 && $height > 0 ) ? $width . '/' . $height : '';
}

$classes = [ 'llao-slider__item' ];
$styles  = [];

if ( '' !== $ratio ) {
	$styles[] = 'aspect-ratio:' . $ratio;
} else {
	// Sin proporción conocida: el ancho lo pone la propia imagen.
	$classes[] = 'llao-slider__item--auto';
}

$img_style = sprintf(
	'object-position:%d%% %d%%;',
	round( ( $focal['x'] ?? 0.5 ) * 100 ),
	round( ( $focal['y'] ?? 0.5 ) * 100 )
);

$img_attrs = [
	'class'     => 'llao-slider__img',
	'style'     => $img_style,
	'alt'       => $alt,
	'draggable' => 'false',
];

if ( $id ) {
	$img = wp_get_attachment_image( $id, 'large', false, $img_attrs );
} else {
	$img = '';
}

// Respaldo si el adjunto ya no existe pero seguimos teniendo la URL guardada.
if ( '' === $img && '' !== $url ) {
	$img = sprintf(
		'<img class="llao-slider__img" src="%s" alt="%s" style="%s" draggable="false" loading="lazy" decoding="async" />',
		esc_url( $url ),
		esc_attr( $alt ),
		esc_attr( $img_style )
	);
}

if ( '' === $img ) {
	return '';
}

$extra = [ 'class' => implode( ' ', $classes ) ];
if ( $styles ) {
	$extra['style'] = implode( ';', $styles );
}

$wrapper = get_block_wrapper_attributes( $extra );
?>
<figure <?php echo $wrapper; ?>>
	<?php echo $img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image / esc_* arriba ?>
</figure>
