<?php
/**
 * Wellness Wag Theme
 *
 * @package   Wellness_Wag_Theme
 * @link      https://rankfoundry.com
 * @copyright Copyright (C) 2021-2023, Rank Foundry LLC - support@rankfoundry.com
 * @since     1.0.0
 * @license   GPL-2.0+
 *
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/*--------------------------------------------------------------*/
/*---------------------- Theme Setup ---------------------------*/
/*--------------------------------------------------------------*/
// Define theme version
if (!defined('WELLNESS_WAG_THEME_VERSION')) {
    define('WELLNESS_WAG_THEME_VERSION', '1.0.20');
}

// Define theme directory path
if (!defined('WELLNESS_WAG_THEME_DIR')) {
    define('WELLNESS_WAG_THEME_DIR', trailingslashit( get_stylesheet_directory() ));
}

// Define theme directory URI
if (!defined('WELLNESS_WAG_THEME_DIR_URI')) {
    define('WELLNESS_WAG_THEME_DIR_URI', trailingslashit( esc_url( get_stylesheet_directory_uri() )));
}

// Define current theme name
if (!defined('CURRENT_THEME_NAME')) {
    $current_theme_obj = wp_get_theme();
    define('CURRENT_THEME_NAME', $current_theme_obj->get('Name'));
}

// Get current theme path in JS var
function add_theme_path_to_js() {
    // Get the active theme path
    $theme_path = esc_url(get_stylesheet_directory_uri());

    // Inline JavaScript to set theme path as a global variable
    $inline_script = "window.themePath = '{$theme_path}';";

    // Add inline script to the footer of the page
    wp_add_inline_script('jquery', $inline_script, 'after');
}
add_action('wp_enqueue_scripts', 'add_theme_path_to_js');


// Load the Composer autoloader.
require_once WELLNESS_WAG_THEME_DIR . 'vendor/autoload.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;


/*--------------------------------------------------------------*/
/*------------------ Theme Update Checker ----------------------*/
/*--------------------------------------------------------------*/
if ( 'Wellness Wag' === CURRENT_THEME_NAME ) {
	$themeUpdateChecker = PucFactory::buildUpdateChecker(
		'https://github.com/rankfoundry/wellnesswag-theme/',
		WELLNESS_WAG_THEME_DIR . '/functions.php',
		'wellness-wag',
		48
	);
	$themeUpdateChecker->setBranch('master');
    //$themeUpdateChecker->setAuthentication('');
}

/*--------------------------------------------------------------*/
/*--------------------- WP Auto Updates ------------------------*/
/*--------------------------------------------------------------*/

// allows WP plugins to automatically update.
add_filter('auto_update_plugin', '__return_true');

// allow themes to automatically update.
//add_filter('auto_update_theme', '__return_false');

// allow WP core updates.
add_filter('allow_minor_auto_core_updates', '__return_true');
add_filter('allow_major_auto_core_updates', '__return_true');

// force auto updates even for version controlled code enviroments.
add_filter('automatic_updates_is_vcs_checkout', '__return_false', 1);


/*---------------------------------------------------------------*/
/*---------------------- Theme Styles ---------------------------*/
/*---------------------------------------------------------------*/
function wellness_wag_enqueue_styles() {
	wp_enqueue_style( 'wellness-wag', get_stylesheet_directory_uri() . '/style.css', array(), WELLNESS_WAG_THEME_VERSION );
	wp_enqueue_style( 'custom', get_stylesheet_directory_uri() . '/assets/css/custom.css', array(), WELLNESS_WAG_THEME_VERSION );
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}
add_action( 'wp_enqueue_scripts', 'wellness_wag_enqueue_styles' ); 

function wellness_wag_enqueue_script() {
	wp_enqueue_script( 'custom', get_stylesheet_directory_uri() . '/assets/js/custom.js', array(), WELLNESS_WAG_THEME_VERSION );
	wp_enqueue_script( 'wwbot-helper', get_stylesheet_directory_uri() . '/assets/js/wwbot-helper.js', array(), WELLNESS_WAG_THEME_VERSION );
}
add_action( 'wp_enqueue_scripts', 'wellness_wag_enqueue_script' ); 


// Remove trailing slash from pagination links
add_filter('paginate_links','untrailingslashit');
add_filter( 'get_pagenum_link', 'untrailingslashit');



