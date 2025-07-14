<?php
/**
 * Test file to verify Key2Pay plugin is loading correctly
 * Remove this file after testing
 */

// Test if we can access the plugin files
echo "Testing Key2Pay Plugin Loading...\n";

// Check if auth class exists
if (class_exists('WC_Key2Pay_Auth')) {
    echo "✅ WC_Key2Pay_Auth class found\n";
    
    // Test getting auth types
    try {
        $auth_types = WC_Key2Pay_Auth::get_auth_types();
        echo "✅ Auth types retrieved: " . print_r($auth_types, true) . "\n";
    } catch (Exception $e) {
        echo "❌ Error getting auth types: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ WC_Key2Pay_Auth class not found\n";
}

// Check if gateway classes exist
if (class_exists('WC_Key2Pay_Gateway')) {
    echo "✅ WC_Key2Pay_Gateway class found\n";
} else {
    echo "❌ WC_Key2Pay_Gateway class not found\n";
}

if (class_exists('WC_Key2Pay_InstaPay_Gateway')) {
    echo "✅ WC_Key2Pay_InstaPay_Gateway class found\n";
} else {
    echo "❌ WC_Key2Pay_InstaPay_Gateway class not found\n";
}

echo "Test completed.\n"; 