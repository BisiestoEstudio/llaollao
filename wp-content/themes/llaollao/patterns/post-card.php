<?php
/**
 * Title: Post Card Horizontal
 * Slug: llaollao/post-card
 * Categories: query
 * Block Types: core/post-template
 * Inserter: no
 *
 * Card horizontal para el listado del blog: imagen a la izquierda, categorías +
 * título + extracto a la derecha. Pensada para referenciarse con
 * <!-- wp:pattern {"slug":"llaollao/post-card"} /--> dentro de un post-template,
 * de modo que WordPress la inlinea y hereda el contexto de cada entrada.
 */
?>
<!-- wp:group {"className":"post-card","layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"top"}} -->
<div class="wp-block-group post-card">
	<!-- wp:post-featured-image {"isLink":true,"className":"post-card__image"} /-->

	<!-- wp:group {"className":"post-card__body","layout":{"type":"constrained"}} -->
	<div class="wp-block-group post-card__body">
		<!-- wp:post-terms {"term":"category","separator":"","className":"post-card__categories"} /-->

		<!-- wp:post-title {"isLink":true,"level":0,"className":"post-card__title"} /-->

		<!-- wp:post-excerpt {"moreText":"","showMoreOnNewLine":false,"excerptLength":24,"className":"post-card__excerpt"} /-->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
