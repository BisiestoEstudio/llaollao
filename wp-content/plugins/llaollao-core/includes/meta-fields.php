<?php
/**
 * Custom fields del CPT "producto".
 *
 * Se registran como post meta expuestos en REST (para el editor de bloques y
 * el front) y se editan con un panel propio en la barra lateral del editor
 * (assets/js/product-fields.js).
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'llao_register_product_meta' );

function llao_register_product_meta() {

	$can_edit = function () {
		return current_user_can( 'edit_posts' );
	};

	// Recursos: array de IDs de adjuntos (imágenes o vídeos).
	register_post_meta( 'producto', 'llao_recursos', array(
		'type'          => 'array',
		'single'        => true,
		'default'       => array(),
		'auth_callback' => $can_edit,
		'show_in_rest'  => array(
			'schema' => array(
				'type'  => 'array',
				'items' => array( 'type' => 'integer' ),
			),
		),
	) );

	// Variantes: repeater de textos.
	register_post_meta( 'producto', 'llao_variantes', array(
		'type'          => 'array',
		'single'        => true,
		'default'       => array(),
		'auth_callback' => $can_edit,
		'show_in_rest'  => array(
			'schema' => array(
				'type'  => 'array',
				'items' => array( 'type' => 'string' ),
			),
		),
	) );

	// Alérgenos: texto.
	register_post_meta( 'producto', 'llao_alergenos', array(
		'type'          => 'string',
		'single'        => true,
		'default'       => '',
		'auth_callback' => $can_edit,
		'show_in_rest'  => true,
	) );
}

add_action( 'enqueue_block_editor_assets', 'llao_product_fields_assets' );

function llao_product_fields_assets() {

	// Solo en el editor del CPT producto.
	$screen = get_current_screen();
	if ( ! $screen || 'producto' !== $screen->post_type ) {
		return;
	}

	$rel  = 'assets/js/product-fields.js';
	$path = LLAO_CORE_DIR . $rel;

	wp_enqueue_script(
		'llao-product-fields',
		LLAO_CORE_URL . $rel,
		array(
			'wp-plugins',
			'wp-editor',
			'wp-edit-post',
			'wp-element',
			'wp-components',
			'wp-data',
			'wp-core-data',
			'wp-block-editor',
			'wp-i18n',
		),
		file_exists( $path ) ? filemtime( $path ) : '1.0.0',
		true
	);
}
