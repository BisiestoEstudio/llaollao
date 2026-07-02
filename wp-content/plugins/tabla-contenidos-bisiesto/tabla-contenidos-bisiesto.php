<?php
/**
 * Plugin Name: Tabla de contenidos Bisiesto
 * Plugin URI:  https://bisiesto.es
 * Description: Tabla de contenidos automática para entradas. Bloque dinámico (FSE) con estilos propios, scroll suave, enlace activo y opción sticky.
 * Version:     2.0.0
 * Author:      Bisiesto Estudio
 * Author URI:  https://bisiesto.es
 * Text Domain: tabla-contenidos-bisiesto
 */

defined( 'ABSPATH' ) || exit;

define( 'TCB_DIR', plugin_dir_path( __FILE__ ) );

require_once TCB_DIR . 'includes/toc.php';

/**
 * Registro del bloque dinámico.
 */
add_action( 'init', function () {
	register_block_type( TCB_DIR . 'blocks/table-of-contents' );
} );
