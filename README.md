# Key2Pay WooCommerce Integration

A secure, production-ready WooCommerce payment gateway for Key2Pay, featuring robust authentication, secure redirect payment flow, and webhook support.

## ✅ Current Status

**Ready for production** – The plugin now includes:

- ✅ **Secure Redirect Payment Gateway**: Customers are redirected to Key2Pay’s hosted payment page for maximum security.
- ✅ **Admin Credential Fields**: Enter your Key2Pay Merchant ID and Password in the WooCommerce admin.
- ✅ **No Hardcoded Credentials**: All credentials are managed via the admin interface.
- ✅ **Webhook Handling**: Automatic order status updates via Key2Pay webhooks.
- ✅ **Dynamic Currency Support**: Uses WooCommerce order currency.
- ✅ **Always English Language**: Payment page is always in English (`lang=en`).
- ✅ **Debug Logging**: Optional logging for troubleshooting.
- ✅ **WordPress Translation Ready**: Includes `.pot` and sample `.po` files.

## 🚀 Quick Setup

1. **Install the plugin** in your WordPress site.
2. **Go to WooCommerce → Settings → Payments**.
3. **Enable Key2Pay Secure Redirect**.
4. **Enter your Key2Pay Merchant ID and Password** in the gateway settings.
5. **(Optional) Enable Debug Logging** for troubleshooting.
6. **Test with sandbox credentials** before going live.

## 🔒 Security Features

- **No PCI DSS burden**: All card data is handled by Key2Pay.
- **Webhook signature verification** for secure callbacks.
- **Secure credential storage** in WooCommerce settings.
- **HTTPS enforcement** for all payment communications.

## 📋 Requirements

- WordPress 5.0+
- WooCommerce 8.0+
- PHP 7.4+
- SSL certificate (for production)

## 🛠️ Development

- **Test Environment**: Use Key2Pay sandbox for development.
- **Debug Mode**: Enable logging for troubleshooting.
- **Webhook Testing**: Verify callback handling with test transactions.

## 🌐 Translations

- Translation ready (`.pot` file included).
- Example Serbian and Japanese translations provided.

## 📞 Support

For technical support or questions about Key2Pay integration, please refer to:
- [Authentication Documentation](AUTHENTICATION.md)
- WooCommerce logs for detailed error information
- Key2Pay's official API documentation

---