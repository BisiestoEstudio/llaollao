<?php
/**
 * Render del bloque "Landing".
 * Dinámico: a la izquierda los inner blocks ($content) y a la derecha tres
 * columnas de imágenes en movimiento continuo. La velocidad viaja como custom
 * property; el resto (direcciones, separaciones, bucle) vive en style.css.
 *
 * Las imágenes se reparten por turnos entre las tres columnas (0, 1, 2, 0, 1…)
 * y cada pista se imprime DOS veces: el bucle de la animación se apoya en que
 * la segunda tanda ocupe el hueco de la primera, así que sin el duplicado se
 * vería el salto al reiniciar. Todo el bloque de imágenes es decorativo y va
 * con aria-hidden: son adorno y además saldrían repetidas.
 */

defined( 'ABSPATH' ) || exit;

$images = is_array( $attributes['images'] ?? null ) ? $attributes['images'] : [];
$speed  = max( 5, (int) ( $attributes['speed'] ?? 30 ) );

// Reparto por turnos. La columna del centro (índice 1) sube; las de los lados
// bajan (ver la clase --down en style.css).
$columns = array( array(), array(), array() );
foreach ( array_values( $images ) as $i => $image ) {
	$columns[ $i % 3 ][] = $image;
}

$wrapper = get_block_wrapper_attributes( array(
	'class' => 'llao-landing',
	'style' => sprintf( '--llao-landing-speed:%ds;', $speed ),
) );
?>
<div <?php echo $wrapper; ?>>

	<?php /* is-layout-flow: sin esa clase el core no aplica sus reglas de alineación
	         (aligncenter/left/right) ni el blockGap a los hijos. Ver blocks.css. */ ?>
	<div class="llao-landing__content is-layout-flow"><?php echo $content; ?></div>

	<div class="llao-landing__media" aria-hidden="true">
		<?php foreach ( $columns as $index => $column ) : ?>
			<?php
			// El centro sube, los lados bajan.
			$direction = ( 1 === $index ) ? 'up' : 'down';
			?>
			<div class="llao-landing__col llao-landing__col--<?php echo esc_attr( $direction ); ?>">
				<div class="llao-landing__track">
					<?php for ( $copy = 0; $copy < 2; $copy++ ) : ?>
						<?php foreach ( $column as $image ) : ?>
							<?php
							$id = (int) ( $image['id'] ?? 0 );
							if ( ! $id ) {
								continue;
							}
							echo wp_get_attachment_image( $id, 'medium_large', false, array(
								'class'   => 'llao-landing__img',
								'loading' => 'lazy',
							) );
							?>
						<?php endforeach; ?>
					<?php endfor; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

</div>
