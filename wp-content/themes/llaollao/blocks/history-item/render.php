<?php
/**
 * Render del bloque "History · año".
 * Dinámico: pinta el año (atributo, texto plano) y debajo los inner blocks
 * ($content). El wrapper lo genera get_block_wrapper_attributes.
 */

defined( 'ABSPATH' ) || exit;

$year = trim( wp_strip_all_tags( (string) ( $attributes['year'] ?? '' ) ) );

$wrapper = get_block_wrapper_attributes( [ 'class' => 'history-item' ] );
?>
<div <?php echo $wrapper; ?>>
	<?php if ( '' !== $year ) : ?>
		<span class="history-item__year"><?php echo esc_html( $year ); ?></span>
	<?php endif; ?>
	<div class="history-item__body">
		<?php echo $content; ?>
	</div>
</div>
