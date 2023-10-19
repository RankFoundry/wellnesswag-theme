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
    define('WELLNESS_WAG_THEME_VERSION', '1.0.3');
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


/*---------------------------------------------------------------*/
/*---------------------- Theme Styles ---------------------------*/
/*---------------------------------------------------------------*/
function wellness_wag_enqueue_styles() {
	wp_enqueue_style( 'wellness-wag', get_stylesheet_directory_uri() . '/style.css', array(), 100 );
}
add_action( 'wp_enqueue_scripts', 'wellness_wag_enqueue_styles' ); 

function custom_quill_scripts() {
	$inline_script = <<<SCRIPT
        document.addEventListener("DOMContentLoaded", function() {
			function getParameterByName(name) {
				name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
				const regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
					results = regex.exec(location.search);
				return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
			}

			function setCookie(name, value, days) {
				const d = new Date();
				d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
				const expires = "expires=" + d.toUTCString();
				document.cookie = name + "=" + value + ";" + expires + ";path=/";
			}

			function getCookie(cname) {
				const name = cname + "=";
				const decodedCookie = decodeURIComponent(document.cookie);
				const ca = decodedCookie.split(';');
				for(let i = 0; i < ca.length; i++) {
					let c = ca[i];
					while (c.charAt(0) === ' ') {
						c = c.substring(1);
					}
					if (c.indexOf(name) === 0) {
						return c.substring(name.length, c.length);
					}
				}
				return "";
			}

			if (!getCookie('_utd')) {
				const parameters = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid','ga_client'];
				let trackingData = {};

				parameters.forEach(param => {
					const value = getParameterByName(param);
					if (value) trackingData[param] = value;
				});

				if (Object.keys(trackingData).length > 0) {
					setCookie('_utd', JSON.stringify(trackingData), 30); // 30 days expiry
				}
			}
		});

	SCRIPT;

	wp_add_inline_script('jquery', $inline_script); // This adds your script after jQuery
}
add_action( 'wp_enqueue_scripts', 'custom_quill_scripts' ); 

