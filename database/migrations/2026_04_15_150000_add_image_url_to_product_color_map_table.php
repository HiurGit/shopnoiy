<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_color_map', function (Blueprint $table): void {
            if (!Schema::hasColumn('product_color_map', 'image_url')) {
                $table->string('image_url', 500)->nullable()->after('color_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_color_map', function (Blueprint $table): void {
            if (Schema::hasColumn('product_color_map', 'image_url')) {
                $table->dropColumn('image_url');
            }
        });
    }
};
