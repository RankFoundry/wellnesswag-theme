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
    define('WELLNESS_WAG_THEME_VERSION', '1.0.22');
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

// Function to create the user_tracking_info table
function create_tracking_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'user_tracking_info';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        email varchar(100) NOT NULL,
        tracking_info text DEFAULT NULL,
        tracked_at datetime DEFAULT NULL,
        purchase_info text DEFAULT NULL,
        purchased_at datetime DEFAULT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // Include the necessary file to access dbDelta function
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Function to check and create the table if it doesn't exist
function check_and_create_tracking_table() {
    // Check if the table creation has already run
    if (get_option('my_tracking_table_created') !== 'yes') {
        create_tracking_table();
        
        // Update the option to indicate the table has been created
        update_option('my_tracking_table_created', 'yes');
    }
}

// Hook the table creation function to the 'init' action
add_action('init', 'check_and_create_tracking_table');

// Function to insert or update user tracking information
function upsertUserInfo($email, $data) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'user_tracking_info';

    // Encode JSON fields before storing in the database
    if (isset($data['tracking_info']) && is_array($data['tracking_info'])) {
        $data['tracking_info'] = json_encode($data['tracking_info']);
        // Update tracked_at date if tracking_info is set
        $data['tracked_at'] = current_time('mysql');
    }

    if (isset($data['purchase_info']) && is_array($data['purchase_info'])) {
        $data['purchase_info'] = json_encode($data['purchase_info']);
        // Update purchased_at date if purchase_info is set
        $data['purchased_at'] = current_time('mysql');
    }

    // Check if a record with the specified email exists
    $existing_record = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE email = %s", $email));

    if ($existing_record) {
        // Update the existing record
        $wpdb->update(
            $table_name,
            $data,
            ['email' => $email]
        );
    } else {
        // Insert a new record
        $data['email'] = $email;
        $wpdb->insert(
            $table_name,
            $data
        );
    }
}

// Function to retrieve user tracking information by email
function getTrackingInfoByEmail($email) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'user_tracking_info';

    // Prepare and execute the query to fetch the tracking info for the given email
    $query = $wpdb->prepare("SELECT tracking_info, tracked_at, purchase_info, purchased_at FROM $table_name WHERE email = %s", $email);
    $result = $wpdb->get_row($query, ARRAY_A);

    if ($result) {
        // Decode JSON fields
        if (!empty($result['tracking_info'])) {
            $result['tracking_info'] = json_decode($result['tracking_info'], true);
        }
        if (!empty($result['purchase_info'])) {
            $result['purchase_info'] = json_decode($result['purchase_info'], true);
        }
    }

    return $result;
}

function inject_tracking_params_script() {
    ob_start();
    ?>
    <script>
        window.getCookie = name => document.cookie.split('; ').reduce((r, v) => v.startsWith(name + '=') ? v.split('=')[1] : r, null);
        window.setCookie = (name, value, days) => document.cookie = `${name}=${value}; path=/; expires=${new Date(Date.now() + days * 864e5).toUTCString()}`;
        window.deleteCookie = name => document.cookie = `${name}=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT`;

        const storeTrackingParamsInCookie = (paramsList, cookieName, cookieExpiryDays = 30) => {
            if (!paramsList || !Array.isArray(paramsList)) return;
            if (!cookieName) return;

            var existingCookie = getCookie(cookieName);
            if (existingCookie) return;

            var url = new URL(window.location.href);
            var searchParams = new URLSearchParams(url.search);

            var urlParams = paramsList
                .map(function(p) { return [p,searchParams.get(p)]; })
                .reduce(function(acc, pair) { acc[pair[0]] = pair[1]; return acc; }, {});

            setCookie(cookieName, JSON.stringify(urlParams), cookieExpiryDays);
        }

        storeTrackingParamsInCookie(['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'msclkid', 'fbclid', '_ga','cuid','cid','sid','cp1','cp2'], '_cupm');
    </script>
    <?php
    $script = ob_get_clean();
    echo $script;
}
add_action('wp_head', 'inject_tracking_params_script');
