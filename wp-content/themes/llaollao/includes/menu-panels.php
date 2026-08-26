<?php
/**
 * Construye el árbol del menú "menu-hamburguesa" y el HTML de los paneles de
 * la mitad derecha del menú a pantalla completa (blocks/header/render.php).
 *
 * Regla de prioridad: si un item tiene hijos, el panel siempre muestra esos
 * hijos en dos columnas con scroll continuo (nombres a la izquierda bajando,
 * imágenes destacadas de los hijos a la derecha subiendo), sin mirar el
 * campo "Panel" del propio item. Solo los items sin hijos usan Nada / Imagen
 * destacada / Grid / Scroll vertical / Scroll oblicuo, con las imágenes del
 * campo Galería.
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

/**
 * Panel "Imagen destacada": pila de cartas (las imágenes de la Galería),
 * cada una con su propia rotación fija (por posición, no aleatoria, para que
 * no cambie de una carga a otra) y un retraso de 1,5s por carta, de modo que
 * al hacer hover (.is-active en el panel padre) vayan apareciendo una a una.
 */
function bisiesto_render_panel_featured( $item_id ) {
	$ids = bisiesto_menu_item_gallery( $item_id );
	if ( ! $ids ) {
		return '';
	}

	$rotations = array( -8, 6, -4, 9, -6, 4, -9, 7 );

	$html = '<div class="site-header__panel-featured" aria-hidden="true"><div class="site-header__panel-featured-cards">';
	foreach ( $ids as $i => $id ) {
		$style = sprintf(
			'--llao-card-rot:%ddeg;--llao-card-delay:%ss;z-index:%d;',
			$rotations[ $i % count( $rotations ) ],
			round( $i * 1.5, 2 ),
			$i
		);
		$html .= wp_get_attachment_image( $id, 'large', false, array(
			'class'   => 'site-header__panel-featured-img',
			'loading' => 'lazy',
			'style'   => $style,
		) );
	}
	$html .= '</div></div>';

	return $html;
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
			// sizes fijo (70% de los 260px de la columna): mismo motivo que en
			// el panel oblicuo, para que no lo calcule mal a partir del ancho
			// original de cada imagen.
			$html .= '<div class="site-header__panel-scroll-vertical-item site-header__panel-scroll-vertical-item--' . esc_attr( $align ) . '">' . wp_get_attachment_image( $id, 'medium_large', false, array(
				'class'   => 'site-header__panel-scroll-vertical-img',
				'loading' => 'lazy',
				'sizes'   => '182px',
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
			// sizes fijo, igual que el max-width real de la imagen en style.css
			// (.site-header__panel-scroll-oblique-img): sin esto, WordPress
			// calcula un "sizes" a partir del ancho original de la imagen (ej.
			// 335px), y el navegador termina pintándola más pequeña de lo que
			// le toca.
			$html .= wp_get_attachment_image( $id, 'medium_large', false, array(
				'class'   => 'site-header__panel-scroll-oblique-img',
				'loading' => 'lazy',
				'sizes'   => '450px',
			) );
		}
	}
	$html .= '</div></div></div>';

	return $html;
}

/**
 * Panel de hijos: dos columnas independientes con scroll continuo, igual que
 * Grid/Scroll vertical/Scroll oblicuo (pista duplicada x2 para el loop sin
 * costura). Ya no van emparejados nombre+imagen en una fila: la de los
 * nombres baja, la de las imágenes sube, a la misma velocidad.
 */
function bisiesto_render_panel_children( $children ) {
	if ( ! $children ) {
		return '';
	}

	$links_html  = '';
	$images_html = '';

	foreach ( $children as $child ) {
		$links_html .= '<a class="site-header__panel-children-text" href="' . esc_url( $child->url ) . '">' . esc_html( $child->title ) . '</a>';

		$image_id = (int) get_post_meta( $child->ID, '_llao_menu_featured_image', true );
		if ( $image_id ) {
			$images_html .= '<span class="site-header__panel-children-image">' . wp_get_attachment_image( $image_id, 'medium', false, array(
				'class'   => 'site-header__panel-children-img',
				'loading' => 'lazy',
			) ) . '</span>';
		}
	}

	$html  = '<div class="site-header__panel-children">';
	$html .= '<div class="site-header__panel-children-links"><div class="site-header__panel-children-links-track">' . str_repeat( $links_html, 2 ) . '</div></div>';

	if ( $images_html ) {
		$html .= '<div class="site-header__panel-children-images" aria-hidden="true"><div class="site-header__panel-children-images-track">' . str_repeat( $images_html, 2 ) . '</div></div>';
	}

	$html .= '</div>';

	return $html;
}
