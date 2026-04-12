<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sepay_webhook_receipts', function (Blueprint $table) {
            if (!Schema::hasColumn('sepay_webhook_receipts', 'invoice_id')) {
                $table->unsignedBigInteger('invoice_id')->nullable()->after('transaction_id');
                $table->index('invoice_id', 'idx_sepay_receipts_invoice');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sepay_webhook_receipts', function (Blueprint $table) {
            if (Schema::hasColumn('sepay_webhook_receipts', 'invoice_id')) {
                $table->dropIndex('idx_sepay_receipts_invoice');
                $table->dropColumn('invoice_id');
            }
        });
    }
};
