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

    function checkEmailAndGenerateToken($email) {
        // $allowedDomains = ['wellnesswag.com', 'enacton.com'];
        // $domain = substr(strrchr($email, "@"), 1);
        
        // if (!in_array($domain, $allowedDomains)) {
        //     return null;
        // }
        
        $curl = curl_init();
        
        $headers = [
            "Content-Type: application/json",
            "User-Agent: Insomnia/2023.5.8"
        ];
    
        $postFields = json_encode(["email" => $email, "type" => "PSD"]);
        
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://care.wellnesswag.com/api/generate-token/",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_POSTREDIR => CURL_REDIR_POST_ALL,
        ]);
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        curl_close($curl);
        
        if ($err) {
            error_log("cURL Error in checkEmailAndGenerateToken: " . $err);
            return null;
        }
        
        if ($httpCode >= 400) {
            error_log("API Error in checkEmailAndGenerateToken: HTTP Code " . $httpCode);
            return null;
        }
        
        // Try to decode as JSON first
        $decodedResponse = json_decode($response, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            // It's a valid JSON
            if (isset($decodedResponse['success']) && $decodedResponse['success'] == true) {
                return $decodedResponse;
            }
        } else {
            // It's not a JSON, assume it's a direct URL string
            if (filter_var($response, FILTER_VALIDATE_URL)) {
                return trim($response);
            }
        }
        
        error_log("Unexpected response in checkEmailAndGenerateToken: " . substr($response, 0, 1000));
        return null;
    }

    $patientResponseData = checkEmailAndGenerateToken($email);
    $redirectUrl = $patientResponseData['redirectUrl'];

    if ($redirectUrl !== null) {
        $cookie_name = 'portal_login_url';
        $cookie_value = $redirectUrl;
        $cookie_expiry = time() + (24 * 60 * 60); // 24 hours
        setcookie($cookie_name, $cookie_value, $cookie_expiry, '/', '', true, false);
    }

    $patientData = [];
    if ($redirectUrl !== null) {
        $cookie_name = 'patient_data';

        $patientData['token'] = $patientResponseData['token'] ?? '';
        $patientData['pet_species'] = $patientResponseData['pets'][0]['type'] ?? '';
        $patientData['pet_gender'] = $patientResponseData['pets'][0]['gender'] ?? '';
        $patientData['pet_age'] = $patientResponseData['pets'][0]['age'] ?? '';
        $patientData['pet_name'] = $patientResponseData['pets'][0]['name'] ?? '';
        $patientData['state'] = $patientResponseData['user']['state'] ?? '';

        $cookie_value = json_encode($patientData);
        $cookie_expiry = time() + (24 * 60 * 60); // 24 hours
        setcookie($cookie_name, $cookie_value, $cookie_expiry, '/', '', true, false);
    }

	
	// Redirect to the purchase page
	wp_redirect($thankyou_page_url);
	exit;
} else {
	$addToCartUrl = 'https://wellnesswag.com/psd-letter-checkout/add-to-cart';
	wp_redirect($addToCartUrl);
	exit;
}

?>