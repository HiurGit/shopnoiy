<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')
            ->whereNotNull('short_description')
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '');
            })
            ->update([
                'description' => DB::raw('short_description'),
            ]);

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('short_description');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('short_description', 255)->nullable()->after('brand');
        });
    }
};
