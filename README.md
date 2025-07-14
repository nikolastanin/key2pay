# Key2Pay WooCommerce Integration

A comprehensive WooCommerce payment gateway for Key2Pay with multiple authentication methods and secure payment processing.

## ✅ Current Status

**Ready for testing and development** - The plugin now includes:

- ✅ **Two Payment Methods**: Credit Card S2S and InstaPay redirect
- ✅ **Multiple Authentication Methods**: Basic, API Key, Bearer Token, HMAC Signed
- ✅ **Secure Webhook Handling**: Signature verification and proper authentication
- ✅ **Proper Gateway Registration**: Both payment methods are registered
- ✅ **Flexible Currency Support**: Configurable currency restrictions
- ✅ **Debug Logging**: Comprehensive logging for troubleshooting
- ✅ **Refund Support**: Full refund processing capabilities

## 🔐 Authentication Methods

The plugin supports multiple authentication methods for maximum flexibility:

1. **Basic Authentication** - Merchant ID + Password (legacy)
2. **API Key Authentication** - API Key in headers (modern)
3. **Bearer Token Authentication** - OAuth-style token access
4. **HMAC Signed Authentication** - Maximum security with request signing

See [AUTHENTICATION.md](AUTHENTICATION.md) for detailed configuration instructions.

## 🚀 Quick Setup

1. **Install the plugin** in your WordPress site
2. **Go to WooCommerce → Settings → Payments**
3. **Enable Key2Pay InstaPay** (recommended for security)
4. **Configure authentication** with your Key2Pay credentials
5. **Test with sandbox** before going live

## 🔒 Security Features

- **No PCI DSS burden** with InstaPay redirect method
- **Webhook signature verification** for secure callbacks
- **Multiple authentication options** for different security needs
- **Secure credential storage** in WooCommerce settings
- **HTTPS enforcement** for all payment communications

## 📋 Requirements

- WordPress 5.0+
- WooCommerce 8.0+
- PHP 7.4+
- SSL certificate (for production)

## 🛠️ Development

- **Test Environment**: Use Key2Pay sandbox for development
- **Debug Mode**: Enable logging for troubleshooting
- **Webhook Testing**: Verify callback handling with test transactions

## 📞 Support

For technical support or questions about Key2Pay integration, please refer to:
- [Authentication Documentation](AUTHENTICATION.md)
- WooCommerce logs for detailed error information
- Key2Pay's official API documentation