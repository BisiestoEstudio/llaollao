<?php
/**
 * Render del bloque "Carta".
 *
 * Dos piezas: la nube de etiquetas de la taxonomía "tipo" y el mosaico de
 * productos a dos columnas. El alto de cada card sale de un ciclo fijo por
 * posición (745 / 504 / 660) para dar el escalonado del masonry; el mosaico en
 * sí es un multi-columna de CSS (ver style.css) y el filtrado lo hace view.js
 * ocultando cards, sin recargar.
 *
 * La primera card lleva .is-wide y ocupa las dos columnas (column-span). Al
 * filtrar, view.js pasa esa clase a la primera que quede visible, para que el
 * mosaico siempre abra con una card ancha.
 *
 * Los productos son los elegidos a mano en el bloque, en ese mismo orden. Sin
 * selección, entran todos los publicados (padres e hijos) por el campo Orden.
 */

defined( 'ABSPATH' ) || exit;

$ids          = array_values( array_filter( array_map( 'intval', (array) ( $attributes['productIds'] ?? array() ) ) ) );
$show_filters = ! empty( $attributes['showFilters'] );
$all_label    = trim( (string) ( $attributes['allLabel'] ?? '' ) );
$gap          = max( 0, (int) ( $attributes['gap'] ?? 20 ) );

if ( '' === $all_label ) {
	$all_label = __( 'Todos', 'llaollao-core' );
}

$args = array(
	'post_type'           => 'producto',
	'post_status'         => 'publish',
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
);

if ( $ids ) {
	// post__in + orderby post__in respeta el orden de la lista del bloque.
	$args['post__in']       = $ids;
	$args['orderby']        = 'post__in';
	$args['posts_per_page'] = count( $ids );
} else {
	$args['orderby']        = array( 'menu_order' => 'ASC', 'title' => 'ASC' );
	$args['posts_per_page'] = -1;
}

$query = new WP_Query( $args );

if ( ! $query->have_posts() ) {
	return;
}

/*
 * Primero se recorre la consulta entera y luego se pinta: la nube de etiquetas
 * va arriba, pero solo debe listar los tipos que de verdad tienen alguna card
 * en este mosaico, y eso no se sabe hasta haberlas visto todas.
 */
$cards = array();
$types = array(); // slug => nombre.

while ( $query->have_posts() ) {
	$query->the_post();

	$post_id = get_the_ID();
	$slugs   = array();

	$terms = get_the_terms( $post_id, 'tipo' );
	if ( $terms && ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			$slugs[]              = $term->slug;
			$types[ $term->slug ] = $term->name;
		}
	}

	$cards[] = array(
		'id'    => $post_id,
		'title' => get_the_title( $post_id ),
		'url'   => get_permalink( $post_id ),
		'thumb' => get_the_post_thumbnail( $post_id, 'large', array(
			'class'   => 'llao-carta__img',
			'loading' => 'lazy',
		) ),
		'slugs' => $slugs,
	);
}

wp_reset_postdata();

asort( $types, SORT_NATURAL | SORT_FLAG_CASE );

// Ciclo de altos en escritorio, en el orden en que se pidió el escalonado.
$heights = array( 745, 504, 660 );

$wrapper = get_block_wrapper_attributes( array(
	'class' => 'llao-carta',
	'style' => sprintf( '--llao-carta-gap:%dpx;', $gap ),
) );
?>
<div <?php echo $wrapper; ?>>

	<?php if ( $show_filters && $types ) : ?>
		<div class="llao-carta__filters" role="group" aria-label="<?php esc_attr_e( 'Filtrar por tipo', 'llaollao-core' ); ?>">
			<button
				type="button"
				class="llao-carta__filter button-tag is-active"
				data-filter=""
				data-text="<?php echo esc_attr( $all_label ); ?>"
				aria-pressed="true"
			><?php echo esc_html( $all_label ); ?></button>

			<?php foreach ( $types as $slug => $name ) : ?>
				<button
					type="button"
					class="llao-carta__filter button-tag"
					data-filter="<?php echo esc_attr( $slug ); ?>"
					data-text="<?php echo esc_attr( $name ); ?>"
					aria-pressed="false"
				><?php echo esc_html( $name ); ?></button>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<div class="llao-carta__grid">
		<?php foreach ( $cards as $i => $card ) : ?>
			<a
				class="llao-carta__card<?php echo 0 === $i ? ' is-wide' : ''; ?>"
				href="<?php echo esc_url( $card['url'] ); ?>"
				style="--llao-carta-card-h:<?php echo (int) $heights[ $i % count( $heights ) ]; ?>px;"
				data-tipos="<?php echo esc_attr( implode( ' ', $card['slugs'] ) ); ?>"
			>
				<div class="llao-carta__media">
					<?php echo $card['thumb'] ? $card['thumb'] : ''; ?>
				</div>
				<div class="llao-carta__body">
					<h3 class="llao-carta__title"><?php echo esc_html( $card['title'] ); ?></h3>
				</div>
			</a>
		<?php endforeach; ?>
	</div>

	<p class="llao-carta__empty" hidden><?php esc_html_e( 'No hay productos de este tipo.', 'llaollao-core' ); ?></p>
</div>
