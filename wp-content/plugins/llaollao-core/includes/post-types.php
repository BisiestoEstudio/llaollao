<?php
/**
 * Registro de Custom Post Types del proyecto.
 *
 * Los CPTs tienen vista de single en el front, pero no generan un archivo
 * (index) ni pertenecen a ninguna taxonomía. Se gestionan desde el admin.
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
		'show_in_rest' => true,
		'menu_icon'    => llao_helado_icon(),
		'supports'     => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
		'rewrite'      => array( 'slug' => 'productos' ),
	) );
}
