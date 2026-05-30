<?php

namespace Aghfatehi\Tamara\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class TamaraService
{
    protected Client $client;

    protected array $config;

    public function __construct()
    {
        $this->config = config('tamara');
    }

    protected function client(): Client
    {
        if (!isset($this->client)) {
            $this->client = new Client([
                'base_uri' => $this->baseUrl(),
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->config['api_token'],
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 30,
            ]);
        }

        return $this->client;
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

    protected function get(string $path, array $query = []): array
    {
        try {
            $response = $this->client()->get($path, ['query' => $query]);
            return json_decode($response->getBody()->getContents(), true) ?? [];
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
            $response = $this->client()->post($path, ['json' => $body]);
            return json_decode($response->getBody()->getContents(), true) ?? [];
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
            $response = $this->client()->put($path, ['json' => $body]);
            return json_decode($response->getBody()->getContents(), true) ?? [];
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
            $response = $this->client()->delete($path);
            return json_decode($response->getBody()->getContents(), true) ?? [];
        } catch (\Throwable $e) {
            Log::error('Tamara API DELETE Error: ' . $e->getMessage(), [
                'path' => $path,
            ]);
            throw $e;
        }
    }
}
