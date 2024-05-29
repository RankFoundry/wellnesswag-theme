<?php
/**
 * Template Name: Internal Intake Redirect
 *
 * @package   Wellness_Wag_Theme
 * @link      https://rankfoundry.com
 * @copyright Copyright (C) 2021-2023, Rank Foundry LLC - support@rankfoundry.com
 * @since     1.0.3
 * @license   GPL-2.0+
 *
 */

// Retrieve email from query parameter
$email = isset($_GET['email']) ? sanitize_email($_GET['email']) : '';
$intake_submitted = isset($_GET['intake_submitted']) ? sanitize_text_field($_GET['intake_submitted']) : '';

// Retrieve tracking parameters from cookie
$tracking_cookie_name = '_cupm'; // Replace with the name of your tracking cookie
$tracking_parameters = array();

if (isset($_COOKIE[$tracking_cookie_name])) {
    $cookie_value = stripslashes($_COOKIE[$tracking_cookie_name]);
    $tracking_parameters = json_decode($cookie_value, true);
    // Ensure the JSON decoding was successful
    if ($tracking_parameters === null) {
        $tracking_parameters = array(); // Reset to an empty array if decoding failed
    }
}


// Upsert tracking info
if (!empty($email) && !empty($tracking_parameters)) {
    $data = array(
        'tracking_info' => $tracking_parameters,
    );
    upsertUserInfo($email, $data);
}


if(!empty($email) && !isset($_COOKIE[$tracking_cookie_name])) {
    $tracking_parameters['email'] = $email;

    $emailTrackingParams = getTrackingInfoByEmail($email);

    if(!is_null($emailTrackingParams) && !is_null($emailTrackingParams['tracking_info']) && !empty($emailTrackingParams)) {
        $tracking_parameters = array_merge($tracking_parameters, $emailTrackingParams['tracking_info']);
    }
}

// Redirect to the purchase page with tracking parameters
$relative_purchase_page_url = '/esa-letter/add-to-cart'; // Replace with your purchase page URL

$purchase_page_url = home_url($relative_purchase_page_url);
if (!empty($email)) {
    // Append email to the purchase page URL
    $purchase_page_url = add_query_arg('email', $email, $purchase_page_url);
}

// Append tracking parameters to the purchase page URL
foreach ($tracking_parameters as $key => $value) {
    if(isset($_GET[$key])) {
        $purchase_page_url = add_query_arg($key, $_GET[$key], $purchase_page_url);
    } else {
        $purchase_page_url = add_query_arg($key, $value, $purchase_page_url);
    }
}

if($intake_submitted) {
    $cookie_name = 't_intake_submitted';
    $cookie_value = 'true'; // Set the cookie value as needed
    $cookie_expiry = time() + (7 * 24 * 60 * 60); // Cookie expiry time (7 days from now)
    setcookie($cookie_name, $cookie_value, $cookie_expiry, '/'); // Path '/' makes it available across the whole domain
}

// Redirect to the purchase page
wp_redirect($purchase_page_url);
exit;
?>