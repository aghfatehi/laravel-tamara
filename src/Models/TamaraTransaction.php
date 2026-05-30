<?php

namespace Aghfatehi\Tamara\Models;

use Illuminate\Database\Eloquent\Model;

class TamaraTransaction extends Model
{
    protected $fillable = [
        'tamara_order_id',
        'tamara_checkout_id',
        'order_reference_id',
        'order_number',
        'amount',
        'currency',
        'status',
        'payment_type',
        'request_payload',
        'response_payload',
        'error_message',
        'billable_id',
        'billable_type',
    ];

    protected $casts = [
        'amount' => 'decimal:3',
        'request_payload' => 'json',
        'response_payload' => 'json',
    ];

    public function billable()
    {
        return $this->morphTo();
    }
}
