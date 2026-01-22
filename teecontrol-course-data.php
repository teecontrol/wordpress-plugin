<?php

/**
 *  Teecontrol Course Data Plugin.
 * 
 * @package Teecontrol Course Data
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 * 
 * @wordpress-plugin
 * Plugin Name: Teecontrol Course Data
 * Plugin URI: https://github.com/teecontrol/wordpress-plugin
 * Description: Teecontrol is the ultimate Tee sheet software for golf courses.
 * Version: 1.0.0
 * Requires at least: 6.9
 * Requires PHP: 8.3
 * License: GPL v3
 * Author: Naboo Software B.V.
 * Author URI: https://teecontrol.com
 * Text Domain: teecontrol-course-data
 * Domain Path: /languages/
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

define('TEECONTROL_COURSE_DATA__VERSION', '1.0.0');
define('TEECONTROL_COURSE_DATA__MINIMUM_WP_VERSION', '6.9');
define('TEECONTROL_COURSE_DATA__PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TEECONTROL_COURSE_DATA__SRC_DIR', TEECONTROL_COURSE_DATA__PLUGIN_DIR . 'build/');
define('TEECONTROL_COURSE_DATA__BASEFILE', basename(dirname(__FILE__)) . '/' . basename(__FILE__));
require_once TEECONTROL_COURSE_DATA__SRC_DIR . 'core/Teecontrol.php';

register_activation_hook(__FILE__, ['TeecontrolCourseData\\Teecontrol', 'plugin_activation']);
register_deactivation_hook(__FILE__, ['TeecontrolCourseData\\Teecontrol', 'plugin_deactivation']);
add_action('init', ['TeecontrolCourseData\\Teecontrol', 'init']);

if (is_admin()) {
    require_once TEECONTROL_COURSE_DATA__SRC_DIR . 'core/TeecontrolAdmin.php';
    add_action('init', ['TeecontrolCourseData\\TeecontrolAdmin', 'init']);
}
