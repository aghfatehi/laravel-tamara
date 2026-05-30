<?php

namespace Aghfatehi\Tamara\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array getPaymentTypes(string $country = 'SA', string $currency = 'SAR', float $orderValue = 1, string $phone = '')
 * @method static array createCheckout(array $data)
 * @method static array getOrder(string $orderId)
 * @method static array authoriseOrder(string $orderId)
 * @method static array captureOrder(string $orderId, array $data = [])
 * @method static array cancelOrder(string $orderId, array $data = [])
 * @method static array refundOrder(string $orderId, float $amount, string $currency = 'SAR', string $comment = '')
 *
 * @see \Aghfatehi\Tamara\Services\TamaraService
 */
class Tamara extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'tamara';
    }
}
