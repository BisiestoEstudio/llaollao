<?php
/**
 * Render del bloque "FAQs".
 * Dinámico (server-side) para evitar errores de validación / recuperación
 * al traducir con Polylang. El contenido libre superior se conserva como
 * InnerBlocks ($content); las preguntas salen de los atributos.
 */

defined( 'ABSPATH' ) || exit;

$items   = is_array( $attributes['items'] ?? null ) ? $attributes['items'] : [];
$wrapper = get_block_wrapper_attributes( array( 'class' => 'faqs' ) );
?>
<div <?php echo $wrapper; ?>>
	<div class="faqs__header is-layout-flow"><?php echo $content; ?></div>

	<div class="faqs__list">
		<?php foreach ( $items as $item ) : ?>
			<details class="faq">
				<summary class="faq__summary">
					<span class="faq__question"><?php echo wp_kses_post( $item['question'] ?? '' ); ?></span>
					<svg class="faq__chevron" viewBox="0 0 20 20" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<path d="M5 7.5L10 12.5L15 7.5" />
					</svg>
				</summary>
				<p class="faq__answer"><?php echo wp_kses_post( $item['answer'] ?? '' ); ?></p>
			</details>
		<?php endforeach; ?>
	</div>
</div>
