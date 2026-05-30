<?php

namespace Aghfatehi\Tamara\Controllers;

use Aghfatehi\Tamara\Facades\Tamara;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;

class TamaraController extends Controller
{
    public function getPaymentTypes(Request $request)
    {
        try {
            $types = Tamara::getPaymentTypes(
                $request->country ?? config('tamara.country_code', 'SA'),
                $request->currency ?? config('tamara.currency', 'SAR'),
                $request->order_value ?? 1,
                $request->phone ?? ''
            );

            return response()->json($types);
        } catch (\Throwable $e) {
            Log::error('Tamara getPaymentTypes failed: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function pay(Request $request)
    {
        Log::info('Initiating Tamara checkout...');

        $amount = $request->input('amount', 0);
        $currency = config('tamara.currency', 'SAR');
        $countryCode = config('tamara.country_code', 'SA');

        $user = $request->user();
        $firstName = $user?->name ?? $request->input('first_name', 'Customer');
        $lastName = $user?->name ?? $request->input('last_name', 'Customer');
        $email = $user?->email ?? $request->input('email', 'customer@example.com');
        $phone = $user?->phone ?? $request->input('phone', '');

        $requestBody = [
            'total_amount' => ['amount' => $amount, 'currency' => $currency],
            'shipping_amount' => ['amount' => 0, 'currency' => $currency],
            'tax_amount' => ['amount' => 0, 'currency' => $currency],
            'order_reference_id' => uniqid('tamara_', true),
            'order_number' => 'ORD-' . time(),
            'items' => [
                [
                    'name' => $request->input('item_name', 'Order Payment'),
                    'type' => 'Digital',
                    'reference_id' => '1',
                    'sku' => 'PAYMENT-001',
                    'quantity' => 1,
                    'unit_price' => ['amount' => $amount, 'currency' => $currency],
                    'total_amount' => ['amount' => $amount, 'currency' => $currency],
                ],
            ],
            'consumer' => [
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone_number' => $phone,
            ],
            'country_code' => $countryCode,
            'description' => $request->input('description', 'Payment for order'),
            'merchant_url' => [
                'cancel' => route('tamara.cancel'),
                'failure' => route('tamara.failure'),
                'success' => route('tamara.callback'),
                'notification' => route('tamara.webhook'),
            ],
            'payment_type' => config('tamara.payment_type', 'PAY_BY_INSTALMENTS'),
            'instalments' => (int) config('tamara.instalments', 3),
            'billing_address' => [
                'city' => $request->input('city', 'Riyadh'),
                'country_code' => $countryCode,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'line1' => $request->input('address_line1', 'Default Address'),
                'phone_number' => $phone,
            ],
            'shipping_address' => [
                'city' => $request->input('city', 'Riyadh'),
                'country_code' => $countryCode,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'line1' => $request->input('address_line1', 'Default Address'),
                'phone_number' => $phone,
            ],
            'platform' => config('app.name', 'Laravel'),
            'is_mobile' => $request->input('is_mobile', false),
            'locale' => config('tamara.locale', 'en_US'),
        ];

        try {
            $response = Tamara::createCheckout($requestBody);
            Log::info('Tamara Checkout Response:', $response);

            if (isset($response['errors'])) {
                $errorMessage = $response['errors'][0]['message'] ?? 'Payment failed';
                return redirect()->back()->withErrors(['error' => $errorMessage]);
            }

            if (isset($response['checkout_url'])) {
                Session::put('tamara_order_id', $response['order_id'] ?? null);
                Session::put('tamara_checkout_id', $response['checkout_id'] ?? null);

                return Redirect::to($response['checkout_url']);
            }

            return redirect()->back()->withErrors(['error' => 'Payment failed: No checkout URL returned']);
        } catch (\Throwable $e) {
            Log::error('Tamara Checkout Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function callback(Request $request)
    {
        Log::info('Tamara Callback:', $request->all());

        $orderId = $request->input('order_id') ?? Session::get('tamara_order_id');

        if (!$orderId) {
            return redirect()->route('home')->withErrors(['error' => 'Payment verification failed']);
        }

        try {
            $response = Tamara::getOrder($orderId);
            Log::info('Tamara Order Status:', $response);

            $status = $response['status'] ?? '';

            if (in_array($status, ['approved', 'authorised', 'captured', 'fully_captured'], true)) {
                Session::put('tamara_payment_success', true);
                Session::put('tamara_payment_response', json_encode($response));

                return redirect()->route('home')->with('success', __('Payment completed successfully'));
            }

            return redirect()->route('home')->withErrors(['error' => __('Payment was not completed')]);
        } catch (\Throwable $e) {
            Log::error('Tamara Callback Error: ' . $e->getMessage());
            return redirect()->route('home')->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function cancel(Request $request)
    {
        Log::info('Tamara Payment Cancelled');
        return redirect()->route('home')->with('warning', __('Payment was cancelled'));
    }

    public function failure(Request $request)
    {
        Log::info('Tamara Payment Failed');
        return redirect()->route('home')->withErrors(['error' => __('Payment failed')]);
    }

    public function webhook(Request $request)
    {
        Log::info('Tamara Webhook Received:', $request->all());

        $event = $request->input('event_type', '');
        $orderId = $request->input('order_id', '');
        $status = $request->input('status', '');

        Log::info("Tamara Webhook - Event: {$event}, Order: {$orderId}, Status: {$status}");

        return response()->json(['success' => true]);
    }

    public function authorise(Request $request)
    {
        $orderId = $request->input('order_id');

        if (!$orderId) {
            return response()->json(['error' => 'order_id is required'], 400);
        }

        try {
            $response = Tamara::authoriseOrder($orderId);
            return response()->json($response);
        } catch (\Throwable $e) {
            Log::error('Tamara Authorise Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
