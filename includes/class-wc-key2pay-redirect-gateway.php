<?php

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * WC_Key2Pay_Redirect_Gateway Class.
 *
 * A secure redirect-based WooCommerce payment gateway for Key2Pay.
 * Customers are redirected to Key2Pay's hosted payment page.
 *
 * @extends WC_Payment_Gateway
 */
class WC_Key2Pay_Redirect_Gateway extends WC_Payment_Gateway
{
    public $id;
    public $icon;
    public $has_fields;
    public $method_title;
    public $method_description;
    public $title;
    public $description;
    public $enabled;
    public $merchant_id;
    public $password;
    public $api_key;
    public $secret_key;
    public $access_token;
    public $auth_type;
    public $debug;
    public $log;
    public $form_fields;
    public $auth_handler;

    /**
     * API Endpoint for Key2Pay redirect payments.
     * This creates a payment session and returns a redirect URL.
     */
    public const API_REDIRECT_ENDPOINT = 'https://api.key2payment.com/PaymentToken/Create';
    
    /**
     * Payment method type for redirect-based payments.
     */
    public const PAYMENT_METHOD_TYPE = 'PHQR';
    
    /**
     * Default language for payment page.
     */
    public const DEFAULT_LANGUAGE = 'en';

    /**
     * Constructor for the gateway.
     */
    public function __construct()
    {
        // Initialize logger
        $this->log = wc_get_logger();
        
        $this->id                 = 'key2pay_redirect'; // Unique ID for redirect gateway.
        $this->icon               = apply_filters('woocommerce_key2pay_redirect_icon', plugin_dir_url(dirname(__FILE__)) . 'assets/images/key2pay-admin.webp');
        $this->has_fields         = false; // Redirect-based payment method - no fields needed.
        $this->method_title       = __('Key2Pay Secure Redirect', 'key2pay');
        $this->method_description = __('Accept payments via Key2Pay with maximum security. Customers are redirected to Key2Pay\'s hosted payment page.', 'key2pay');

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Get settings values.
        $this->title          = $this->get_option('title');
        $this->description    = $this->get_option('description');
        $this->enabled        = $this->get_option('enabled');
        $this->debug          = 'yes' === $this->get_option('debug');
        
        // Get credentials from admin settings
        $this->merchant_id    = sanitize_text_field($this->get_option('merchant_id'));
        $this->password       = sanitize_text_field($this->get_option('password'));



        // Initialize authentication handler
        try {
            $this->auth_handler = new WC_Key2Pay_Auth('basic');
            $this->auth_handler->set_credentials(array(
                'merchant_id' => $this->merchant_id,
                'password' => $this->password,
            ));
            $this->auth_handler->set_debug($this->debug);
        } catch (Exception $e) {
            $this->log->error('Key2Pay Error: Failed to initialize auth handler: ' . $e->getMessage(), array('source' => 'key2pay-redirect'));
        }

        // Add hooks for admin settings and payment processing.
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_thankyou_' . $this->id, array($this, 'thankyou_page'));

        // Webhook listener for payment status updates.
        add_action('woocommerce_api_' . strtolower($this->id), array($this, 'handle_webhook_callback'));
    }

