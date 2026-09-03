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

	// País (del local).
	register_taxonomy( 'pais', 'local', array(
		'labels'             => array(
			'name'          => __( 'Países', 'llaollao-core' ),
			'singular_name' => __( 'País', 'llaollao-core' ),
			'menu_name'     => __( 'País', 'llaollao-core' ),
			'all_items'     => __( 'Todos los países', 'llaollao-core' ),
			'edit_item'     => __( 'Editar país', 'llaollao-core' ),
			'update_item'   => __( 'Actualizar país', 'llaollao-core' ),
			'add_new_item'  => __( 'Añadir nuevo país', 'llaollao-core' ),
			'new_item_name' => __( 'Nombre del nuevo país', 'llaollao-core' ),
			'search_items'  => __( 'Buscar países', 'llaollao-core' ),
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

/**
 * "Tipo" y "país" tienen public => false (sin archivo ni consultas en el
 * front), así que Polylang no las lista en Ajustes > Idiomas > Tipos de
 * contenido personalizados y taxonomías (esa pantalla solo escanea taxonomías
 * públicas). Se añaden a mano para que aparezcan ahí.
 *
 * Solo se añaden cuando $is_settings es true (la llamada que arma esa
 * pantalla de ajustes): si se añadieran siempre, Polylang las trataría como
 * "activas permanentemente" en vez de mirar la casilla guardada, y saldrían
 * marcadas y bloqueadas sin poder desactivarlas (mismo fallo que tuvo "local"
 * en post-types.php).
 */
add_filter( 'pll_get_taxonomies', function( $taxonomies, $is_settings ) {
	if ( $is_settings ) {
		$taxonomies['tipo'] = 'tipo';
		$taxonomies['pais'] = 'pais';
	}
	return $taxonomies;
}, 10, 2 );
