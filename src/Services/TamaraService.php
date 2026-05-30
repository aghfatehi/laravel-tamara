<?php

namespace Aghfatehi\Tamara\Services;

use Illuminate\Support\Facades\Log;

class TamaraService
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('tamara');
    }

    public function baseUrl(): string
    {
        $urls = $this->config['api_urls'];

        return $this->config['sandbox']
            ? $urls['sandbox']
            : $urls['production'];
    }

    public function getPaymentTypes(
        string $country = 'SA',
        string $currency = 'SAR',
        float $orderValue = 1,
        string $phone = ''
    ): array {
        $query = [
            'country' => $country,
            'currency' => $currency,
            'order_value' => $orderValue,
        ];

        if ($phone) {
            $query['phone'] = $phone;
        }

        return $this->get('/checkout/payment-types', $query);
    }

    public function createCheckout(array $data): array
    {
        return $this->post('/checkout', $data);
    }

    public function getOrder(string $orderId): array
    {
        return $this->get("/orders/{$orderId}");
    }

    public function getOrderByReferenceId(string $referenceId): array
    {
        return $this->get("/merchants/orders/reference-id/{$referenceId}");
    }

    public function authoriseOrder(string $orderId): array
    {
        return $this->post("/orders/{$orderId}/authorise");
    }

    public function captureOrder(string $orderId, array $data = []): array
    {
        return $this->post('/payments/capture', array_merge($data, [
            'order_id' => $orderId,
        ]));
    }

    public function cancelOrder(string $orderId, array $data = []): array
    {
        return $this->post("/orders/{$orderId}/cancel", $data);
    }

    public function refundOrder(
        string $orderId,
        float $amount,
        string $currency = 'SAR',
        string $comment = ''
    ): array {
        return $this->post("/payments/simplified-refund/{$orderId}", [
            'total_amount' => [
                'amount' => $amount,
                'currency' => $currency,
            ],
            'comment' => $comment,
        ]);
    }

    public function webhookRegister(string $url, array $events, array $headers = []): array
    {
        return $this->post('/webhooks', [
            'url' => $url,
            'events' => $events,
            'headers' => $headers,
        ]);
    }

    public function webhookList(): array
    {
        return $this->get('/webhooks/list');
    }

    public function webhookGet(string $webhookId): array
    {
        return $this->get("/webhooks/{$webhookId}");
    }

    public function webhookDelete(string $webhookId): array
    {
        return $this->delete("/webhooks/{$webhookId}");
    }

    public function webhookUpdate(string $webhookId, string $url, array $events, array $headers = []): array
    {
        return $this->put("/webhooks/{$webhookId}", [
            'url' => $url,
            'events' => $events,
            'headers' => $headers,
        ]);
    }

    protected function sendRequest(string $method, string $path, array $data = []): array
    {
        $url = $this->baseUrl() . $path;

        if ($method === 'GET' && !empty($data)) {
            $url .= '?' . http_build_query($data);
        }

        $headers = [
            'Authorization: Bearer ' . $this->config['api_token'],
            'Accept: application/json',
            'Content-Type: application/json',
        ];

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        ]);

        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($curl);
        $error = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($error) {
            Log::error('Tamara API cURL Error: ' . $error, [
                'method' => $method,
                'path' => $path,
                'http_code' => $httpCode,
            ]);
            throw new \Exception('Tamara API Error: ' . $error);
        }

        return json_decode($response, true) ?? [];
    }

    protected function get(string $path, array $query = []): array
    {
        try {
            return $this->sendRequest('GET', $path, $query);
        } catch (\Throwable $e) {
            Log::error('Tamara API GET Error: ' . $e->getMessage(), [
                'path' => $path,
                'query' => $query,
            ]);
            throw $e;
        }
    }

    protected function post(string $path, array $body = []): array
    {
        try {
            return $this->sendRequest('POST', $path, $body);
        } catch (\Throwable $e) {
            Log::error('Tamara API POST Error: ' . $e->getMessage(), [
                'path' => $path,
                'body' => $body,
            ]);
            throw $e;
        }
    }

    protected function put(string $path, array $body = []): array
    {
        try {
            return $this->sendRequest('PUT', $path, $body);
        } catch (\Throwable $e) {
            Log::error('Tamara API PUT Error: ' . $e->getMessage(), [
                'path' => $path,
                'body' => $body,
            ]);
            throw $e;
        }
    }

    protected function delete(string $path): array
    {
        try {
            return $this->sendRequest('DELETE', $path);
        } catch (\Throwable $e) {
            Log::error('Tamara API DELETE Error: ' . $e->getMessage(), [
                'path' => $path,
            ]);
            throw $e;
        }
    }
}
