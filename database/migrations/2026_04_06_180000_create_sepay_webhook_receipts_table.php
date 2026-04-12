<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sepay_webhook_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id', 190)->unique();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('order_payment_id')->nullable();
            $table->string('gateway', 120)->nullable();
            $table->string('account_number', 60)->nullable();
            $table->string('reference_code', 190)->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('transfer_type', 20)->nullable();
            $table->string('content', 500)->nullable();
            $table->json('payload_json')->nullable();
            $table->timestamp('processed_at')->useCurrent();

            $table->index('order_id', 'idx_sepay_receipts_order');
            $table->index('order_payment_id', 'idx_sepay_receipts_order_payment');
            $table->index('reference_code', 'idx_sepay_receipts_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sepay_webhook_receipts');
    }
};
