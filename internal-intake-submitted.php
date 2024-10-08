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
// $email = isset($_GET['email']) ? urldecode(sanitize_email($_GET['email'])) : '';
$email = isset($_GET['email']) ? sanitize_email(str_replace(' ', '+', urldecode($_GET['email']))) : '';
$phone = isset($_GET['phone']) ? sanitize_text_field($_GET['phone']) : '';
$userid = isset($_GET['userid']) ? sanitize_text_field($_GET['userid']) : '';

$intake_submitted = isset($_GET['intake_submitted']) ? sanitize_text_field($_GET['intake_submitted']) : '';

// Redirect to the purchase page with tracking parameters
$relative_purchase_page_url = '/esa-letter-checkout/add-to-cart'; // Replace with your purchase page URL
$purchase_page_url = home_url($relative_purchase_page_url);
$redirect_url = isset($_GET['url']) ? sanitize_text_field($_GET['url']) : home_url($relative_purchase_page_url);

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
if (!empty($email) && !empty($tracking_parameters) && $intake_submitted) {
    $data = array(
        'tracking_info' => $tracking_parameters,
    );
    upsertUserInfo($email, $data);
    if($phone) updateUserPhone($email, $phone);
}

if(!empty($email) && !isset($_COOKIE[$tracking_cookie_name])) {
    $tracking_parameters['email'] = $email;
    $emailTrackingParams = getTrackingInfoByEmail($email);
	
    if(is_array($emailTrackingParams) && isset($emailTrackingParams['tracking_info']) && is_array($emailTrackingParams['tracking_info'])) {
        $tracking_parameters = array_merge($tracking_parameters, $emailTrackingParams['tracking_info']);
    }
}

if (!empty($email)) {
    // Append email to the purchase page URL
    $redirect_url = add_query_arg('email', $email, $redirect_url);
}

// Append tracking parameters to the purchase page URL
foreach ($tracking_parameters as $key => $value) {
    if(isset($_GET[$key])) {
        $redirect_url = add_query_arg($key, $_GET[$key], $redirect_url);
    } else {
//         $redirect_url = add_query_arg($key, $value, $redirect_url);
    }
}

if($intake_submitted) {
    $cookie_name = 't_intake_submitted';
    $cookie_value = 'true'; // Set the cookie value as needed
    $cookie_expiry = time() + (7 * 24 * 60 * 60); // Cookie expiry time (7 days from now)
    setcookie($cookie_name, $cookie_value, $cookie_expiry, '/'); // Path '/' makes it available across the whole domain

    if(!$phone) $phone = getUserPhone($email);
    if($phone) {
        // Set the client-side cookie using PHP
        $cookie_name = 't_phone';
        $cookie_value = ($phone); // Set the cookie value as needed
        $cookie_expiry = time() + (7 * 24 * 60 * 60); // Cookie expiry time (7 days from now)
        setcookie($cookie_name, $cookie_value, $cookie_expiry, '/'); // Path '/' makes it available across the whole domain
    }

    // first check if t_userid named cookie exists, if not then check if the userid is set on the database via getUserUserid function. if its not then set user userid via setUserUserid. generate userid first via generateUniqueUserId and then pass that userid to setUsersUserid function. after setting, also create a cookie named t_userid.
    if(!isset($_COOKIE['t_userid'])) {
        $userid = getUserUserid($email);
        if(!$userid) {
            $userid = generateUniqueUserId($email);
            serUserUserid($email, $userid);
        }
        setcookie('t_userid', $userid, time() + (7 * 24 * 60 * 60), "/");
    }
}

// check if t_userid exists. if not then get it from db and set it in the cookie.
if(!isset($_COOKIE['t_userid'])) {
    $userid = getUserUserid($email);
    // NOTE: It should only be set if the intake_submitted is true
    // if(!$userid) {
    //     $userid = generateUniqueUserId();
    //     serUserUserid($email, $userid);
    // }
    setcookie('t_userid', $userid, time() + (7 * 24 * 60 * 60), "/");
}

// check if t_phone exists. if not then get it from db and set it in the cookie.
if(!isset($_COOKIE['t_phone'])) {
    $phone = getUserPhone($email);
	if($phone) setcookie('t_phone', $phone, time() + (7 * 24 * 60 * 60), "/");
}

if (!isset($_COOKIE['_cgclid'])) {
    $gclid = $tracking_parameters['gclid'];
    if ($gclid) {
        setcookie('_cgclid', $gclid, time() + (90 * 24 * 60 * 60), "/"); // Cookie set for 30 days
    }
}

// Redirect to the purchase page
wp_redirect($redirect_url);
exit;
?>