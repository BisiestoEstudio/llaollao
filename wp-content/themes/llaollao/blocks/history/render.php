<?php
/**
 * Render del bloque "History".
 * Dinámico para poder generar automáticamente la navegación de años: lee el
 * atributo `year` de cada hijo (bisiesto/history-item) y pinta un enlace por
 * año, unidos por una línea. Debajo, los inner blocks ($content) dentro de
 * .history__items. Nav e ítems son dos scrollers horizontales (ver style.css).
 */

defined( 'ABSPATH' ) || exit;

// Años de los hijos, en orden.
$years = [];
if ( isset( $block->parsed_block['innerBlocks'] ) && is_array( $block->parsed_block['innerBlocks'] ) ) {
	foreach ( $block->parsed_block['innerBlocks'] as $inner ) {
		if ( ( $inner['blockName'] ?? '' ) !== 'bisiesto/history-item' ) {
			continue;
		}
		$years[] = trim( wp_strip_all_tags( (string) ( $inner['attrs']['year'] ?? '' ) ) );
	}
}

// Navegación: un enlace por año, con una línea entre enlaces.
$nav = '';
foreach ( $years as $i => $year ) {
	if ( $i > 0 ) {
		$nav .= '<span class="history__nav-line" aria-hidden="true"></span>';
	}
	$label = '' !== $year ? $year : sprintf( __( 'Año %d', 'bisiesto' ), $i + 1 );
	$nav  .= sprintf(
		'<a class="history__nav-link button-tag" href="#" data-index="%d" data-text="%s">%s</a>',
		$i,
		esc_attr( $label ),
		esc_html( $label )
	);
}

$wrapper = get_block_wrapper_attributes( [ 'class' => 'history' ] );
?>
<div <?php echo $wrapper; ?>>
	<?php if ( '' !== $nav ) : ?>
		<nav class="history__nav" aria-label="<?php echo esc_attr__( 'Años', 'bisiesto' ); ?>">
			<?php echo $nav; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- construido con esc_html arriba ?>
		</nav>
	<?php endif; ?>
	<div class="history__items">
		<?php echo $content; ?>
	</div>
</div>
