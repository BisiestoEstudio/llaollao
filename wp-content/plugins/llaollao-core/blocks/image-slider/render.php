<?php
/**
 * Render del bloque "Slider de imágenes".
 * Dinámico: el wrapper lleva como custom properties el alto de la fila (móvil y
 * escritorio) y la separación entre imágenes; el resto de la maquetación —fila
 * horizontal, sangrado hasta el borde derecho, scroll— vive en style.css.
 * Dentro, la pista (.llao-slider__track) con los inner blocks ($content).
 */

defined( 'ABSPATH' ) || exit;

$height        = (int) ( $attributes['height'] ?? 480 );
$height_mobile = (int) ( $attributes['heightMobile'] ?? 260 );
$gap           = (int) ( $attributes['gap'] ?? 20 );

$style = sprintf(
	'--llao-slider-h:%dpx;--llao-slider-h-mobile:%dpx;--llao-slider-gap:%dpx;',
	max( 1, $height ),
	max( 1, $height_mobile ),
	max( 0, $gap )
);

$wrapper = get_block_wrapper_attributes( [
	'class' => 'llao-slider',
	'style' => $style,
] );
?>
<div <?php echo $wrapper; ?>>
	<div class="llao-slider__track">
		<?php echo $content; ?>
	</div>
</div>
