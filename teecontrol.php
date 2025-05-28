<?php

/**
 * @package Teecontrol
 */
/*
Plugin Name: Teecontrol Wordpress plugin
Plugin URI: https://teecontrol.com
Description: Teecontrol is the ultimate Tee sheet software for golf courses.
Version: 0.1.0
Requires at least: 6.8
Requires PHP: 8.3.13
License: GPL v3
Author: Naboo Software B.V.
Author URI: https://teecontrol.com
Text Domain: teecontrol
Domain Path: /languages/
*/

define('TEECONTROL__VERSION', '1.0.0');
define('TEECONTROL__MINIMUM_WP_VERSION', '6.8');
define('TEECONTROL__PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TEECONTROL__SRC_DIR', TEECONTROL__PLUGIN_DIR . 'build/');
define('TEECONTROL__BASEFILE', basename(dirname(__FILE__)) . '/' . basename(__FILE__));

require_once TEECONTROL__SRC_DIR . 'core/Teecontrol.php';

register_activation_hook(__FILE__, ['Teecontrol\\Teecontrol', 'plugin_activation']);
register_deactivation_hook(__FILE__, ['Teecontrol\\Teecontrol', 'plugin_deactivation']);

add_action('init', ['Teecontrol\\Teecontrol', 'init']);
add_action('plugins_loaded', ['Teecontrol\\Teecontrol', 'load_textdomain']);

if (is_admin()) {
    require_once TEECONTROL__SRC_DIR . 'core/TeecontrolAdmin.php';
    add_action('init', ['Teecontrol\\TeecontrolAdmin', 'init']);
}
