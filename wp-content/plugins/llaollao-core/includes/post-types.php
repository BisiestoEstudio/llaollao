<?php
/**
 * Registro de Custom Post Types del proyecto.
 *
 * - Producto: tiene vista de single en el front, pero no genera archivo (index).
 * - Local: no se asoma al front en absoluto (ni single ni archivo); existe solo
 *   como contenido que consultan los bloques.
 *
 * Todos se gestionan desde el admin.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'llao_register_post_types' );

/**
 * Icono de helado (soft-serve) como data URI para el menú del admin.
 * SVG monocromo; se hornea el color claro (#a7aaad, el gris de los iconos
 * nativos del menú) porque WordPress no recolorea un SVG de fondo.
 */
function llao_helado_icon() {
	$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#a7aaad">'
		. '<path d="M13.9 8.5a3.4 3.4 0 0 0 .5-1.8 3.5 3.5 0 0 0-1.4-2.8 3.3 3.3 0 0 0-6 0 3.5 3.5 0 0 0-1.4 2.8 3.4 3.4 0 0 0 .5 1.8H13.9z"/>'
		. '<path d="M6.3 10l3.1 8.6a.6.6 0 0 0 1.2 0L13.7 10H6.3z"/>'
		. '</svg>';

	return 'data:image/svg+xml;base64,' . base64_encode( $svg );
}

function llao_register_post_types() {

	// Productos.
	register_post_type( 'producto', array(
		'labels'       => array(
			'name'               => __( 'Productos', 'llaollao-core' ),
			'singular_name'      => __( 'Producto', 'llaollao-core' ),
			'menu_name'          => __( 'Productos', 'llaollao-core' ),
			'add_new'            => __( 'Añadir nuevo', 'llaollao-core' ),
			'add_new_item'       => __( 'Añadir nuevo producto', 'llaollao-core' ),
			'edit_item'          => __( 'Editar producto', 'llaollao-core' ),
			'new_item'           => __( 'Nuevo producto', 'llaollao-core' ),
			'view_item'          => __( 'Ver producto', 'llaollao-core' ),
			'search_items'       => __( 'Buscar productos', 'llaollao-core' ),
			'not_found'          => __( 'No se han encontrado productos', 'llaollao-core' ),
			'not_found_in_trash' => __( 'No hay productos en la papelera', 'llaollao-core' ),
		),
		'public'       => true,
		'has_archive'  => false,
		'hierarchical' => true,
		'show_in_rest' => true,
		'menu_icon'    => llao_helado_icon(),
		'supports'     => array( 'title', 'editor', 'thumbnail', 'custom-fields', 'page-attributes' ),
		'rewrite'      => array( 'slug' => 'productos' ),
	) );

	// Locales.
	//
	// No es público: no tiene single ni archivo en el front, no entra en la
	// búsqueda y no reserva ninguna URL. Aun así se administra con normalidad
	// (show_ui) y se puede consultar desde el código: WP_Query no distingue
	// entre público y privado, así que un bloque puede listar locales sin
	// problema. show_in_rest sí hace falta: es lo que permite al editor de
	// bloques leerlos (por ejemplo con useSelect) y da el editor moderno en la
	// ficha.
	register_post_type( 'local', array(
		'labels'             => array(
			'name'               => __( 'Locales', 'llaollao-core' ),
			'singular_name'      => __( 'Local', 'llaollao-core' ),
			'menu_name'          => __( 'Locales', 'llaollao-core' ),
			'add_new'            => __( 'Añadir nuevo', 'llaollao-core' ),
			'add_new_item'       => __( 'Añadir nuevo local', 'llaollao-core' ),
			'edit_item'          => __( 'Editar local', 'llaollao-core' ),
			'new_item'           => __( 'Nuevo local', 'llaollao-core' ),
			'view_item'          => __( 'Ver local', 'llaollao-core' ),
			'search_items'       => __( 'Buscar locales', 'llaollao-core' ),
			'not_found'          => __( 'No se han encontrado locales', 'llaollao-core' ),
			'not_found_in_trash' => __( 'No hay locales en la papelera', 'llaollao-core' ),
		),
		'public'             => false,
		'publicly_queryable' => false,
		'exclude_from_search' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_rest'       => true,
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_icon'          => 'dashicons-location',
		'supports'           => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
		'rewrite'            => false,
		'query_var'          => false,
	) );
}
