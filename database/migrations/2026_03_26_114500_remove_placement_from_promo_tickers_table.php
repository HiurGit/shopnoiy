<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('promo_tickers', 'placement')) {
            return;
        }

        Schema::table('promo_tickers', function (Blueprint $table) {
            try {
                $table->dropIndex('idx_promo_tickers_placement_status');
            } catch (\Throwable $exception) {
                // Ignore when the index is already absent.
            }

            $table->dropColumn('placement');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('promo_tickers', 'placement')) {
            return;
        }

        Schema::table('promo_tickers', function (Blueprint $table) {
            $table->string('placement', 50)->default('topbar')->after('name');
            $table->index(['placement', 'status'], 'idx_promo_tickers_placement_status');
        });
    }
};
