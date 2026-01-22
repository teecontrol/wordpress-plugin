<?php

/**
 * @package Teecontrol Course Data
 */
/*
Plugin Name: Teecontrol Course Data
Plugin URI: https://github.com/teecontrol/wordpress-plugin
Description: Teecontrol is the ultimate Tee sheet software for golf courses.
Version: 1.0.1
Requires at least: 6.8
Requires PHP: 8.3
License: GPL v3
Author: Naboo Software B.V.
Author URI: https://teecontrol.com
Text Domain: teecontrol-course-data
Domain Path: /languages/
*/

define('TEECONTROL_COURSE_DATA__VERSION', '1.0.1');
define('TEECONTROL_COURSE_DATA__MINIMUM_WP_VERSION', '6.8');
define('TEECONTROL_COURSE_DATA__PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TEECONTROL_COURSE_DATA__SRC_DIR', TEECONTROL_COURSE_DATA__PLUGIN_DIR . 'build/');
define('TEECONTROL_COURSE_DATA__BASEFILE', basename(dirname(__FILE__)) . '/' . basename(__FILE__));
require_once TEECONTROL_COURSE_DATA__SRC_DIR . 'core/Teecontrol.php';

register_activation_hook(__FILE__, ['TeecontrolCourseData\\Teecontrol', 'plugin_activation']);
register_deactivation_hook(__FILE__, ['TeecontrolCourseData\\Teecontrol', 'plugin_deactivation']);
add_action('init', ['TeecontrolCourseData\\Teecontrol', 'init']);
add_action('plugins_loaded', ['TeecontrolCourseData\\Teecontrol', 'load_textdomain']);

if (is_admin()) {
    require_once TEECONTROL_COURSE_DATA__SRC_DIR . 'core/TeecontrolAdmin.php';
    add_action('init', ['TeecontrolCourseData\\TeecontrolAdmin', 'init']);
}
