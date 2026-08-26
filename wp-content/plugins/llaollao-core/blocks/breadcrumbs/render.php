<?php
/**
 * Migas de pan del single de producto.
 *
 * Estructura: Carta / {Tipo de primer nivel} / {Productos superiores} / {Producto}
 *
 * Los productos superiores salen de la jerarquía del CPT (cada escalón es un
 * producto padre del actual); el escalón de Tipo es el término raíz (sin
 * padre) de la rama de la taxonomía "tipo" a la que pertenece el producto —
 * no toda la cadena, solo el de primer nivel (si tiene varios términos
 * "tipo", se usa el primero).
 *
 * - "Carta" enlaza a la página configurada en Ajustes → Llaollao.
 * - El escalón de Tipo enlaza a esa misma página de Carta con ?tipo={slug}:
 *   el bloque Carta (view.js) lee ese parámetro al cargar y aplica el filtro
 *   de esa etiqueta solo. Sin página de Carta configurada, sin enlace.
 * - Los productos superiores sí enlazan a su single, salvo que no estén
 *   publicados (un borrador no tiene URL pública que ofrecer).
 */

defined( 'ABSPATH' ) || exit;

$product_id = get_the_ID();
if ( ! $product_id || 'producto' !== get_post_type( $product_id ) ) {
	return;
}

if ( get_post_meta( $product_id, 'llao_ocultar_breadcrumbs', true ) ) {
	return;
}

$crumbs = array();

// 1. Carta (página configurable).
$carta_id  = llao_get_carta_page_id();
$carta_url = $carta_id ? get_permalink( $carta_id ) : '';

$crumbs[] = array(
	'label' => $carta_id ? get_the_title( $carta_id ) : __( 'Carta', 'llaollao-core' ),
	'url'   => $carta_url,
);

// 2. Taxonomía "tipo": solo el término raíz (primer nivel) de la rama del
// producto (el primero, si tiene varios). Enlaza a la Carta con ese tipo
// filtrado (?tipo=slug); sin página de Carta, se queda sin enlace.
$tipo_terms = get_the_terms( $product_id, 'tipo' );
if ( $tipo_terms && ! is_wp_error( $tipo_terms ) ) {
	$tipo_term = reset( $tipo_terms );

	while ( $tipo_term->parent ) {
		$tipo_parent = get_term( $tipo_term->parent, 'tipo' );
		if ( ! $tipo_parent || is_wp_error( $tipo_parent ) ) {
			break;
		}
		$tipo_term = $tipo_parent;
	}

	$crumbs[] = array(
		'label' => $tipo_term->name,
		'url'   => $carta_url ? add_query_arg( 'tipo', $tipo_term->slug, $carta_url ) : '',
	);
}

// 3. Productos superiores, del más lejano al más cercano.
foreach ( array_reverse( get_post_ancestors( $product_id ) ) as $ancestor_id ) {
	$crumbs[] = array(
		'label' => get_the_title( $ancestor_id ),
		'url'   => 'publish' === get_post_status( $ancestor_id ) ? get_permalink( $ancestor_id ) : '',
	);
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
