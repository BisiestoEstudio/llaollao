<?php
/**
 * Render del bloque "Video Llaollao".
 *
 * Dinámico (server-side). Muestra la imagen de inicio y, al hacer hover (o tap),
 * carga y reproduce el vídeo en bucle (ver view.js). Aspecto 3:4 con opción
 * vertical (3:4) u horizontal (4:3) según el atributo `orientation`.
 */

defined( 'ABSPATH' ) || exit;

$video_url   = $attributes['videoUrl'] ?? '';
$poster_url  = $attributes['posterUrl'] ?? '';
$poster_alt  = $attributes['posterAlt'] ?? '';
$orientation = ( 'horizontal' === ( $attributes['orientation'] ?? 'vertical' ) ) ? 'horizontal' : 'vertical';
$hide_mobile = ! empty( $attributes['hideOnMobile'] );

$class = 'video-llaollao is-' . $orientation;
if ( $hide_mobile ) {
	$class .= ' is-hidden-mobile';
}
$wrapper = get_block_wrapper_attributes( [ 'class' => $class ] );
?>
<div <?php echo $wrapper; ?>>
	<?php if ( $video_url ) : ?>
		<?php // Carga diferida: la URL va en data-src y view.js la asigna al primer hover/tap. ?>
		<video
			class="video-llaollao__video"
			data-src="<?php echo esc_url( $video_url ); ?>"
			muted
			loop
			playsinline
			preload="none"
			<?php if ( '' !== $poster_url ) : ?>poster="<?php echo esc_url( $poster_url ); ?>"<?php endif; ?>
		></video>
	<?php endif; ?>

	<?php if ( '' !== $poster_url ) : ?>
		<img
			class="video-llaollao__poster"
			src="<?php echo esc_url( $poster_url ); ?>"
			alt="<?php echo esc_attr( $poster_alt ); ?>"
			loading="lazy"
			decoding="async"
		>
	<?php endif; ?>
</div>
