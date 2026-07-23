<?php
/**
 * Registro de bloques propios del core.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'llao_register_blocks' );

function llao_register_blocks() {
	register_block_type( LLAO_CORE_DIR . 'blocks/breadcrumbs' );
}
