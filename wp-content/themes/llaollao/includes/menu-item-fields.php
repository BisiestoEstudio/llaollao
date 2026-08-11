<?php
/**
 * Campos propios en los items de menú (Apariencia → Menús): imagen destacada,
 * galería y tipo de panel. Los lee blocks/header/render.php para construir el
 * panel de la derecha del menú a pantalla completa (ver includes/menu-panels.php).
 */

defined( 'ABSPATH' ) || exit;

function bisiesto_menu_panel_options() {
	return array(
		''                => __( 'Nada', 'bisiesto' ),
		'featured'        => __( 'Imagen destacada', 'bisiesto' ),
		'grid'            => __( 'Grid', 'bisiesto' ),
		'scroll-vertical' => __( 'Scroll vertical', 'bisiesto' ),
		'scroll-oblique'  => __( 'Scroll oblicuo', 'bisiesto' ),
	);
}

add_action( 'wp_nav_menu_item_custom_fields', 'bisiesto_menu_item_custom_fields', 10, 2 );

function bisiesto_menu_item_custom_fields( $item_id, $item ) {
	$panel    = get_post_meta( $item_id, '_llao_menu_panel', true );
	$image_id = (int) get_post_meta( $item_id, '_llao_menu_featured_image', true );
	$gallery  = array_values( array_filter( array_map( 'intval', (array) get_post_meta( $item_id, '_llao_menu_gallery', true ) ) ) );
	?>
	<p class="field-llao-panel description description-wide">
		<label for="edit-menu-item-llao-panel-<?php echo esc_attr( $item_id ); ?>">
			<?php esc_html_e( 'Panel del menú a pantalla completa', 'bisiesto' ); ?><br />
			<select id="edit-menu-item-llao-panel-<?php echo esc_attr( $item_id ); ?>" class="widefat" name="menu-item-llao-panel[<?php echo esc_attr( $item_id ); ?>]">
				<?php foreach ( bisiesto_menu_panel_options() as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $panel, $value ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
	</p>

	<p class="field-llao-featured-image description description-wide llao-media-field" data-kind="single">
		<label><?php esc_html_e( 'Imagen destacada (panel)', 'bisiesto' ); ?></label><br />
		<span class="llao-media-preview">
			<?php if ( $image_id ) : ?>
				<?php echo wp_get_attachment_image( $image_id, 'thumbnail' ); ?>
			<?php endif; ?>
		</span>
		<input type="hidden" class="llao-media-value" name="menu-item-llao-featured-image[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $image_id ? $image_id : '' ); ?>" />
		<button type="button" class="button llao-media-select"><?php esc_html_e( 'Seleccionar imagen', 'bisiesto' ); ?></button>
		<button type="button" class="button llao-media-remove" <?php echo $image_id ? '' : 'style="display:none;"'; ?>><?php esc_html_e( 'Quitar', 'bisiesto' ); ?></button>
	</p>

	<p class="field-llao-gallery description description-wide llao-media-field" data-kind="gallery">
		<label><?php esc_html_e( 'Galería (panel: Grid / Scroll vertical / Scroll oblicuo)', 'bisiesto' ); ?></label><br />
		<span class="llao-media-preview">
			<?php foreach ( $gallery as $gid ) : ?>
				<?php echo wp_get_attachment_image( $gid, 'thumbnail' ); ?>
			<?php endforeach; ?>
		</span>
		<input type="hidden" class="llao-media-value" name="menu-item-llao-gallery[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( implode( ',', $gallery ) ); ?>" />
		<button type="button" class="button llao-media-select"><?php esc_html_e( 'Seleccionar galería', 'bisiesto' ); ?></button>
		<button type="button" class="button llao-media-remove" <?php echo $gallery ? '' : 'style="display:none;"'; ?>><?php esc_html_e( 'Vaciar', 'bisiesto' ); ?></button>
	</p>
	<?php
}

add_action( 'wp_update_nav_menu_item', 'bisiesto_save_menu_item_fields', 10, 2 );

function bisiesto_save_menu_item_fields( $menu_id, $menu_item_db_id ) {
	if ( isset( $_POST['menu-item-llao-panel'][ $menu_item_db_id ] ) ) {
		$panel = sanitize_key( wp_unslash( $_POST['menu-item-llao-panel'][ $menu_item_db_id ] ) );
		if ( array_key_exists( $panel, bisiesto_menu_panel_options() ) ) {
			update_post_meta( $menu_item_db_id, '_llao_menu_panel', $panel );
		}
	}

	if ( isset( $_POST['menu-item-llao-featured-image'][ $menu_item_db_id ] ) ) {
		$image_id = (int) $_POST['menu-item-llao-featured-image'][ $menu_item_db_id ];
		if ( $image_id > 0 ) {
			update_post_meta( $menu_item_db_id, '_llao_menu_featured_image', $image_id );
		} else {
			delete_post_meta( $menu_item_db_id, '_llao_menu_featured_image' );
		}
	}

	if ( isset( $_POST['menu-item-llao-gallery'][ $menu_item_db_id ] ) ) {
		$raw = sanitize_text_field( wp_unslash( $_POST['menu-item-llao-gallery'][ $menu_item_db_id ] ) );
		$ids = array_values( array_filter( array_map( 'intval', explode( ',', $raw ) ) ) );
		if ( $ids ) {
			update_post_meta( $menu_item_db_id, '_llao_menu_gallery', $ids );
		} else {
			delete_post_meta( $menu_item_db_id, '_llao_menu_gallery' );
		}
	}
}

add_action( 'admin_enqueue_scripts', 'bisiesto_menu_item_fields_assets' );

function bisiesto_menu_item_fields_assets( $hook ) {
	if ( 'nav-menus.php' !== $hook ) {
		return;
	}

	wp_enqueue_media();

	wp_enqueue_style(
		'bisiesto-menu-item-fields',
		get_theme_file_uri( 'assets/css/menu-item-fields.css' ),
		array(),
		wp_get_theme()->get( 'Version' )
	);

	wp_enqueue_script(
		'bisiesto-menu-item-fields',
		get_theme_file_uri( 'assets/js/menu-item-fields.js' ),
		array(),
		wp_get_theme()->get( 'Version' ),
		true
	);

	wp_localize_script( 'bisiesto-menu-item-fields', 'bisiestoMenuItemFields', array(
		'selectImageTitle'   => __( 'Seleccionar imagen', 'bisiesto' ),
		'selectGalleryTitle' => __( 'Seleccionar galería', 'bisiesto' ),
		'useTitle'           => __( 'Usar', 'bisiesto' ),
	) );
}
