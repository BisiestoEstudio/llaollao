<?php
/**
 * Migas de pan del single de producto.
 *
 * Estructura: Carta / Productos / {Tipo del producto} / {Nombre del producto}
 *
 * - "Carta" enlaza a la página configurada en Ajustes → Llaollao.
 * - "Productos" y "Tipo" van sin enlace: el CPT no tiene archivo público y la
 *   taxonomía no es pública, así que no hay URL válida a la que apuntar.
 */

defined( 'ABSPATH' ) || exit;

$product_id = get_the_ID();
if ( ! $product_id || 'producto' !== get_post_type( $product_id ) ) {
	return;
}

$crumbs = array();

// 1. Carta (página configurable).
$carta_id = llao_get_carta_page_id();
if ( $carta_id ) {
	$crumbs[] = array(
		'label' => get_the_title( $carta_id ),
		'url'   => get_permalink( $carta_id ),
	);
} else {
	$crumbs[] = array( 'label' => __( 'Carta', 'llaollao-core' ), 'url' => '' );
}

// 2. Productos (sin enlace: el CPT no tiene archivo público).
$pt       = get_post_type_object( 'producto' );
$crumbs[] = array(
	'label' => $pt ? $pt->labels->name : __( 'Productos', 'llaollao-core' ),
	'url'   => '',
);

// 3. Tipo del producto actual (primer término; taxonomía no pública → sin enlace).
$terms = get_the_terms( $product_id, 'tipo' );
if ( $terms && ! is_wp_error( $terms ) ) {
	$term     = array_shift( $terms );
	$crumbs[] = array( 'label' => $term->name, 'url' => '' );
}

// 4. Producto actual.
$crumbs[] = array( 'label' => get_the_title( $product_id ), 'url' => '' );

$wrapper = get_block_wrapper_attributes( array( 'class' => 'llao-breadcrumbs' ) );
$last    = count( $crumbs ) - 1;
?>
<nav <?php echo $wrapper; ?> aria-label="<?php esc_attr_e( 'Migas de pan', 'llaollao-core' ); ?>">
	<ol class="llao-breadcrumbs__list">
		<?php foreach ( $crumbs as $i => $crumb ) : ?>
			<li class="llao-breadcrumbs__item">
				<?php if ( ! empty( $crumb['url'] ) ) : ?>
					<a class="llao-breadcrumbs__link" href="<?php echo esc_url( $crumb['url'] ); ?>"><?php echo esc_html( $crumb['label'] ); ?></a>
				<?php else : ?>
					<span class="llao-breadcrumbs__current"<?php echo $i === $last ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $crumb['label'] ); ?></span>
				<?php endif; ?>
				<?php if ( $i !== $last ) : ?>
					<span class="llao-breadcrumbs__sep" aria-hidden="true">/</span>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ol>
</nav>
