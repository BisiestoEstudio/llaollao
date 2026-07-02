<?php
/**
 * Render del bloque "Video hero".
 *
 * Dinámico (server-side) para no romper la validación al traducir alt/atributos.
 * - Vídeo a tamaño completo (object-fit: cover).
 * - Portada: si hay imagen se usa como capa superior (y como `poster` del vídeo);
 *   si está vacía, se usa el primer frame del vídeo (fragmento #t=0.1).
 * - Contenido ($content = InnerBlocks) superpuesto: un heading de Gutenberg.
 * El hover (escritorio) / tap (móvil) que reproduce el vídeo lo gestiona view.js.
 */

defined( 'ABSPATH' ) || exit;

$video_url  = $attributes['videoUrl'] ?? '';
$poster_url = $attributes['posterUrl'] ?? '';
$poster_alt = $attributes['posterAlt'] ?? '';

$wrapper = get_block_wrapper_attributes( [ 'class' => 'video-hero' ] );
?>
<div <?php echo $wrapper; ?>>
	<?php if ( $video_url ) : ?>
		<?php // Carga diferida: la URL va en data-src y view.js la asigna al primer hover/tap. ?>
		<video
			class="video-hero__video"
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
			class="video-hero__poster"
			src="<?php echo esc_url( $poster_url ); ?>"
			alt="<?php echo esc_attr( $poster_alt ); ?>"
			loading="eager"
			decoding="async"
		>
	<?php endif; ?>

	<div class="video-hero__content">
		<?php echo $content; ?>
	</div>
</div>
