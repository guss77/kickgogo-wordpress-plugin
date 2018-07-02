<?php
/*
 Plugin Name: KickGoGo
 Plugin URI:  http://github.com/guss77/kickgogo-wordpress-plugin
 Description: Crowd-funding campaign manager
 Version:     1.0.6
 Author:      Oded Arbel
 Author URI:  https://github.com/guss77/kickgogo-wordpress-plugin
 License:     GPL2
 License URI: https://www.gnu.org/licenses/gpl-2.0.html
 Domain Path: /languages
 Text Domain: kickgogo
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

require_once __DIR__.'/options.php';
require_once __DIR__.'/inc/functions.php';
require_once __DIR__.'/inc/pelepay.php';
require_once __DIR__.'/shortcodes.php';

register_activation_hook( __FILE__, 'kickgogo_install' );
add_action( 'plugins_loaded', 'kickgogo_update_db');
add_action( 'admin_enqueue_scripts', 'kickgogo_custom_wp_admin_style' );
add_action( 'parse_request', [ $kickgogo_ref, 'handle_callbacks']);
