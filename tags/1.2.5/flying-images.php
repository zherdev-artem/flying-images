<?php
/**
 * Plugin Name: Flying Images
 * Plugin URI: https://wordpress.org/plugins/nazy-load/
 * Description: High-performance Native Image Lazy Loading
 * Author: Gijo Varghese
 * Author URI: https://wpspeedmatters.com/
 * Version: 1.2.5
 * Text Domain: flying-images
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
    die;
}

// Define constant with current version
if (!defined('FLYING_IMAGES_VERSION'))
define('FLYING_IMAGES_VERSION', '1.2.5');

include('set-config.php');
include('settings-view.php');
include('noscript-css.php');
include('html-rewrite.php');
include('inject-js.php');
include('shortcuts.php');
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'flying_images_add_shortcuts');