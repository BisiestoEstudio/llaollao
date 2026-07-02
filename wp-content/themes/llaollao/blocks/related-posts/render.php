<?php
/**
 * Render del bloque "Related posts".
 * Entradas de la misma categoría que la actual (excluyendo la actual),
 * con las mismas cards que el grid de la home.
 */

defined( 'ABSPATH' ) || exit;

$count      = max( 1, (int) ( $attributes['count'] ?? 3 ) );
$current_id = get_the_ID();
$categories = wp_get_post_categories( $current_id );

$args = [
	'post_type'           => 'post',
	'posts_per_page'      => $count,
	'post__not_in'        => [ $current_id ],
	'ignore_sticky_posts' => true,
	'orderby'             => 'date',
	'order'               => 'DESC',
];
if ( $categories ) {
	$args['category__in'] = $categories;
}

$query = new WP_Query( $args );

if ( ! $query->have_posts() ) {
	wp_reset_postdata();
	return;
}

$wrapper = get_block_wrapper_attributes( [ 'class' => 'related-posts' ] );
?>
<section <?php echo $wrapper; ?>>
	<h2 class="related-posts__title"><?php echo esc_html__( 'Related posts', 'baygo' ); ?></h2>

	<ul class="baygo-blog-grid related-posts__grid is-layout-grid">
		<?php
		while ( $query->have_posts() ) :
			$query->the_post();
			?>
			<li>
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="wp-block-post-featured-image">
						<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'medium_large' ); ?></a>
					</div>
				<?php endif; ?>
				<h2 class="wp-block-post-title">
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h2>
				<div class="wp-block-post-date"><?php echo esc_html( get_the_date( 'j \d\e F Y' ) ); ?></div>
			</li>
			<?php
		endwhile;
		?>
	</ul>
</section>
<?php
wp_reset_postdata();