    /**
     * Initialize Gateway Settings Form Fields.
     */
    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => __('Enable/Disable', 'key2pay'),
                'type'    => 'checkbox',
                'label'   => __('Enable Key2Pay Secure Redirect', 'key2pay'),
                'default' => 'yes', // Enable by default for testing
            ),
            'title' => array(
                'title'       => __('Title', 'key2pay'),
                'type'        => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'key2pay'),
                'default'     => __('Key2Pay Secure Redirect', 'key2pay'),
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => __('Description', 'key2pay'),
                'type'        => 'textarea',
                'description' => __('This controls the description which the user sees during checkout.', 'key2pay'),
                'default'     => __('Pay securely using Key2Pay. You will be redirected to complete your payment securely.', 'key2pay'),
                'desc_tip'    => true,
            ),
            'credentials_section' => array(
                'title'       => __('Key2Pay Credentials', 'key2pay'),
                'type'        => 'title',
                'description' => __('Enter your Key2Pay credentials. These are required for the gateway to work.', 'key2pay'),
            ),
            'merchant_id' => array(
                'title'       => __('Merchant ID', 'key2pay'),
                'type'        => 'text',
                'description' => __('Your Key2Pay Merchant ID.', 'key2pay'),
                'default'     => '',
                'desc_tip'    => true,
            ),
            'password' => array(
                'title'       => __('Password', 'key2pay'),
                'type'        => 'password',
                'description' => __('Your Key2Pay API Password.', 'key2pay'),
                'default'     => '',
                'desc_tip'    => true,
            ),
            'debug' => array(
                'title'       => __('Debug Log', 'key2pay'),
                'type'        => 'checkbox',
                'label'       => __('Enable logging', 'key2pay'),
                'default'     => 'no',
                'description' => __('Log Key2Pay redirect events.', 'key2pay'),
            ),
        );
    }

    /**
     * Payment fields - just show description since this is redirect-based.
     */
    public function payment_fields()
    {
        if ($this->description) {
            echo wpautop(wp_kses_post($this->description));
        } else {
            // Default description if none is set
            echo wpautop(wp_kses_post(__('Pay securely using Key2Pay. You will be redirected to complete your payment securely.', 'woocommerce-key2pay-gateway')));
        }
    }

    /**
     * Validate fields (no custom fields needed for redirect).
     */
    public function validate_fields()
    {
        return true;
    }

    /**
     * Process the payment for redirect-based payments.
     * 
     * This method creates a payment session with Key2Pay and redirects the customer
     * to their hosted payment page. The payment status will be updated via webhook.
     *
     * @param int $order_id Order ID.
     * @return array An array with 'result' and 'redirect' keys.
     */
    public function process_payment($order_id)
    {
        $order = wc_get_order($order_id);

        if ($this->debug) {
            $this->log->debug(sprintf('Processing redirect payment for order #%s.', $order_id), array('source' => 'key2pay-redirect'));
        }

        // Prepare data for Key2Pay redirect API request.
        $amount           = (float) $order->get_total();
        $currency         = $order->get_currency();
        $return_url       = $this->get_return_url($order);
        $customer_ip      = WC_Geolocation::get_ip_address();
        $server_url       = WC()->api_request_url('wc_key2pay_redirect_gateway'); // Webhook endpoint for this gateway.
        


        // Prepare request data for Key2Pay API
        $request_data = array(
            'merchantid'        => $this->merchant_id,
            'password'          => $this->password,
            'payment_method'    => array('type' => self::PAYMENT_METHOD_TYPE),
            'trackid'           => $order->get_id() . '_' . time(),
            'bill_currencycode' => $currency,
            'bill_amount'       => $amount,
            'returnUrl'         => $return_url,
            'returnUrl_on_failure' => $order->get_checkout_payment_url(false),
            'productdesc'       => sprintf(__('Order %s from %s', 'key2pay'), $order->get_order_number(), get_bloginfo('name')),
            'bill_customerip'   => $customer_ip,
            'bill_phone'        => $order->get_billing_phone() ?: '',
            'bill_email'        => $order->get_billing_email(),
            'bill_country'      => $order->get_billing_country() ?: '',
            'bill_city'         => $order->get_billing_city() ?: '',
            'bill_state'        => $order->get_billing_state() ?: '',
            'bill_address'      => $order->get_billing_address_1() ?: '',
            'bill_zip'          => $order->get_billing_postcode(),
            'serverUrl'         => $server_url,
            'lang'              => self::DEFAULT_LANGUAGE,
        );

        // Get authentication headers
        $auth_headers = $this->auth_handler->get_auth_headers();
        
        // Prepare request headers
        $headers = array(
            'Content-Type' => 'application/json',
        );
        
        // Merge authentication headers
        $headers = array_merge($headers, $auth_headers);

        // Make the API call to Key2Pay redirect endpoint.
        $response = wp_remote_post(
            self::API_REDIRECT_ENDPOINT,
            array(
                'method'    => 'POST',
                'headers'   => $headers,
                'body'      => json_encode($request_data),
                'timeout'   => 60,
                'sslverify' => true,
            )
        );

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            wc_add_notice(sprintf(__('Key2Pay redirect payment error: %s', 'key2pay'), $error_message), 'error');
            if ($this->debug) {
                $this->log->error(sprintf('Key2Pay redirect API Request Failed for order #%s: %s', $order_id, $error_message), array('source' => 'key2pay-redirect'));
            }
            return array(
                'result'   => 'fail',
                'redirect' => '',
            );
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);

        if ($this->debug) {
            $this->log->debug(sprintf('Key2Pay redirect API Response for order #%s: %s', $order_id, print_r($data, true)), array('source' => 'key2pay-redirect'));
        }

        // Process the API response.
        if (isset($data->type) && 'valid' === $data->type) {
            if (isset($data->redirectUrl) && ! empty($data->redirectUrl)) {
                // Payment session created successfully, redirect customer to Key2Pay.
                $order->update_status('pending', __('Awaiting Key2Pay payment confirmation.', 'key2pay'));

                // Store Key2Pay transaction details.
                if (isset($data->transactionid)) {
                    $order->update_meta_data('_key2pay_transaction_id', $data->transactionid);
                }
                if (isset($data->trackid)) {
                    $order->update_meta_data('_key2pay_track_id', $data->trackid);
                }
                $order->save();

                return array(
                    'result'   => 'success',
                    'redirect' => $data->redirectUrl,
                );
            } else {
                // Valid response but no redirect URL, which is unexpected.
                $error_message = isset($data->error_text) ? $data->error_text : __('Payment session created, but no redirection URL received.', 'key2pay');
                wc_add_notice(sprintf(__('Key2Pay redirect failed: %s', 'key2pay'), $error_message), 'error');
                if ($this->debug) {
                    $this->log->error(sprintf('Key2Pay redirect Missing Redirect URL for order #%s: %s', $order_id, $error_message), array('source' => 'key2pay-redirect'));
                }
                return array(
                    'result'   => 'fail',
                    'redirect' => '',
                );
            }
        } else {
            // Payment session creation failed or API returned an error.
            $error_message = isset($data->error_text) ? $data->error_text : __('An unknown error occurred with Key2Pay redirect.', 'key2pay');
            wc_add_notice(sprintf(__('Key2Pay redirect payment failed: %s', 'key2pay'), $error_message), 'error');
            if ($this->debug) {
                $this->log->error(sprintf('Key2Pay redirect API Error for order #%s: %s', $order_id, $error_message), array('source' => 'key2pay-redirect'));
            }
            return array(
                'result'   => 'fail',
                'redirect' => '',
            );
        }
    }

    /**
     * Output for the order received page.
     */
    public function thankyou_page($order_id)
    {
        $order = wc_get_order($order_id);
        if ($order->has_status('pending')) {
            echo wpautop(wp_kses_post(__('Your order is awaiting payment confirmation from Key2Pay. We will update your order status once the payment is confirmed.', 'key2pay')));
        }
    }

    /**
     * Handle webhook callbacks from Key2Pay.
     * This processes payment status updates from Key2Pay.
     */
    public function handle_webhook_callback()
    {
        // Get the raw POST data
        $raw_data = file_get_contents('php://input');

        // Parse the webhook data
        $webhook_data = json_decode($raw_data, true);
        
        if (!$webhook_data) {
            if ($this->debug) {
                $this->log->error('Key2Pay Webhook: Failed to parse webhook data', array('source' => 'key2pay-redirect'));
            }
            status_header(400);
            exit();
        }

        if ($this->debug) {
            $this->log->debug('Key2Pay Webhook: Received data: ' . print_r($webhook_data, true), array('source' => 'key2pay-redirect'));
        }

        // Extract order information
        $track_id = isset($webhook_data['trackid']) ? $webhook_data['trackid'] : '';
        $transaction_id = isset($webhook_data['transactionid']) ? $webhook_data['transactionid'] : '';
        $result = isset($webhook_data['result']) ? $webhook_data['result'] : '';
        $error_text = isset($webhook_data['error_text']) ? $webhook_data['error_text'] : '';

        // Find the order by track ID
        $order = null;
        if ($track_id) {
            // Extract order ID from track_id (format: order_id_timestamp)
            $parts = explode('_', $track_id);
            if (count($parts) >= 2) {
                $order_id = $parts[0];
                $order = wc_get_order($order_id);
            }
        }

        if (!$order) {
            if ($this->debug) {
                $this->log->error('Key2Pay Webhook: Order not found for track_id: ' . $track_id, array('source' => 'key2pay-redirect'));
            }
            status_header(404);
            exit();
        }

        if ($this->debug) {
            $this->log->debug('Key2Pay Webhook: Processing order #' . $order->get_id(), array('source' => 'key2pay-redirect'));
        }

        // Process the payment status
        if ($result === 'CAPTURED') {
            // Payment successful
            $order->payment_complete($transaction_id);
            $order->add_order_note(sprintf(__('Key2Pay payment completed successfully. Transaction ID: %s', 'key2pay'), $transaction_id));
            
            if ($this->debug) {
                $this->log->debug('Key2Pay Webhook: Order #' . $order->get_id() . ' marked as paid', array('source' => 'key2pay-redirect'));
            }
        } else {
            // Payment failed
            $order->update_status('failed', sprintf(__('Key2Pay payment failed. Result: %s, Error: %s', 'key2pay'), $result, $error_text));
            
            if ($this->debug) {
                $this->log->debug('Key2Pay Webhook: Order #' . $order->get_id() . ' marked as failed', array('source' => 'key2pay-redirect'));
            }
        }

        // Always acknowledge the webhook
        status_header(200);
        exit();
    }

    /**
     * Process a refund.
     *
     * @param int    $order_id Order ID.
     * @param float  $amount Refund amount.
     * @param string $reason Refund reason.
     * @return bool True if refund was successful, false otherwise.
     */
    public function process_refund($order_id, $amount = null, $reason = '')
    {
        // Redirect gateway refunds would use the same refund API as other methods
        $main_gateway = new WC_Key2Pay_Gateway();
        return $main_gateway->process_refund($order_id, $amount, $reason);
    }

    /**
     * Check if the gateway is available for use.
     *
     * @return bool
     */
    public function is_available()
    {
        $log_file = WP_CONTENT_DIR . '/uploads/mycustomlog.log';
        $log_message = '[' . date('Y-m-d H:i:s') . '] Key2Pay Debug: Redirect gateway is_available() called' . PHP_EOL;
        error_log($log_message, 3, $log_file);
        
        // Check if gateway is enabled
        if ('yes' !== $this->enabled) {
            $log_message = '[' . date('Y-m-d H:i:s') . '] Key2Pay Debug: Gateway disabled' . PHP_EOL;
            error_log($log_message, 3, $log_file);
            return false;
        }

        // Check if credentials are provided
        if (empty($this->merchant_id) || empty($this->password)) {
            $log_message = '[' . date('Y-m-d H:i:s') . '] Key2Pay Debug: Missing credentials - merchant_id: ' . (empty($this->merchant_id) ? 'empty' : 'set') . ', password: ' . (empty($this->password) ? 'empty' : 'set') . PHP_EOL;
            error_log($log_message, 3, $log_file);
            
            if (is_admin() && current_user_can('manage_woocommerce') && (! defined('DOING_AJAX') || ! DOING_AJAX)) {
                wc_print_notice(sprintf(__('Key2Pay redirect is enabled but credentials are not configured. %sClick here to configure.%s', 'key2pay'), '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=' . $this->id) . '">', '</a>'), 'error');
            }
            return false;
        }

        $log_message = '[' . date('Y-m-d H:i:s') . '] Key2Pay Debug: Gateway available - all checks passed' . PHP_EOL;
        error_log($log_message, 3, $log_file);
        
        return parent::is_available();
    }
} 