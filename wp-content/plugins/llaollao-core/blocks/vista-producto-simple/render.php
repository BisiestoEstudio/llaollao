<?php
/**
 * Render del bloque "Vista producto".
 *
 * De arriba abajo: la nube de etiquetas de la taxonomía "tipo" del producto, el
 * título de su producto padre (un h2 con el tamaño de un h1), la fila de
 * hermanos y, por último, dos columnas: a la izquierda el título del producto
 * (un h1 con el tamaño de un h3), su descripción y los alérgenos; a la derecha
 * el primer recurso, al 70% del ancho.
 *
 * Sin galería ni slide: para eso está "Vista producto con slide vertical".
 *
 * Todo lo que pinta son post meta del CPT (ver meta-fields.php): llao_descripcion
 * (texto), llao_recursos (IDs de adjuntos, imagen o vídeo) y llao_alergenos
 * (texto). La descripción es un campo propio y no el contenido del producto,
 * así que el bloque puede insertarse dentro del propio producto sin recursión.
 *
 * El producto es el elegido en el bloque (atributo productId). Sin elegir, cae
 * en el producto que se esté mostrando.
 */

defined( 'ABSPATH' ) || exit;

$post_id = 0;
$elegido = (int) ( $attributes['productId'] ?? 0 );

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

$media_width   = min( 95, max( 20, (int) ( $attributes['mediaWidth'] ?? 70 ) ) );
$gap           = max( 0, (int) ( $attributes['columnGap'] ?? 64 ) );
$show_tipos    = ! empty( $attributes['showTipos'] );
$reduced_info  = ! empty( $attributes['reducedInfo'] );
$alergenos_lbl = trim( (string) ( $attributes['alergenosLabel'] ?? '' ) );

if ( '' === $alergenos_lbl ) {
	$alergenos_lbl = __( 'Alérgenos', 'llaollao-core' );
}

// --- Etiquetas de tipo -------------------------------------------------
// La fila no enseña solo los términos del producto: enseña también los de su
// mismo nivel, para que se vean las alternativas y no una etiqueta suelta. En
// una taxonomía jerárquica como esta, "mismo nivel" es compartir padre.
// Los que el producto sí tiene salen marcados.
//
// La taxonomía no es pública (sin archivo ni query var), así que van como
// etiquetas de solo lectura, sin enlace.
$tipos         = array();
$tipos_activos = array();

if ( $show_tipos ) {
	$propios = get_the_terms( $post_id, 'tipo' );

	if ( $propios && ! is_wp_error( $propios ) ) {
		$tipos_activos = wp_list_pluck( $propios, 'term_id' );

		$primero = reset( $propios );
		$mismo_nivel = get_terms( array(
			'taxonomy'   => 'tipo',
			'parent'     => (int) $primero->parent,
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		) );

		// Si la consulta falla, al menos se pintan los del producto.
		$tipos = ( $mismo_nivel && ! is_wp_error( $mismo_nivel ) ) ? $mismo_nivel : $propios;
	}
}

// --- Padre y hermanos --------------------------------------------------
$parent   = wp_get_post_parent_id( $post_id );
$siblings = array();

