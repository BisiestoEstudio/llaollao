<?php
/**
 * Plugin Name: Llaollao Core
 * Plugin URI:  https://bisiesto.es
 * Description: Núcleo del proyecto Llaollao: registro de CPTs, taxonomías y funcionalidad propia del sitio.
 * Version:     1.0.0
 * Author:      Bisiesto Estudio
 * Author URI:  https://bisiesto.es
 * Text Domain: llaollao-core
 */

defined( 'ABSPATH' ) || exit;

define( 'LLAO_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'LLAO_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once LLAO_CORE_DIR . 'includes/post-types.php';
require_once LLAO_CORE_DIR . 'includes/taxonomies.php';
require_once LLAO_CORE_DIR . 'includes/meta-fields.php';
require_once LLAO_CORE_DIR . 'includes/settings.php';
require_once LLAO_CORE_DIR . 'includes/blocks.php';

/**
 * Al activar: registra CPTs y taxonomías y regenera las reglas de reescritura
 * para que la ruta del single de producto funcione sin tener que reguardar los
 * enlaces permanentes a mano.
 */
register_activation_hook( __FILE__, function () {
	llao_register_post_types();
	llao_register_taxonomies();
	flush_rewrite_rules();
} );

/**
 * Al desactivar: limpia las reglas de reescritura del CPT.
 */
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
