<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_targets', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->string('slug', 80)->unique();
            $table->integer('sort_order')->default(0);
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->index(['status', 'sort_order'], 'idx_product_targets_status_sort');
        });

        $now = now();
        DB::table('product_targets')->insert([
            ['name' => 'Nữ', 'slug' => 'female', 'sort_order' => 10, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Nam', 'slug' => 'male', 'sort_order' => 20, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Em bé', 'slug' => 'baby', 'sort_order' => 30, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Unisex', 'slug' => 'unisex', 'sort_order' => 40, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
        ]);

        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('product_target_id')->nullable()->after('target_gender');
            $table->index('product_target_id', 'idx_products_target_id');
        });

        $defaultTargetId = DB::table('product_targets')->where('slug', 'female')->value('id');
        $targetBySlug = DB::table('product_targets')->pluck('id', 'slug');

        if (Schema::hasColumn('products', 'target_gender')) {
            foreach ($targetBySlug as $slug => $id) {
                DB::table('products')
                    ->where('target_gender', $slug)
                    ->update(['product_target_id' => $id]);
            }
        }

        DB::table('products')
            ->whereNull('product_target_id')
            ->update(['product_target_id' => $defaultTargetId]);

        Schema::table('products', function (Blueprint $table) {
            $table->foreign('product_target_id', 'fk_products_product_target')
                ->references('id')
                ->on('product_targets')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign('fk_products_product_target');
            $table->dropIndex('idx_products_target_id');
            $table->dropColumn('product_target_id');
        });

        Schema::dropIfExists('product_targets');
    }
};
