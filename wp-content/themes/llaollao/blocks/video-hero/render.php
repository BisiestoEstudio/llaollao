<?php
/**
 * Render del bloque "Video hero".
 *
 * Dinámico (server-side) para no romper la validación al traducir alt/atributos.
 * - Vídeo a tamaño completo (object-fit: cover). Puede haber uno para
 *   escritorio y otro distinto para móvil (videoMobileUrl); sin elegir uno de
 *   móvil, se reutiliza el de escritorio en las dos versiones. Los dos <video>
 *   van en el DOM y style.css oculta el que no toca según el ancho — solo se
 *   descarga el visible, porque view.js no le asigna `src` al oculto.
 * - Portada: si hay imagen se usa como capa superior (y como `poster` de
 *   ambos vídeos); si está vacía, se usa el primer frame del vídeo (fragmento
 *   #t=0.1).
 * - Contenido ($content = InnerBlocks) superpuesto: un heading de Gutenberg.
 * El hover (escritorio) / visibilidad (móvil) que reproduce el vídeo lo
 * gestiona view.js.
 */

defined( 'ABSPATH' ) || exit;

$video_url        = trim( $attributes['videoUrl'] ?? '' );
$video_mobile_url = trim( $attributes['videoMobileUrl'] ?? '' );
$poster_url       = $attributes['posterUrl'] ?? '';
$poster_alt       = $attributes['posterAlt'] ?? '';

// Vídeo de móvil propio distinto del de escritorio: si no hay uno elegido, o
// es el mismo, se pinta un único <video> sin modificador, como hasta ahora.
$has_mobile_video = '' !== $video_mobile_url && $video_mobile_url !== $video_url;

$wrapper = get_block_wrapper_attributes( [ 'class' => 'video-hero' ] );
?>
<div <?php echo $wrapper; ?>>
	<?php if ( $video_url ) : ?>
		<?php // Carga diferida: la URL va en data-src y view.js la asigna al primer hover/tap. ?>
		<video
			class="video-hero__video<?php echo $has_mobile_video ? ' video-hero__video--desktop' : ''; ?>"
			data-src="<?php echo esc_url( $video_url ); ?>"
			muted
			loop
			playsinline
			preload="none"
			<?php if ( '' !== $poster_url ) : ?>poster="<?php echo esc_url( $poster_url ); ?>"<?php endif; ?>
		></video>
	<?php endif; ?>

	<?php if ( $has_mobile_video ) : ?>
		<video
			class="video-hero__video video-hero__video--mobile"
			data-src="<?php echo esc_url( $video_mobile_url ); ?>"
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
