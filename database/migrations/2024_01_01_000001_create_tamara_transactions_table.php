<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tamara_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('tamara_order_id')->nullable()->index();
            $table->string('tamara_checkout_id')->nullable()->index();
            $table->string('order_reference_id')->nullable();
            $table->string('order_number')->nullable();
            $table->decimal('amount', 20, 3)->default(0);
            $table->string('currency', 3)->default('SAR');
            $table->string('status')->nullable()->index();
            $table->string('payment_type')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->string('error_message')->nullable();
            $table->nullableMorphs('billable');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tamara_transactions');
    }
};
