<?php
/**
 * Render del bloque "Vista producto con slide vertical".
 *
 * Arriba, los hermanos del producto (los demás hijos de su mismo padre) como
 * etiquetas, con el actual marcado. Debajo, dos columnas separadas un 7%: a la
 * izquierda título, contenido, variantes y alérgenos; a la derecha la galería
 * de recursos, con la pieza grande y una tira vertical al lado para cambiarla
 * (view.js).
 *
 * Todo lo que pinta son post meta del CPT (ver meta-fields.php): llao_descripcion
 * (texto), llao_recursos (IDs de adjuntos, imagen o vídeo), llao_variantes
 * (textos) y llao_alergenos (texto).
 *
 * La descripción es un campo propio y no el contenido del producto justamente
 * para que el bloque pueda insertarse dentro de ese mismo producto: si pintara
 * the_content() se encontraría a sí mismo y la recursión no acabaría.
 *
 * El producto es el elegido en el bloque (atributo productId). Sin elegir, cae
 * en el producto que se esté mostrando, de modo que el bloque también sirve
 * puesto en una plantilla de single.
 */

defined( 'ABSPATH' ) || exit;

$post_id  = 0;
$elegido  = (int) ( $attributes['productId'] ?? 0 );

if ( $elegido ) {
	$post_id = $elegido;
} elseif ( isset( $block ) && ! empty( $block->context['postId'] ) ) {
	$post_id = (int) $block->context['postId'];
} else {
	$post_id = (int) get_the_ID();
}

if ( ! $post_id || 'producto' !== get_post_type( $post_id ) ) {
	return;
}

$gap            = max( 0, (float) ( $attributes['columnGap'] ?? 7 ) );
$left_max       = max( 100, (int) ( $attributes['leftMaxWidth'] ?? 400 ) );
$show_siblings  = ! empty( $attributes['showSiblings'] );
$alergenos_lbl  = trim( (string) ( $attributes['alergenosLabel'] ?? '' ) );

if ( '' === $alergenos_lbl ) {
	$alergenos_lbl = __( 'Alérgenos', 'llaollao-core' );
}

// --- Hermanos ----------------------------------------------------------
// Los hijos del mismo padre, el actual incluido y marcado. Un producto de nivel
// superior no tiene padre, así que tampoco fila.
$siblings = array();
$parent   = wp_get_post_parent_id( $post_id );

if ( $show_siblings && $parent ) {
	$siblings = get_posts( array(
		'post_type'        => 'producto',
		'post_status'      => 'publish',
		'post_parent'      => $parent,
		'numberposts'      => -1,
		'orderby'          => 'menu_order title',
		'order'            => 'ASC',
		'suppress_filters' => false,
	) );
}

// --- Campos personalizados ---------------------------------------------
$descripcion = (string) get_post_meta( $post_id, 'llao_descripcion', true );
$recursos    = get_post_meta( $post_id, 'llao_recursos', true );
$variantes   = get_post_meta( $post_id, 'llao_variantes', true );
$alergenos   = (string) get_post_meta( $post_id, 'llao_alergenos', true );

$recursos  = is_array( $recursos ) ? array_values( array_filter( array_map( 'intval', $recursos ) ) ) : array();
$variantes = is_array( $variantes ) ? array_values( array_filter( array_map( 'trim', array_map( 'strval', $variantes ) ) ) ) : array();

// El campo es texto plano: se escapa y luego se reparte en párrafos, de modo
// que un salto de línea doble en el textarea salga como <p> y nada de lo que
// se pegue ahí pueda inyectar marcado.
$descripcion_html = trim( $descripcion ) ? wpautop( esc_html( $descripcion ) ) : '';

