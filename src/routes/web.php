<?php

use Aghfatehi\Tamara\Controllers\TamaraController;
use Illuminate\Support\Facades\Route;

$prefix = config('tamara.routes.prefix', 'tamara');
$middleware = config('tamara.routes.middleware', ['web']);

Route::middleware($middleware)->prefix($prefix)->group(function () {
    Route::get('/payment/types', [TamaraController::class, 'getPaymentTypes'])
        ->name('tamara.payment.types');

    Route::post('/pay', [TamaraController::class, 'pay'])
        ->name('tamara.pay');

    Route::any('/callback', [TamaraController::class, 'callback'])
        ->name('tamara.callback');

    Route::get('/cancel', [TamaraController::class, 'cancel'])
        ->name('tamara.cancel');

    Route::get('/failure', [TamaraController::class, 'failure'])
        ->name('tamara.failure');

    Route::post('/webhook', [TamaraController::class, 'webhook'])
        ->name('tamara.webhook');

    Route::post('/authorise', [TamaraController::class, 'authorise'])
        ->name('tamara.authorise');
});