if ( $parent ) {
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
$alergenos   = get_post_meta( $post_id, 'llao_alergenos', true );
$alergenos   = is_array( $alergenos ) ? array_values( array_filter( array_map( 'intval', $alergenos ) ) ) : array();
$recursos    = get_post_meta( $post_id, 'llao_recursos', true );
$recursos    = is_array( $recursos ) ? array_values( array_filter( array_map( 'intval', $recursos ) ) ) : array();

// Solo el primero: este bloque no tiene galería.
$recurso_id = $recursos ? $recursos[0] : 0;

// Texto plano: se escapa y luego se reparte en párrafos, de modo que un salto
// de línea doble salga como <p> y nada de lo pegado ahí inyecte marcado.
$descripcion_html = trim( $descripcion ) ? wpautop( esc_html( $descripcion ) ) : '';

$wrapper = get_block_wrapper_attributes( array(
	'class' => 'llao-vp',
	'style' => sprintf( '--llao-vp-media:%d%%;--llao-vp-gap:%dpx;', $media_width, $gap ),
) );
?>
<div <?php echo $wrapper; ?>>

	<?php if ( $tipos ) : ?>
		<div class="llao-vp__tipos">
			<?php /* Marcador decorativo: fuera de la pista para no irse con el desplazamiento. */ ?>
			<span class="llao-vp__tipos-icon" aria-hidden="true"></span>

			<ul class="llao-vp__tipos-track">
				<?php foreach ( $tipos as $tipo ) : ?>
					<?php $suyo = in_array( (int) $tipo->term_id, array_map( 'intval', $tipos_activos ), true ); ?>
					<li>
						<span
							class="llao-vp__tipo button-tag<?php echo $suyo ? ' is-active' : ''; ?>"
							data-text="<?php echo esc_attr( $tipo->name ); ?>"
						><?php echo esc_html( $tipo->name ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<?php if ( $parent ) : ?>
		<h2 class="llao-vp__familia"><?php echo esc_html( get_the_title( $parent ) ); ?></h2>
	<?php endif; ?>

	<?php if ( $siblings ) : ?>
		<nav class="llao-vp__hermanos" aria-label="<?php esc_attr_e( 'Otros productos de la misma familia', 'llaollao-core' ); ?>">
			<?php foreach ( $siblings as $sibling ) : ?>
				<?php $actual = ( (int) $sibling->ID === $post_id ); ?>
				<a
					class="llao-vp__hermano<?php echo $actual ? ' is-active' : ''; ?>"
					href="<?php echo esc_url( get_permalink( $sibling->ID ) ); ?>"
					<?php echo $actual ? ' aria-current="page"' : ''; ?>
				>
					<span class="llao-vp__hermano-img">
						<?php
						// Sin imagen destacada queda el círculo en beige, que es
						// preferible a un hueco desalineando la fila.
						echo get_the_post_thumbnail( $sibling->ID, 'thumbnail', array(
							'class'   => 'llao-vp__hermano-thumb',
							'loading' => 'lazy',
						) );
						?>
					</span>
					<span class="llao-vp__hermano-label"><?php echo esc_html( get_the_title( $sibling->ID ) ); ?></span>
				</a>
			<?php endforeach; ?>
		</nav>
	<?php endif; ?>

	<?php if ( ! $reduced_info ) : ?>
	<div class="llao-vp__cols">

		<div class="llao-vp__info">
			<h1 class="llao-vp__title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h1>

			<?php if ( $descripcion_html ) : ?>
				<div class="llao-vp__content"><?php echo $descripcion_html; ?></div>
			<?php endif; ?>

			<?php if ( $alergenos ) : ?>
				<details class="llao-vp__alergenos">
					<summary class="llao-vp__alergenos-label"><?php echo esc_html( $alergenos_lbl ); ?></summary>
					<div class="llao-vp__alergenos-body">
						<div class="llao-vp__alergenos-grid">
							<?php foreach ( $alergenos as $alergeno_id ) : ?>
								<?php
								echo wp_get_attachment_image( $alergeno_id, 'thumbnail', false, array(
									'class'   => 'llao-vp__alergeno-img',
									'loading' => 'lazy',
								) );
								?>
							<?php endforeach; ?>
						</div>
					</div>
				</details>
			<?php endif; ?>
		</div>

		<?php if ( $recurso_id ) : ?>
			<figure class="llao-vp__media">
				<?php if ( wp_attachment_is_image( $recurso_id ) ) : ?>
					<?php
					echo wp_get_attachment_image( $recurso_id, 'large', false, array(
						'class' => 'llao-vp__media-img',
					) );
					?>
				<?php elseif ( wp_attachment_is( 'video', $recurso_id ) ) : ?>
					<?php
					// El póster del vídeo es su imagen destacada, si la tiene.
					$poster_id  = get_post_thumbnail_id( $recurso_id );
					$poster_url = $poster_id ? wp_get_attachment_image_url( $poster_id, 'large' ) : '';
					?>
					<video
						class="llao-vp__media-video"
						controls
						preload="metadata"
						<?php echo $poster_url ? 'poster="' . esc_url( $poster_url ) . '"' : ''; ?>
						src="<?php echo esc_url( wp_get_attachment_url( $recurso_id ) ); ?>"
					></video>
				<?php endif; ?>
			</figure>
		<?php endif; ?>

	</div>
	<?php endif; ?>

</div>
