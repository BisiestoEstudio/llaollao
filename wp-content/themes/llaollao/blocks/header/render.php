<?php
$show_lang       = $attributes['showLanguageSwitcher'] ?? true;
$wrapper_attrs   = get_block_wrapper_attributes( [ 'class' => 'site-header' ] );
$logo_id         = get_theme_mod( 'custom_logo' );
$home_url        = esc_url( home_url( '/' ) );
$site_name       = get_bloginfo( 'name' );
?>
<div <?php echo $wrapper_attrs; ?>>
	<div class="site-header__bar">

		<a href="<?php echo $home_url; ?>" class="site-header__logo" aria-label="<?php echo esc_attr( $site_name ); ?>">
			<?php if ( $logo_id ) : ?>
				<?php echo wp_get_attachment_image( $logo_id, 'full', false, [ 'class' => 'site-header__logo-img', 'loading' => 'eager', 'decoding' => 'sync' ] ); ?>
			<?php else : ?>
				<span class="site-header__logo-text"><?php echo esc_html( $site_name ); ?></span>
			<?php endif; ?>
		</a>

		<nav class="site-header__nav" id="site-header-nav" aria-label="<?php esc_attr_e( 'Navegación principal', 'bisiesto' ); ?>">
			<?php echo $content; ?>
		</nav>

		<?php if ( $show_lang && function_exists( 'baygo_lang_switcher_markup' ) ) {
			echo baygo_lang_switcher_markup();
		} ?>

		<button
			class="site-header__hamburger"
			aria-expanded="false"
			aria-controls="site-header-nav"
			aria-label="<?php esc_attr_e( 'Abrir menú', 'bisiesto' ); ?>"
		>
			<svg class="site-header__hamburger-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
				<line x1="3" y1="6"  x2="21" y2="6"  stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
				<line x1="3" y1="12" x2="21" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
				<line x1="3" y1="18" x2="21" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
			</svg>
			<svg class="site-header__close-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
				<line x1="18" y1="6" x2="6"  y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
				<line x1="6"  y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
			</svg>
		</button>

	</div>


</div>
