<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promo_tickers', function (Blueprint $table) {
            $table->string('placement', 50)->default('topbar')->after('name');
            $table->index(['placement', 'status'], 'idx_promo_tickers_placement_status');
        });
    }

    public function down(): void
    {
        Schema::table('promo_tickers', function (Blueprint $table) {
            $table->dropIndex('idx_promo_tickers_placement_status');
            $table->dropColumn('placement');
        });
    }
};
