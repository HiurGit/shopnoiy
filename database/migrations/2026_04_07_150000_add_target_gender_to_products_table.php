<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('target_gender', 20)->default('female')->after('brand');
        });

        DB::table('products')->update([
            'target_gender' => DB::raw("COALESCE(target_gender, 'female')"),
        ]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('target_gender');
        });
    }
};
