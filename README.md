# Laravel Tamara Payment Gateway

[![Latest Version](https://img.shields.io/packagist/v/aghfatehi/laravel-tamara.svg)](https://packagist.org/packages/aghfatehi/laravel-tamara)
[![Laravel](https://img.shields.io/badge/Laravel-10~13-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.1+-blue.svg)](https://php.net)
[![License](https://img.shields.io/github/license/aghfatehi/laravel-tamara)](LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/aghfatehi/laravel-tamara.svg)](https://packagist.org/packages/aghfatehi/laravel-tamara)

A professional Laravel package for integrating [Tamara](https://tamara.co) - the leading Buy Now Pay Later (BNPL) payment solution in the Middle East. Supports Saudi Arabia, UAE, Kuwait, Bahrain, Qatar, and Oman.

## Features

- ✅ Full Tamara Online Checkout flow
- ✅ Payment Types lookup
- ✅ Create Checkout Sessions
- ✅ Authorise / Capture / Cancel / Refund orders
- ✅ Webhook management (register, list, update, delete)
- ✅ Sandbox & Production environments
- ✅ Multi-currency support (SAR, AED, KWD, BHD, QAR, OMR)
- ✅ Multi-country support (SA, AE, KW, BH, QA, OM)
- ✅ In-store checkout support
- ✅ cURL HTTP client (no Guzzle)
- ✅ Configurable routes prefix & middleware
- ✅ Transaction logging migration
- ✅ Laravel 10, 11, 12 & 13 compatible
- ✅ PHP 8.1+

## Requirements

| Laravel | PHP   | Package Version |
|---------|-------|-----------------|
| 10.x    | ^8.1  | ^1.0            |
| 11.x    | ^8.2  | ^1.0            |
| 12.x    | ^8.2  | ^1.0            |
| 13.x    | ^8.2  | ^1.0            |

## Installation

```bash
composer require aghfatehi/laravel-tamara
```

## Configuration

### 1. Publish Configuration

```bash
php artisan vendor:publish --tag=tamara-config
```

### 2. Publish Migration (Optional)

```bash
php artisan vendor:publish --tag=tamara-migrations
php artisan migrate
```

### 3. Environment Variables

Add these to your `.env` file:

```env
TAMARA_SANDBOX_MODE=true              # true = sandbox, false = production
TAMARA_API_TOKEN=your-api-token-here  # API token from Tamara Merchant Dashboard
TAMARA_COUNTRY_CODE=SA                # SA, AE, KW, BH, QA, OM
TAMARA_CURRENCY=SAR                   # SAR, AED, KWD, BHD, QAR, OMR
TAMARA_INSTALMENTS=3                  # Number of instalments (3, 6, 12)
TAMARA_PAYMENT_TYPE=PAY_BY_INSTALMENTS # PAY_BY_INSTALMENTS or PAY_NEXT_MONTH
TAMARA_LOCALE=en_US                   # en_US or ar_SA
TAMARA_LOGGING=true                   # Enable API request/response logging
# true = enabled | false = disabled
TAMARA_ROUTE_PREFIX=tamara            # URL prefix for package routes
```

### 4. Service Provider

The package auto-discovers via Laravel's package discovery. If you disable discovery, register manually in `config/app.php`:

```php
'providers' => [
    Aghfatehi\Tamara\TamaraServiceProvider::class,
],
```

### 5. Facade (Optional)

```php
'aliases' => [
    'Tamara' => Aghfatehi\Tamara\Facades\Tamara::class,
],
```

## Usage

### Quick Start - Frontend Checkout

```php
use Aghfatehi\Tamara\Facades\Tamara;

// Get available payment types
$types = Tamara::getPaymentTypes('SA', 'SAR', 500);

// Build checkout request body with example values
$requestbody = [
    'total_amount' => [                          // Order total amount
        'amount' => 500,
        'currency' => 'SAR',
    ],
    'shipping_amount' => [                       // Shipping cost
        'amount' => 0,
        'currency' => 'SAR',
    ],
    'tax_amount' => [                            // Tax amount
        'amount' => 0,
        'currency' => 'SAR',
    ],
    'order_reference_id' => uniqid('tamara_'),   // Unique order reference
    'order_number' => 'ORD-' . time(),            // Merchant order number
    'items' => [                                  // Order items (max 50)
        [
            'name' => 'Order Payment',
            'type' => 'Digital',                  // Digital or Physical
            'reference_id' => '1',                // Item ID in your system
            'sku' => 'PAYMENT-001',
            'quantity' => 1,
            'unit_price' => [                     // Price per unit
                'amount' => 500,
                'currency' => 'SAR',
            ],
            'total_amount' => [                   // quantity * unit_price
                'amount' => 500,
                'currency' => 'SAR',
            ],
        ],
    ],
    'consumer' => [                               // Customer details
        'email' => 'customer@example.com',
        'first_name' => 'Ahmed',
        'last_name' => 'Ali',
        'phone_number' => '500000000',            // Without country code prefix
    ],
    'country_code' => 'SA',                       // SA, AE, KW, BH, QA, OM
    'description' => 'Payment for order',
    'merchant_url' => [                           // Callback URLs
        'success' => route('tamara.callback'),
        'failure' => route('tamara.failure'),
        'cancel' => route('tamara.cancel'),
        'notification' => route('tamara.webhook'),
    ],
    'payment_type' => 'PAY_BY_INSTALMENTS',       // PAY_BY_INSTALMENTS or PAY_NEXT_MONTH
    'instalments' => 3,                           // 3,4 or 6
    'billing_address' => [                        // Billing address
        'city' => 'Riyadh',
        'country_code' => 'SA',
        'first_name' => 'Ahmed',
        'last_name' => 'Ali',
        'line1' => 'Default Address',
        'phone_number' => '500000000',
    ],
    'shipping_address' => [                       // Shipping address
        'city' => 'Riyadh',
        'country_code' => 'SA',
        'first_name' => 'Ahmed',
        'last_name' => 'Ali',
        'line1' => 'Default Address',
        'phone_number' => '500000000',
    ],
    'platform' => 'Laravel',                      // Platform name
    'is_mobile' => false,                         // true if mobile app
    'locale' => 'en_US',                          // en_US or ar_SA
];

// Create checkout session
$response = Tamara::createCheckout($requestbody);

// Redirect customer to Tamara checkout
if (isset($response['checkout_url'])) {
    return redirect()->away($response['checkout_url']);
}

// Handle errors
if (isset($response['errors'])) {
    $errorMessage = $response['errors'][0]['message'] ?? 'Payment failed';
    return back()->with('error', $errorMessage);
}
```

### Using Routes

The package registers these routes under the configured prefix (`/tamara` by default):

| Method | URI                      | Name                | Description              |
|--------|--------------------------|---------------------|--------------------------|
| GET    | `/tamara/payment/types`  | `tamara.payment.types` | Get eligible payment types |
| POST   | `/tamara/pay`            | `tamara.pay`        | Initiate checkout         |
| ANY    | `/tamara/callback`       | `tamara.callback`   | Payment callback          |
| GET    | `/tamara/cancel`         | `tamara.cancel`     | Cancel handler            |
| GET    | `/tamara/failure`        | `tamara.failure`    | Failure handler           |
| POST   | `/tamara/webhook`        | `tamara.webhook`    | Webhook receiver          |
| POST   | `/tamara/authorise`      | `tamara.authorise`  | Authorise order           |

### API Methods

```php
// Payment Types
$types = Tamara::getPaymentTypes('SA', 'SAR', 500, '500000000');

// Checkout
$checkout = Tamara::createCheckout($data);

// Order Management
$order = Tamara::getOrder('order-id-here');
$order = Tamara::getOrderByReferenceId('ref-id-here');

// Authorise / Capture / Cancel / Refund
$authorised = Tamara::authoriseOrder('order-id');
$captured = Tamara::captureOrder('order-id', $data);
$cancelled = Tamara::cancelOrder('order-id', $data);
$refunded = Tamara::refundOrder('order-id', 500, 'SAR', 'Refund comment');

// Webhook Management
$webhook = Tamara::webhookRegister('https://example.com/webhook', [
    'order_approved',
    'order_declined',
    'order_authorised',
    'order_captured',
    'order_refunded',
]);
$list = Tamara::webhookList();
$detail = Tamara::webhookGet('webhook-id');
Tamara::webhookDelete('webhook-id');
Tamara::webhookUpdate('webhook-id', 'https://example.com/webhook', [...]);
```

## Customising Routes

Publish the config and modify the `routes` section:

```php
// config/tamara.php
'routes' => [
    'prefix' => 'payment/tamara',     // Custom prefix
    'middleware' => ['web', 'auth'],   // Custom middleware
],
```

## Handling Webhooks

The webhook endpoint logs all incoming events. Extend the controller or listen to the log to implement your business logic:

```php
// Example webhook payload handling
public function webhook(Request $request)
{
    $event = $request->input('event_type');
    $orderId = $request->input('order_id');
    $status = $request->input('status');

    switch ($event) {
        case 'order_approved':
            // Mark order as approved
            break;
        case 'order_captured':
            // Fulfill the order
            break;
        case 'order_refunded':
            // Process refund
            break;
    }
}
```

## Testing

```bash
composer test
```

## Changelog

See [CHANGELOG](CHANGELOG.md) for recent changes.

## Security

If you discover security issues, please email fathi.a.n2002@gmail.com instead of using the issue tracker.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## Support

- **Issues**: [GitHub Issues](https://github.com/aghfatehi/laravel-tamara/issues)
- **Tamara Docs**: [https://docs.tamara.co](https://docs.tamara.co)
- **Author**: AL-AGHBARI Fatehi ([fathi.a.n2002@gmail.com](mailto:fathi.a.n2002@gmail.com))

## Countries & Currencies

| Country       | Code | Currency | Code |
|---------------|------|----------|------|
| Saudi Arabia  | SA   | Riyal    | SAR  |
| UAE           | AE   | Dirham   | AED  |
| Kuwait        | KW   | Dinar    | KWD  |
| Bahrain       | BH   | Dinar    | BHD  |
| Qatar         | QA   | Riyal    | QAR  |
| Oman          | OM   | Riyal    | OMR  |
