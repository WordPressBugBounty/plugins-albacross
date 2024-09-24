<?php
/**
 * @package Albacross
 * @version 1.3.3
 */
/*
Plugin Name: Albacross
Plugin URI: https://albacross.com/
Description: This is a plugin for enabling Albacross visitor analysis on your website.
Author: Albacross Nordic AB
Version: 1.3.3
Author URI: https://albacross.com/
*/

$base_path = realpath(dirname(__FILE__));

require $base_path . '/insert-code.php';
require $base_path . '/admin.php';

if(is_admin()) {
    add_action('admin_menu', 'albacross_admin_create_menu');
    add_action('admin_init', 'albacross_register_settings');
    add_action('admin_notices', 'albacross_admin_notice' );
}

add_action('wp_footer', 'albacross_insert_code', 0);
// add_action('plugins_loaded', 'albacross_load_textdomain');

function albacross_load_textdomain() {
  load_plugin_textdomain('albacross-wordpress-plugin', false, dirname(plugin_basename(__FILE__)) . '/lang/');
}
