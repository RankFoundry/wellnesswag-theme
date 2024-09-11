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
    define('WELLNESS_WAG_THEME_VERSION', '1.0.30');
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
		phone varchar(100) NULL,
		userid varchar(100) NULL,
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

function create_psd_tracking_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'user_tracking_info_psd';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        email varchar(100) NOT NULL,
		phone varchar(100) NULL,
        userid varchar(100) NULL,
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
    if (get_option('psd_tracking_table_created') !== 'yes') {
        create_psd_tracking_table();
        
        // Update the option to indicate the table has been created
        update_option('psd_tracking_table_created', 'yes');
    }
}

// Hook the table creation function to the 'init' action
add_action('init', 'check_and_create_tracking_table');

// Function to insert or update user tracking information
function upsertUserInfo($email, $data, $type = 'esa') {
    global $wpdb;

    $table_name = $wpdb->prefix . 'user_tracking_info';

    if($type == 'psd') $table_name = $wpdb->prefix . 'user_tracking_info_psd';
	
	// Check if a record with the specified email exists
    $existing_record = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE email = %s", $email));
	
	$dbdata = [];

    // Encode JSON fields before storing in the database
    if (isset($data['tracking_info']) && is_array($data['tracking_info']) && (!$existing_record || is_null($existing_record->tracking_info))) {
        $dbdata['tracking_info'] = json_encode($data['tracking_info']);
        // Update tracked_at date if tracking_info is set
        $dbdata['tracked_at'] = current_time('mysql');
    }

    if (isset($data['purchase_info']) && is_array($data['purchase_info']) && (!$existing_record || is_null($existing_record->purchase_info))) {
        $dbdata['purchase_info'] = json_encode($data['purchase_info']);
        // Update purchased_at date if purchase_info is set
        $dbdata['purchased_at'] = current_time('mysql');
    }
	
	if(count($dbdata) <= 0) return;
	
    if ($existing_record) {
        // Update the existing record
        $wpdb->update(
            $table_name,
            $dbdata,
            ['email' => $email]
        );
    } else {
        // Insert a new record
        $dbdata['email'] = $email;
        $wpdb->insert(
            $table_name,
            $dbdata
        );
    }
}

