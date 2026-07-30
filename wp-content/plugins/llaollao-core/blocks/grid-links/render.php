<?php
/**
 * Render del bloque "Grid links".
 * Dinámico: el wrapper lleva la separación entre elementos como custom
 * property y el resto de la maquetación vive en style.css. Arriba, la zona
 * libre con los inner blocks ($content); debajo, la rejilla a partir de los
 * atributos.
 */

defined( 'ABSPATH' ) || exit;

$items = is_array( $attributes['items'] ?? null ) ? $attributes['items'] : [];
$gap   = (int) ( $attributes['gap'] ?? 20 );

$wrapper = get_block_wrapper_attributes( array(
	'class' => 'llao-grid-links',
	'style' => sprintf( '--llao-grid-links-gap:%dpx;', max( 0, $gap ) ),
) );
?>
<div <?php echo $wrapper; ?>>
	<div class="llao-grid-links__header is-layout-flow"><?php echo $content; ?></div>

	<div class="llao-grid-links__grid">
		<?php
		foreach ( $items as $item ) :
			$url      = trim( $item['url'] ?? '' );
			$image_id = (int) ( $item['imageId'] ?? 0 );
			$text     = $item['text'] ?? '';

			// Sin enlace no ponemos <a> (un ancla sin href no es enfocable ni
			// anunciable); el elemento se pinta igual como div.
			$tag = $url ? 'a' : 'div';

			// La imagen va como capa <img> y no como background-image: así sale
			// con srcset y alt, igual que en el resto de bloques del plugin.
			$img = $image_id
				? wp_get_attachment_image( $image_id, 'large', false, array(
					'class'   => 'llao-grid-links__img',
					'alt'     => $item['imageAlt'] ?? '',
					'loading' => 'lazy',
				) )
				: '';
			?>
			<<?php echo $tag; ?> class="llao-grid-links__item"<?php echo $url ? ' href="' . esc_url( $url ) . '"' : ''; ?>>
				<?php echo $img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image / esc_* arriba ?>
				<span class="llao-grid-links__text"><?php echo wp_kses_post( $text ); ?></span>
			</<?php echo $tag; ?>>
		<?php endforeach; ?>
	</div>
</div>
