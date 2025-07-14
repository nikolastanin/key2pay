<?php
/**
 * Debug script to test Key2Pay gateway loading
 * Access this file directly in your browser to see debug output
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h2>Key2Pay Gateway Debug Test</h2>";

// Check if WooCommerce is active
if (!class_exists('WooCommerce')) {
    echo "<p style='color: red;'>❌ WooCommerce is not active</p>";
    exit;
}
echo "<p style='color: green;'>✅ WooCommerce is active</p>";

// Check if our classes exist
if (class_exists('WC_Key2Pay_Gateway')) {
    echo "<p style='color: green;'>✅ WC_Key2Pay_Gateway class exists</p>";
} else {
    echo "<p style='color: red;'>❌ WC_Key2Pay_Gateway class not found</p>";
}

if (class_exists('WC_Key2Pay_InstaPay_Gateway')) {
    echo "<p style='color: green;'>✅ WC_Key2Pay_InstaPay_Gateway class exists</p>";
} else {
    echo "<p style='color: red;'>❌ WC_Key2Pay_InstaPay_Gateway class not found</p>";
}

if (class_exists('WC_Key2Pay_Auth')) {
    echo "<p style='color: green;'>✅ WC_Key2Pay_Auth class exists</p>";
} else {
    echo "<p style='color: red;'>❌ WC_Key2Pay_Auth class not found</p>";
}

// Get all available payment gateways
$available_gateways = WC()->payment_gateways()->payment_gateways();
echo "<h3>Available Payment Gateways:</h3>";
echo "<ul>";
foreach ($available_gateways as $gateway_id => $gateway) {
    $status = $gateway->enabled === 'yes' ? '✅ Enabled' : '❌ Disabled';
    echo "<li><strong>{$gateway->method_title}</strong> (ID: {$gateway_id}) - {$status}</li>";
}
echo "</ul>";

// Check specifically for our gateways
if (isset($available_gateways['key2pay'])) {
    echo "<p style='color: green;'>✅ Key2Pay main gateway found in available gateways</p>";
} else {
    echo "<p style='color: red;'>❌ Key2Pay main gateway NOT found in available gateways</p>";
}

if (isset($available_gateways['key2pay_instapay'])) {
    echo "<p style='color: green;'>✅ Key2Pay InstaPay gateway found in available gateways</p>";
    $instapay = $available_gateways['key2pay_instapay'];
    echo "<p>InstaPay Gateway Details:</p>";
    echo "<ul>";
    echo "<li>ID: {$instapay->id}</li>";
    echo "<li>Title: {$instapay->method_title}</li>";
    echo "<li>Enabled: {$instapay->enabled}</li>";
    echo "<li>Has Fields: " . ($instapay->has_fields ? 'Yes' : 'No') . "</li>";
    echo "</ul>";
} else {
    echo "<p style='color: red;'>❌ Key2Pay InstaPay gateway NOT found in available gateways</p>";
}

// Test gateway availability
echo "<h3>Gateway Availability Test:</h3>";
if (isset($available_gateways['key2pay_instapay'])) {
    $instapay = $available_gateways['key2pay_instapay'];
    $is_available = $instapay->is_available();
    echo "<p>InstaPay gateway is_available(): " . ($is_available ? '✅ True' : '❌ False') . "</p>";
}

echo "<hr>";
echo "<p><strong>Note:</strong> Check your WordPress error logs for additional debug information.</p>";
?> 