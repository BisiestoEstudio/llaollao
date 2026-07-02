<?php
/**
 * Selector de idiomas reutilizable (Polylang).
 *
 * Genera el mismo markup que usa la cabecera general (.site-header__lang)
 * para poder reutilizarlo en otras cabeceras vía el shortcode [baygo_lang_switcher].
 */

defined( 'ABSPATH' ) || exit;

/**
 * Devuelve el HTML del selector de idiomas con el markup de la cabecera general.
 */
function baygo_lang_switcher_markup(): string {
	if ( ! function_exists( 'pll_the_languages' ) ) {
		return '';
	}

	ob_start();
	?>
	<div class="site-header__lang">
		<div class="site-header__lang-wrapper">
			<?php pll_the_languages( [
				'show_flags'       => 0,
				'show_names'       => 1,
				'display_names_as' => 'slug',
				'hide_current'     => 0,
				'force_home'       => 0,
				'dropdown'         => 1,
			] ); ?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

add_shortcode( 'baygo_lang_switcher', 'baygo_lang_switcher_markup' );
