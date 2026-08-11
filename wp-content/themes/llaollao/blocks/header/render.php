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

		<button
			class="site-header__menu-toggle"
			aria-expanded="false"
			aria-controls="site-header-menu"
			aria-label="<?php esc_attr_e( 'Abrir menú', 'bisiesto' ); ?>"
		>
			<svg class="site-header__menu-toggle-icon--open" xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 30 30" fill="none" aria-hidden="true">
				<path d="M5 10H25" stroke="#64A50A" stroke-width="2"/>
				<path d="M5 15H25" stroke="#64A50A" stroke-width="2"/>
				<path d="M5 20H25" stroke="#64A50A" stroke-width="2"/>
			</svg>
			<svg class="site-header__menu-toggle-icon--close" xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 30 30" fill="none" aria-hidden="true">
				<path d="M3.75 11.75C3.75 8.94974 3.75 7.54961 4.29497 6.48005C4.77433 5.53924 5.53924 4.77433 6.48005 4.29497C7.54961 3.75 8.94974 3.75 11.75 3.75H18.25C21.0503 3.75 22.4504 3.75 23.52 4.29497C24.4608 4.77433 25.2257 5.53924 25.705 6.48005C26.25 7.54961 26.25 8.94974 26.25 11.75V18.25C26.25 21.0503 26.25 22.4504 25.705 23.52C25.2257 24.4608 24.4608 25.2257 23.52 25.705C22.4504 26.25 21.0503 26.25 18.25 26.25H11.75C8.94974 26.25 7.54961 26.25 6.48005 25.705C5.53924 25.2257 4.77433 24.4608 4.29497 23.52C3.75 22.4504 3.75 21.0503 3.75 18.25V11.75Z" stroke="#64A50A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M18.75 11.25L11.25 18.75M11.25 11.25L18.7499 18.75" stroke="#64A50A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
		</button>

		<?php if ( $show_lang && function_exists( 'baygo_lang_switcher_markup' ) ) {
			echo baygo_lang_switcher_markup();
		} ?>

	</div>

	<?php $menu_items = bisiesto_get_menu_tree( 'menu-hamburguesa' ); ?>

	<div class="site-header__menu-overlay" id="site-header-menu">

		<div class="site-header__menu-left">
			<ul class="site-header__menu-list">
				<?php foreach ( $menu_items as $item ) : ?>
					<li>
						<a href="<?php echo esc_url( $item->url ); ?>" data-menu-item="<?php echo esc_attr( $item->ID ); ?>">
							<?php echo esc_html( $item->title ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<div class="site-header__menu-right">
			<?php foreach ( $menu_items as $item ) : ?>
				<?php $panel_html = bisiesto_render_menu_panel( $item ); ?>
				<?php if ( '' === $panel_html ) : ?>
					<?php continue; ?>
				<?php endif; ?>
				<div class="site-header__menu-panel" data-menu-item="<?php echo esc_attr( $item->ID ); ?>">
					<?php echo $panel_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- construido en includes/menu-panels.php ya escapado pieza a pieza. ?>
				</div>
			<?php endforeach; ?>
		</div>

	</div>

</div>