// Function to retrieve user tracking information by email
function getTrackingInfoByEmail($email, $type = 'esa') {
    global $wpdb;

    $table_name = $wpdb->prefix . 'user_tracking_info';

    if($type == 'psd') $table_name = $wpdb->prefix . 'user_tracking_info_psd';

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

function updateUserPhone($email, $phone, $type = 'esa') {
    global $wpdb;

    $table_name = $wpdb->prefix . 'user_tracking_info';

    if($type == 'psd') $table_name = $wpdb->prefix . 'user_tracking_info_psd';
	
	$wpdb->update(
        $table_name,
        ['phone' => $phone],
        ['email' => $email]
    );
}

function getUserPhone($email, $type = 'esa') {
    global $wpdb;

    $table_name = $wpdb->prefix . 'user_tracking_info';

    if($type == 'psd') $table_name = $wpdb->prefix . 'user_tracking_info_psd';

    // Prepare and execute the query to fetch the tracking info for the given email
    $query = $wpdb->prepare("SELECT phone FROM $table_name WHERE email = %s", $email);
    $result = $wpdb->get_row($query, ARRAY_A);

    if ($result) return $result['phone'];

    return null;
}

function generateUniqueUserId($email) {
    // Create a unique ID using the email, current time, and a random component
    $timestamp = microtime(true); // Current time in microseconds
    $randomNumber = mt_rand(); // Generate a random number

    // Combine the email, timestamp, and random number
    $input = $email . $timestamp . $randomNumber;

    // Hash the combined input to generate the user ID
    // Use SHA-256 and truncate to 14 characters
    // There are approximately 7.5 x 10^21 possible unique IDs, making collisions highly unlikely
    $userId = substr(hash('sha256', $input), 0, 14);

    return $userId;
}


function serUserUserid($email, $userid, $type = 'esa') {
    global $wpdb;

    $table_name = $wpdb->prefix . 'user_tracking_info';

    if($type == 'psd') $table_name = $wpdb->prefix . 'user_tracking_info_psd';

	
	$wpdb->update(
        $table_name,
        ['userid' => $userid],
        ['email' => $email]
    );
}

function getUserUserid($email, $type = 'esa') {
    global $wpdb;

    $table_name = $wpdb->prefix . 'user_tracking_info';

    if($type == 'psd') $table_name = $wpdb->prefix . 'user_tracking_info_psd';

    // Prepare and execute the query to fetch the tracking info for the given email
    $query = $wpdb->prepare("SELECT userid FROM $table_name WHERE email = %s", $email);
    $result = $wpdb->get_row($query, ARRAY_A);

    if ($result) return $result['userid'];

    return null;
}

function inject_tracking_params_script() {
    ob_start();
    ?>
    <script nowprocket data-cfasync='false'>
        window.getCookie = name => document.cookie.split('; ').reduce((r, v) => v.startsWith(name + '=') ? v.split('=')[1] : r, null);
        window.setCookie = (name, value, days) => document.cookie = `${name}=${value}; path=/; expires=${new Date(Date.now() + days * 864e5).toUTCString()}`;
        window.deleteCookie = name => document.cookie = `${name}=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT`;
		window.getDecodedURLParameter = name => decodeURIComponent(new URLSearchParams(window.location.search).get(name) || '') || null;
        window.getQueryParam = param => new URLSearchParams(window.location.search).get(param);

        const storeGclidInCookie = (gclid, cookieName, cookieExpiryDays = 90) => {
            if (!gclid) return;
            if (!cookieName) return;
            setCookie(cookieName, gclid, cookieExpiryDays);
        }

        const storeTrackingParamsInCookie = (paramsList, cookieName, cookieExpiryDays = 90) => {
            if (!paramsList || !Array.isArray(paramsList)) return;
            if (!cookieName) return;

            var existingCookie = getCookie(cookieName);
            if (existingCookie) return;

            var url = new URL(window.location.href);
            var searchParams = new URLSearchParams(url.search);

            var urlParams = paramsList
				.filter(p => searchParams.get(p))
                .map(function(p) { return [p,searchParams.get(p)]; })
                .reduce(function(acc, pair) { acc[pair[0]] = pair[1]; return acc; }, {});
			
			// Check if the urlParams object is empty
			var isEmpty = Object.keys(urlParams).length === 0;

			if (!isEmpty) {
				setCookie(cookieName, JSON.stringify(urlParams), cookieExpiryDays);
			}            
        }

        storeTrackingParamsInCookie(['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'msclkid', 'fbclid', '_ga','cuid','cid','sid','cp1','cp2'], '_cupm');
        window.gclid = getQueryParam('gclid');
        storeGclidInCookie(window.gclid, '_cgclid');		

    </script>
    <?php
    $script = ob_get_clean();
    echo $script;
}
add_action('wp_head', 'inject_tracking_params_script');

function add_x_client_country_header_and_cookie() {
    if ( isset($_SERVER['HTTP_CF_IPCOUNTRY']) ) {
        $client_country = sanitize_text_field($_SERVER['HTTP_CF_IPCOUNTRY']);
        
        // Add the X-Client-Country header
        header('X-Client-Country: ' . $client_country);
        
        // Set a cookie with the same name and value, set to expire far in the future (e.g., 10 years)
        setcookie('X-Client-Country', $client_country, time() + (10 * 365 * 24 * 60 * 60), "/"); // 10 years
    }

    if ( isset($_SERVER['HTTP_CF_REGION_CODE']) && isset($_SERVER['HTTP_CF_IPCOUNTRY'])  ) {
        $client_country = sanitize_text_field($_SERVER['HTTP_CF_IPCOUNTRY']);
		$client_region = sanitize_text_field($_SERVER['HTTP_CF_REGION_CODE']);
        
        // Add the X-Client-Country header
        header('X-Client-Region: ' . $client_country . '-' . $client_region);
        
        // Set a cookie with the same name and value, set to expire far in the future (e.g., 10 years)
        setcookie('X-Client-Region',$client_country . '-' . $client_region, time() + (10 * 365 * 24 * 60 * 60), "/"); // 10 years
    }
}
add_action('send_headers', 'add_x_client_country_header_and_cookie');