$wrapper = get_block_wrapper_attributes( array(
	'class' => 'llao-vista',
	'style' => sprintf( '--llao-vista-gap:%s%%;--llao-vista-left:%dpx;', rtrim( rtrim( number_format( $gap, 2, '.', '' ), '0' ), '.' ), $left_max ),
) );
?>
<div <?php echo $wrapper; ?>>

	<?php if ( $siblings ) : ?>
		<nav class="llao-vista__siblings" aria-label="<?php esc_attr_e( 'Otros productos de la misma familia', 'llaollao-core' ); ?>">
			<?php /* Marcador decorativo: queda fuera de la pista para no irse con el desplazamiento. */ ?>
			<span class="llao-vista__siblings-icon" aria-hidden="true"></span>

			<div class="llao-vista__siblings-track">
				<?php foreach ( $siblings as $sibling ) : ?>
					<?php $actual = ( (int) $sibling->ID === $post_id ); ?>
					<a
						class="llao-vista__sibling button-tag<?php echo $actual ? ' is-active' : ''; ?>"
						href="<?php echo esc_url( get_permalink( $sibling->ID ) ); ?>"
						data-text="<?php echo esc_attr( get_the_title( $sibling->ID ) ); ?>"
						<?php echo $actual ? ' aria-current="page"' : ''; ?>
					><?php echo esc_html( get_the_title( $sibling->ID ) ); ?></a>
				<?php endforeach; ?>
			</div>
		</nav>
	<?php endif; ?>

	<div class="llao-vista__cols">

		<div class="llao-vista__info">
			<h1 class="llao-vista__title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h1>

			<?php if ( $descripcion_html ) : ?>
				<div class="llao-vista__content"><?php echo $descripcion_html; ?></div>
			<?php endif; ?>

			<?php if ( $variantes ) : ?>
				<ul class="llao-vista__variantes">
					<?php foreach ( $variantes as $variante ) : ?>
						<li><span class="llao-vista__variante button-tag" data-text="<?php echo esc_attr( $variante ); ?>"><?php echo esc_html( $variante ); ?></span></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php if ( trim( $alergenos ) ) : ?>
				<details class="llao-vista__alergenos">
					<summary class="llao-vista__alergenos-label"><?php echo esc_html( $alergenos_lbl ); ?></summary>
					<div class="llao-vista__alergenos-body"><?php echo nl2br( esc_html( $alergenos ) ); ?></div>
				</details>
			<?php endif; ?>
		</div>

		<?php if ( $recursos ) : ?>

			<div class="llao-vista__stage">
				<?php foreach ( $recursos as $i => $recurso_id ) : ?>
					<figure class="llao-vista__media<?php echo 0 === $i ? ' is-active' : ''; ?>" data-index="<?php echo (int) $i; ?>">
						<?php if ( wp_attachment_is_image( $recurso_id ) ) : ?>
							<?php
							echo wp_get_attachment_image( $recurso_id, 'large', false, array(
								'class'   => 'llao-vista__media-img',
								'loading' => 0 === $i ? 'eager' : 'lazy',
							) );
							?>
						<?php elseif ( wp_attachment_is( 'video', $recurso_id ) ) : ?>
							<?php
							// El póster del vídeo es su imagen destacada, si la tiene.
							$poster_id  = get_post_thumbnail_id( $recurso_id );
							$poster_url = $poster_id ? wp_get_attachment_image_url( $poster_id, 'large' ) : '';
							?>
							<video
								class="llao-vista__media-video"
								controls
								preload="metadata"
								<?php echo $poster_url ? 'poster="' . esc_url( $poster_url ) . '"' : ''; ?>
								src="<?php echo esc_url( wp_get_attachment_url( $recurso_id ) ); ?>"
							></video>
						<?php endif; ?>
					</figure>
				<?php endforeach; ?>
			</div>

			<?php if ( count( $recursos ) > 1 ) : ?>
				<div class="llao-vista__thumbs" role="group" aria-label="<?php esc_attr_e( 'Elegir imagen', 'llaollao-core' ); ?>">
					<?php foreach ( $recursos as $i => $recurso_id ) : ?>
						<?php
						// En un vídeo la miniatura es su imagen destacada (el póster)
						// si la tiene; si no, queda el hueco con el marcador de play.
						$thumb_id = wp_attachment_is_image( $recurso_id ) ? $recurso_id : get_post_thumbnail_id( $recurso_id );
						$es_video = ! wp_attachment_is_image( $recurso_id );
						?>
						<button
							type="button"
							class="llao-vista__thumb<?php echo 0 === $i ? ' is-active' : ''; ?><?php echo $es_video ? ' is-video' : ''; ?>"
							data-index="<?php echo (int) $i; ?>"
							aria-pressed="<?php echo 0 === $i ? 'true' : 'false'; ?>"
							aria-label="<?php echo esc_attr( sprintf( __( 'Ver recurso %d', 'llaollao-core' ), $i + 1 ) ); ?>"
						>
							<?php
							if ( $thumb_id ) {
								echo wp_get_attachment_image( $thumb_id, 'medium', false, array(
									'class'   => 'llao-vista__thumb-img',
									'loading' => 'lazy',
								) );
							}
							?>
						</button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

		<?php endif; ?>

	</div>
</div>
