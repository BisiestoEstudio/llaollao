<?php
/**
 * Registro de taxonomías del proyecto.
 *
 * Las taxonomías se gestionan desde el admin, pero no se exponen en el front:
 * sin archivo (archive) ni consultas públicas.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'llao_register_taxonomies' );

function llao_register_taxonomies() {

	// Tipo (de producto).
	register_taxonomy( 'tipo', 'producto', array(
		'labels'             => array(
			'name'          => __( 'Tipos', 'llaollao-core' ),
			'singular_name' => __( 'Tipo', 'llaollao-core' ),
			'menu_name'     => __( 'Tipo', 'llaollao-core' ),
			'all_items'     => __( 'Todos los tipos', 'llaollao-core' ),
			'edit_item'     => __( 'Editar tipo', 'llaollao-core' ),
			'update_item'   => __( 'Actualizar tipo', 'llaollao-core' ),
			'add_new_item'  => __( 'Añadir nuevo tipo', 'llaollao-core' ),
			'new_item_name' => __( 'Nombre del nuevo tipo', 'llaollao-core' ),
			'search_items'  => __( 'Buscar tipos', 'llaollao-core' ),
		),
		'public'             => false,
		'publicly_queryable' => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_admin_column'  => true,
		'show_in_rest'       => true,
		'hierarchical'       => true,
		'rewrite'            => false,
		'query_var'          => false,
	) );
}
