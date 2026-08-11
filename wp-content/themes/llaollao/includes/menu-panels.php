<?php
/**
 * Construye el árbol del menú "menu-hamburguesa" y el HTML de los paneles de
 * la mitad derecha del menú a pantalla completa (blocks/header/render.php).
 *
 * Regla de prioridad: si un item tiene hijos, el panel siempre muestra esos
 * hijos en scroll continuo (texto izq. + imagen destacada del hijo a la
 * derecha), sin mirar el campo "Panel" del propio item. Solo los items sin
 * hijos usan Nada / Imagen destacada / Grid / Scroll vertical / Scroll
 * oblicuo, con las imágenes del campo Galería.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Items de primer nivel de una ubicación de menú, cada uno con
 * ->llao_children (array de items hijos directos).
 */
function bisiesto_get_menu_tree( $location ) {
	$locations = get_nav_menu_locations();
	if ( empty( $locations[ $location ] ) ) {
		return array();
	}

	$menu = wp_get_nav_menu_object( $locations[ $location ] );
	if ( ! $menu ) {
		return array();
	}

	$items = wp_get_nav_menu_items( $menu, array( 'update_post_term_cache' => false ) );
	if ( ! $items ) {
		return array();
	}

	$by_parent = array();
	foreach ( $items as $item ) {
		$by_parent[ (int) $item->menu_item_parent ][] = $item;
	}

	foreach ( $items as $item ) {
		$item->llao_children = isset( $by_parent[ $item->ID ] ) ? $by_parent[ $item->ID ] : array();
	}

	return isset( $by_parent[0] ) ? $by_parent[0] : array();
}

function bisiesto_menu_item_gallery( $item_id ) {
	$gallery = (array) get_post_meta( $item_id, '_llao_menu_gallery', true );
	return array_values( array_filter( array_map( 'intval', $gallery ) ) );
}

/**
 * HTML del panel de la derecha para un item de primer nivel. Cadena vacía si
 * no hay nada que mostrar ("Nada", o campo sin imágenes).
 */
function bisiesto_render_menu_panel( $item ) {
	if ( ! empty( $item->llao_children ) ) {
		return bisiesto_render_panel_children( $item->llao_children );
	}

	$panel = get_post_meta( $item->ID, '_llao_menu_panel', true );

	switch ( $panel ) {
		case 'featured':
			return bisiesto_render_panel_featured( $item->ID );
		case 'grid':
			return bisiesto_render_panel_grid( $item->ID );
		case 'scroll-vertical':
			return bisiesto_render_panel_scroll_vertical( $item->ID );
		case 'scroll-oblique':
			return bisiesto_render_panel_scroll_oblique( $item->ID );
		default:
			return '';
	}
}

function bisiesto_render_panel_featured( $item_id ) {
	$image_id = (int) get_post_meta( $item_id, '_llao_menu_featured_image', true );
	if ( ! $image_id ) {
		return '';
	}

	return '<div class="site-header__panel-featured">' . wp_get_attachment_image( $image_id, 'large', false, array(
		'class' => 'site-header__panel-featured-img',
	) ) . '</div>';
}

function bisiesto_render_panel_grid( $item_id ) {
	$ids = bisiesto_menu_item_gallery( $item_id );
	if ( ! $ids ) {
		return '';
	}

	// Reparto por turnos entre las 3 columnas, igual que el bloque Landing.
	$columns = array( array(), array(), array() );
	foreach ( $ids as $i => $id ) {
		$columns[ $i % 3 ][] = $id;
	}

	$html = '<div class="site-header__panel-grid" aria-hidden="true">';
	foreach ( $columns as $index => $column ) {
		if ( ! $column ) {
			continue;
		}
		$direction = ( 1 === $index ) ? 'up' : 'down';
		$html     .= '<div class="site-header__panel-grid-col site-header__panel-grid-col--' . esc_attr( $direction ) . '"><div class="site-header__panel-grid-track">';
		for ( $copy = 0; $copy < 2; $copy++ ) {
			foreach ( $column as $id ) {
				$html .= wp_get_attachment_image( $id, 'medium_large', false, array(
					'class'   => 'site-header__panel-grid-img',
					'loading' => 'lazy',
				) );
			}
		}
		$html .= '</div></div>';
	}
	$html .= '</div>';

	return $html;
}

function bisiesto_render_panel_scroll_vertical( $item_id ) {
	$ids = bisiesto_menu_item_gallery( $item_id );
	if ( ! $ids ) {
		return '';
	}

	$html = '<div class="site-header__panel-scroll-vertical" aria-hidden="true"><div class="site-header__panel-scroll-vertical-track">';
	for ( $copy = 0; $copy < 2; $copy++ ) {
		foreach ( $ids as $i => $id ) {
			$align = ( 0 === $i % 2 ) ? 'left' : 'right';
			$html .= '<div class="site-header__panel-scroll-vertical-item site-header__panel-scroll-vertical-item--' . esc_attr( $align ) . '">' . wp_get_attachment_image( $id, 'medium_large', false, array(
				'class'   => 'site-header__panel-scroll-vertical-img',
				'loading' => 'lazy',
			) ) . '</div>';
		}
	}
	$html .= '</div></div>';

	return $html;
}

function bisiesto_render_panel_scroll_oblique( $item_id ) {
	$ids = bisiesto_menu_item_gallery( $item_id );
	if ( ! $ids ) {
		return '';
	}

	$html = '<div class="site-header__panel-scroll-oblique" aria-hidden="true"><div class="site-header__panel-scroll-oblique-rotate"><div class="site-header__panel-scroll-oblique-track">';
	for ( $copy = 0; $copy < 2; $copy++ ) {
		foreach ( $ids as $id ) {
			$html .= wp_get_attachment_image( $id, 'medium_large', false, array(
				'class'   => 'site-header__panel-scroll-oblique-img',
				'loading' => 'lazy',
			) );
		}
	}
	$html .= '</div></div></div>';

	return $html;
}

/**
 * Filas de hijos (texto + imagen). Scroll normal del navegador, sin loop: los
 * hijos se listan una sola vez, sin duplicar contenido.
 */
function bisiesto_render_menu_children_rows( $children ) {
	$rows = '';
	foreach ( $children as $child ) {
		$image_id = (int) get_post_meta( $child->ID, '_llao_menu_featured_image', true );

		$rows .= '<div class="site-header__panel-children-row">';
		$rows .= '<a class="site-header__panel-children-text" href="' . esc_url( $child->url ) . '">' . esc_html( $child->title ) . '</a>';
		if ( $image_id ) {
			$rows .= '<span class="site-header__panel-children-image">' . wp_get_attachment_image( $image_id, 'medium', false, array(
				'class'   => 'site-header__panel-children-img',
				'loading' => 'lazy',
			) ) . '</span>';
		}
		$rows .= '</div>';
	}

	return $rows;
}

function bisiesto_render_panel_children( $children ) {
	return '<div class="site-header__panel-children">' . bisiesto_render_menu_children_rows( $children ) . '</div>';
}
