jQuery(document).ready(function($) {
    'use strict';

    console.log('Key2Pay checkout script loaded');

    // Add any client-side validation or UI enhancements here
    // For example, you could add loading states, form validation, etc.

    // Example: Add loading state when payment method is selected
    $('input[name="payment_method"]').on('change', function() {
        if ($(this).val() === 'key2pay_instapay') {
            console.log('InstaPay payment method selected');
        }
    });

    // Example: Add custom validation if needed
    $('form.checkout').on('checkout_place_order_key2pay_instapay', function() {
        console.log('InstaPay payment processing...');
        // You can add custom validation here if needed
        return true;
    });
});