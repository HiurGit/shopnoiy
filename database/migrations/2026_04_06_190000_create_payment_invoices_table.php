<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_code', 80)->unique();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('customer_name', 150);
            $table->string('customer_phone', 30);
            $table->string('customer_email', 190)->nullable();
            $table->string('delivery_type', 20)->default('delivery');
            $table->unsignedBigInteger('store_id')->nullable();
            $table->string('shipping_address_text', 500)->nullable();
            $table->string('payment_method', 30)->default('vietqr');
            $table->string('invoice_status', 30)->default('pending_payment');
            $table->string('payment_status', 30)->default('unpaid');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('shipping_fee', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('transfer_content', 120)->nullable();
            $table->string('note', 500)->nullable();
            $table->json('items_json')->nullable();
            $table->json('raw_payment_json')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->dateTime('converted_at')->nullable();
            $table->timestamps();

            $table->index('order_id', 'idx_payment_invoices_order');
            $table->index('user_id', 'idx_payment_invoices_user');
            $table->index('store_id', 'idx_payment_invoices_store');
            $table->index(['invoice_status', 'payment_status'], 'idx_payment_invoices_status');
            $table->index('payment_method', 'idx_payment_invoices_method');
            $table->index('created_at', 'idx_payment_invoices_created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_invoices');
    }
};
