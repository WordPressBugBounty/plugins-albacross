<?php
/**
 * Plugin Name:       Albacross – B2B Website Visitor Identification
 * Plugin URI:        https://www.albacross.com/
 * Description:       Identify the companies visiting your website. Adds the Albacross tracking script to WordPress in one click — no code, no theme edits.
 * Version:           1.6.0
 * Requires at least: 5.7
 * Requires PHP:      7.4
 * Author:            Albacross Nordic AB
 * Author URI:        https://www.albacross.com/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       albacross
 *
 * @package Albacross
 */

defined( 'ABSPATH' ) || exit;

define( 'ALBACROSS_PLUGIN_VERSION', '1.6.0' );
define( 'ALBACROSS_PLUGIN_FILE', __FILE__ );
define( 'ALBACROSS_MENU_SLUG', 'albacross' );

/**
 * Option name. Unchanged since 1.0 so that existing installs keep their Client ID.
 */
define( 'ALBACROSS_OPTION_CLIENT_ID', 'albacross_client_id' );

require_once __DIR__ . '/insert-code.php';
require_once __DIR__ . '/optimizer-compat.php';

add_action( 'wp_enqueue_scripts', 'albacross_enqueue_tracking_script' );
add_filter( 'script_loader_tag', 'albacross_async_script_tag', 10, 3 );

if ( is_admin() ) {
	require_once __DIR__ . '/admin.php';

	add_action( 'admin_menu', 'albacross_admin_create_menu' );
	add_action( 'admin_init', 'albacross_register_settings' );
	add_action( 'admin_notices', 'albacross_admin_notice' );
	add_action( 'admin_enqueue_scripts', 'albacross_admin_styles' );
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'albacross_plugin_action_links' );
}