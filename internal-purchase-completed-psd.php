<?php
/**
 * Template Name: Internal Purchase Redirect PSD
 *
 * @package   Wellness_Wag_Theme
 * @link      https://rankfoundry.com
 * @copyright Copyright (C) 2021-2023, Rank Foundry LLC - support@rankfoundry.com
 * @since     1.0.3
 * @license   GPL-2.0+
 *
 */


// Retrieve email from query parameter
$payment_intent_id = isset($_GET['payment_intent']) ? sanitize_text_field($_GET['payment_intent']) : '';
$customer_id = isset($_GET['customer_id']) ? sanitize_text_field($_GET['customer_id']) : '';

$payment_info = array();

function getPaymentIntentData($intentId) {
    $apiKey = constant('STRIPE_SECRET_KEY');

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents/' . $intentId);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $apiKey . ':');

    $response = curl_exec($ch);

    if(curl_errno($ch)){
        $errorMessage = 'Curl error: ' . curl_error($ch);
        curl_close($ch);
        return array('error' => $errorMessage);
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 400) {
        return array('error' => 'HTTP error: ' . $httpCode);
    }

    // Decode the JSON response to PHP array
    $responseData = json_decode($response, true);

    return $responseData;
}
function getCustomerData($customerId) {
    $apiKey = constant('STRIPE_SECRET_KEY');

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/customers/' . $customerId);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $apiKey . ':');

    $response = curl_exec($ch);

    if(curl_errno($ch)){
        $errorMessage = 'Curl error: ' . curl_error($ch);
        curl_close($ch);
        return array('error' => $errorMessage);
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 400) {
        return array('error' => 'HTTP error: ' . $httpCode);
    }

    // Decode the JSON response to PHP array
    $responseData = json_decode($response, true);

    return $responseData;
}

if (isset($payment_intent_id) && isset($customer_id)) {
    $payment_info['payment_intent'] = getPaymentIntentData($payment_intent_id);
    $payment_info['customer_info'] = getCustomerData($customer_id);

}

$email = $payment_info['customer_info']['email'];
	
$transactionDetails = [
	'transaction_id' => $payment_info['payment_intent']['id'] ?? null,
	'amount' => $payment_info['payment_intent']['amount'] ?? 0,
	'metadata' => $payment_info['payment_intent']['metadata'] ?? null,
];


// Upsert payment info
if (!empty($email) && !empty($payment_intent_id)) {
    $data = array(
        'purchase_info' => $payment_info,
    );
    upsertUserInfo($email, $data, 'psd');
}


$tracking_parameters = array('email' => $email);
// $emailTrackingParams = getTrackingInfoByEmail($email, 'psd');
// if(!is_null($emailTrackingParams) && !is_null($emailTrackingParams['tracking_info']) && !empty($emailTrackingParams)) {
//     $tracking_parameters = array_merge($tracking_parameters, $emailTrackingParams['tracking_info']);
// }


// Redirect to the purchase page with tracking parameters
$relative_thankyou_page_url = '/psd-letter-checkout/thank-you'; // Replace with your purchase page URL

$thankyou_page_url = home_url($relative_thankyou_page_url);

// Append tracking parameters to the purchase page URL
foreach ($tracking_parameters as $key => $value) {
    $thankyou_page_url = add_query_arg($key, $value, $thankyou_page_url);
}

if($payment_info['payment_intent']['status'] == 'succeeded') {
	// Set the client-side cookie using PHP
	$cookie_name = 't_purchase';
	$cookie_value = 'true'; // Set the cookie value as needed
	$cookie_expiry = time() + (7 * 24 * 60 * 60); // Cookie expiry time (7 days from now)
	setcookie($cookie_name, $cookie_value, $cookie_expiry, '/'); // Path '/' makes it available across the whole domain


	// Set the client-side cookie using PHP
	$cookie_name = 't_transaction_amount';
	$cookie_value = (floatval($transactionDetails['amount'])/100); // Set the cookie value as needed
	$cookie_expiry = time() + (7 * 24 * 60 * 60); // Cookie expiry time (7 days from now)
	setcookie($cookie_name, $cookie_value, $cookie_expiry, '/'); // Path '/' makes it available across the whole domain



	// Set the client-side cookie using PHP
	$cookie_name = 't_transaction_id';
	$cookie_value = ($transactionDetails['transaction_id']); // Set the cookie value as needed
	$cookie_expiry = time() + (7 * 24 * 60 * 60); // Cookie expiry time (7 days from now)
	setcookie($cookie_name, $cookie_value, $cookie_expiry, '/'); // Path '/' makes it available across the whole domain	
	
	// Redirect to the purchase page
	wp_redirect($thankyou_page_url);
	exit;
} else {
	$addToCartUrl = 'https://wellnesswag.com/psd-letter-checkout/add-to-cart';
	wp_redirect($addToCartUrl);
	exit;
}

?>