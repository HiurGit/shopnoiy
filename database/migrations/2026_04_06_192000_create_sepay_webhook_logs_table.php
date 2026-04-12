<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sepay_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('transaction_id', 190)->nullable();
            $table->integer('http_status')->default(200);
            $table->string('status', 40)->default('received');
            $table->string('message', 255)->nullable();
            $table->string('auth_type', 40)->nullable();
            $table->string('secret_preview', 80)->nullable();
            $table->json('headers_json')->nullable();
            $table->json('payload_json')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('invoice_id', 'idx_sepay_logs_invoice');
            $table->index('order_id', 'idx_sepay_logs_order');
            $table->index('transaction_id', 'idx_sepay_logs_transaction');
            $table->index(['status', 'created_at'], 'idx_sepay_logs_status_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sepay_webhook_logs');
    }
};
