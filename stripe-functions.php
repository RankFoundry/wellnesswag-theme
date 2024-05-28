
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/initiate-checkout', array(
        'methods' => 'POST',
        'callback' => 'initiate_checkout',
        'permission_callback' => '__return_true', // Adjust permission as needed
    ));
});

function initiate_checkout(WP_REST_Request $request) {
    $params = $request->get_json_params();

    $product_id = isset($params['product_id']) ? intval($params['product_id']) : 1;

    // Define your products and their prices
    $products = [
        1 => 22900, // Product 1 price in cents
        2 => 24900  // Product 2 price in cents
    ];

    $payment_intent = get_stripe_payment_intent($products[$product_id], 'usd');

    return new WP_REST_Response(['token' => $payment_intent['client_secret'] ?? null], 200);


    // create stripe payment intent
}

function get_stripe_payment_intent($amount, $currency) {
    // Set the Stripe API endpoint
    $url = "https://api.stripe.com/v1/payment_intents";
    
    // Set your Stripe secret key
    $apiKey = constant('STRIPE_SECRET_KEY');

    // Create the data array
    $data = [
        'amount' => $amount,
        'currency' => $currency,
        'automatic_payment_methods[enabled]' => 'true',
    ];

    // Initialize a cURL session
    $ch = curl_init();

    // Set the cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $apiKey . ":");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

    // Execute the cURL session and capture the response
    $response = curl_exec($ch);

    // Check for errors
    if(curl_errno($ch)) {
        $error_msg = 'Curl error: ' . curl_error($ch);
        curl_close($ch);
        return json_encode(['error' => $error_msg]);
    }

    // Close the cURL session
    curl_close($ch);

    // Decode the JSON response
    $response_data = json_decode($response, true);

    // Return the response data
    return $response_data;
}

function stripe_payment_element_shortcode() {
    ob_start();
    ?>

    <script src="https://js.stripe.com/v3/"></script>

    <div>Email</div>
    <input id="email-element" />
    
    <div id="payment-element"></div>
    <button id="submit-button">Pay</button>
    <div id="payment-message" class="hidden"></div>

    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', async function () {
            const urlParams = new URLSearchParams(window.location.search);
            const productId = urlParams.get('product_id') || 1;
            const userEmail = urlParams.get('email') || null;

            let paymentToken = localStorage.getItem('checkout_token');
            if(!paymentToken) {
                let checkoutResponse = await fetch('/wp-json/custom/v1/initiate-checkout', {method: 'POST', body: JSON.stringify({'product_id': productId})}).then(r => r.json());
                if(!checkoutResponse.token) return;

                paymentToken = checkoutResponse.token;
                localStorage.setItem('checkout_token', paymentToken);
            }

            const stripe = Stripe('<?= constant('STRIPE_PUBLISHABLE_KEY') ?>');

            const appearance = { /* appearance */ };
            const options = {
                fields: {
                    billingDetails: {
                        email: 'auto'
                    }
                },
            };
            if(userEmail) {
                document.getElementById('email-element').value = userEmail;
            }
            const elements = stripe.elements({ clientSecret: paymentToken}, appearance);
            const paymentElement = elements.create('payment', options);
            paymentElement.mount('#payment-element');

            document.querySelector('#submit-button')?.addEventListener('click', async () => {
                const userProvidedEmail = document.getElementById('email-element').value;

                localStorage.removeItem('checkout_token');

                const {error} = await stripe.confirmPayment({
                    elements,
                    confirmParams: {
                        return_url: '<?= (constant('SITE_URL') . '/esa-letter/thank-you') ?>',
                    },
                    payment_method_data: {
                        billing_details: {
                            email: userProvidedEmail,
                        }
                    }
                });

                if (error) {
                    document.getElementById('payment-message').textContent = error.message;
                }
            })

        });
    </script>

    <?php
    return ob_get_clean();
}
add_shortcode('stripe_payment_element', 'stripe_payment_element_shortcode');