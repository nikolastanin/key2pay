<?php

/**
 * Plugin Name: WooCommerce Key2Pay Gateway
 * Plugin URI:  https://axons.com/
 * Description: A secure redirect-based WooCommerce payment gateway for Key2Pay.
 * Version:     1.0.0
 * Author:      AX
 * Author URI:  https://axons.com/
 * Text Domain: key2pay
 * Domain Path: /languages
 * WC requires at least: 8.0
 * WC tested up to: 8.0
 */

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * The main plugin class.
 */
class WC_Key2Pay_Gateway_Plugin
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        add_action('plugins_loaded', array( $this, 'init' ));
    }

    /**
     * Initialize the plugin.
     */
    public function init()
    {
        // Check if WooCommerce is active.
        if (! class_exists('WooCommerce')) {
            return;
        }

        // Include the redirect gateway class.
        require_once plugin_dir_path(__FILE__) . 'includes/class-wc-key2pay-auth.php';
        require_once plugin_dir_path(__FILE__) . 'includes/class-wc-key2pay-redirect-gateway.php';

        // Add the Key2Pay Gateway to WooCommerce.
        add_filter('woocommerce_payment_gateways', array( $this, 'add_key2pay_gateway' ));
        
        // Add debugging for available gateways
        add_filter('woocommerce_available_payment_gateways', array( $this, 'debug_available_gateways' ), 999);

        // Load plugin text domain.
        load_plugin_textdomain('key2pay', false, basename(dirname(__FILE__)) . '/languages');

        // Enqueue scripts and styles for the checkout page.
        add_action('wp_enqueue_scripts', array( $this, 'key2pay_enqueue_checkout_scripts' ));
        
        // Add debugging for checkout template
        add_action('woocommerce_checkout_payment', array( $this, 'debug_checkout_payment' ), 5);
    }

    /**
     * Enqueue scripts and styles for the Key2Pay checkout.
     *
     * This function checks if the current page is the checkout page and not an endpoint,
     * then enqueues the necessary JavaScript and CSS files for the Key2Pay payment gateway.
     */
    public function key2pay_enqueue_checkout_scripts()
    {
        // todo:remove time()
        $version = time();
        if (is_checkout() && ! is_wc_endpoint_url()) {
            wp_enqueue_script('key2pay-checkout', plugin_dir_url(__FILE__) . 'assets/js/key2pay-checkout.js', array( 'jquery' ), $version, true);
            wp_enqueue_style('key2pay-styles', plugin_dir_url(__FILE__) . 'assets/css/key2pay.css', array(), $version);
        }
    }

    /**
     * Add the Key2Pay Gateway to the list of available gateways.
     *
     * @param array $gateways Available gateways.
     * @return array $gateways Updated gateways.
     */
    public function add_key2pay_gateway($gateways)
    {
        // Debug logging to custom log file
        $log_file = WP_CONTENT_DIR . '/uploads/mycustomlog.log';
        $log_message = '[' . date('Y-m-d H:i:s') . '] Key2Pay Debug: Adding redirect gateway to WooCommerce. Current gateways: ' . print_r($gateways, true) . PHP_EOL;
        error_log($log_message, 3, $log_file);
        
        $gateways[] = 'WC_Key2Pay_Redirect_Gateway';
        
        $log_message = '[' . date('Y-m-d H:i:s') . '] Key2Pay Debug: Gateways after adding: ' . print_r($gateways, true) . PHP_EOL;
        error_log($log_message, 3, $log_file);
        
        return $gateways;
    }
    
    /**
     * Debug available payment gateways on checkout
     *
     * @param array $available_gateways Available gateways.
     * @return array $available_gateways Available gateways.
     */
    public function debug_available_gateways($available_gateways)
    {
        // Debug logging to custom log file
        $log_file = WP_CONTENT_DIR . '/uploads/mycustomlog.log';
        $log_message = '[' . date('Y-m-d H:i:s') . '] Key2Pay Debug: Available gateways filter called' . PHP_EOL;
        error_log($log_message, 3, $log_file);
        
        // Check if we're on checkout page
        $log_message = '[' . date('Y-m-d H:i:s') . '] Key2Pay Debug: Is checkout page: ' . (is_checkout() ? 'yes' : 'no') . PHP_EOL;
        error_log($log_message, 3, $log_file);
        
        // Check if cart exists and needs payment
        if (WC()->cart) {
            $log_message = '[' . date('Y-m-d H:i:s') . '] Key2Pay Debug: Cart needs payment: ' . (WC()->cart->needs_payment() ? 'yes' : 'no') . PHP_EOL;
            error_log($log_message, 3, $log_file);
            
            $log_message = '[' . date('Y-m-d H:i:s') . '] Key2Pay Debug: Cart total: ' . WC()->cart->get_total() . PHP_EOL;
            error_log($log_message, 3, $log_file);
        } else {
            $log_message = '[' . date('Y-m-d H:i:s') . '] Key2Pay Debug: Cart is null' . PHP_EOL;
            error_log($log_message, 3, $log_file);
        }
        
        $log_message = '[' . date('Y-m-d H:i:s') . '] Key2Pay Debug: Available gateways count: ' . count($available_gateways) . PHP_EOL;
        error_log($log_message, 3, $log_file);
        
        foreach ($available_gateways as $gateway_id => $gateway) {
            $log_message = '[' . date('Y-m-d H:i:s') . '] Key2Pay Debug: Available gateway - ID: ' . $gateway_id . ', Title: ' . $gateway->get_title() . ', Enabled: ' . $gateway->enabled . ', Method Title: ' . $gateway->method_title . PHP_EOL;
            error_log($log_message, 3, $log_file);
        }
        
        return $available_gateways;
    }
    
    /**
     * Debug checkout payment template
     */
    public function debug_checkout_payment()
    {
        // Debug logging to custom log file
        $log_file = WP_CONTENT_DIR . '/uploads/mycustomlog.log';
        $log_message = '[' . date('Y-m-d H:i:s') . '] Key2Pay Debug: Checkout payment template hook called' . PHP_EOL;
        error_log($log_message, 3, $log_file);
        
        if (WC()->payment_gateways()) {
            $available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
            $log_message = '[' . date('Y-m-d H:i:s') . '] Key2Pay Debug: Template - Available gateways count: ' . count($available_gateways) . PHP_EOL;
            error_log($log_message, 3, $log_file);
            
            foreach ($available_gateways as $gateway_id => $gateway) {
                $log_message = '[' . date('Y-m-d H:i:s') . '] Key2Pay Debug: Template - Gateway: ' . $gateway_id . ' - ' . $gateway->get_title() . PHP_EOL;
                error_log($log_message, 3, $log_file);
            }
        }
    }
}

// Instantiate the plugin.
new WC_Key2Pay_Gateway_Plugin();
