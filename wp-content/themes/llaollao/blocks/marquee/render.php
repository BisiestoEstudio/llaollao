<?php
/**
 * Render del bloque "Marquee".
 * La cinta se anima solo con CSS con el patrón clásico sin costura: dos grupos
 * idénticos, cada uno con min-width:100% (nunca más estrechos que el viewport),
 * animados con translateX(-100%). Cuando el segundo grupo llega a la izquierda
 * ocupa exactamente el sitio del primero → bucle infinito sin hueco, aunque
 * haya pocos textos. El · lo pone style.css como ::after de cada texto para que
 * la separación sea igual también en el empalme entre grupos.
 */

defined( 'ABSPATH' ) || exit;

$items = $attributes['items'] ?? [];
$items = array_values( array_filter(
	array_map( 'trim', array_map( 'strval', (array) $items ) ),
	static function ( $t ) { return '' !== $t; }
) );

if ( empty( $items ) ) {
	return;
}

$group = '';
foreach ( $items as $text ) {
	$group .= '<span class="marquee__item">' . esc_html( $text ) . '</span>';
}

$wrapper = get_block_wrapper_attributes( [ 'class' => 'marquee' ] );
?>
<div <?php echo $wrapper; ?>>
	<div class="marquee__group"><?php echo $group; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ya escapado arriba ?></div>
	<div class="marquee__group" aria-hidden="true"><?php echo $group; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
</div>
