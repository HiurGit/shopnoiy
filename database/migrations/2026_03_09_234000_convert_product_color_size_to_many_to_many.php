<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('product_color_map')) {
            Schema::create('product_color_map', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('color_id');
                $table->timestamps();

                $table->unique(['product_id', 'color_id'], 'uk_product_color_map');
                $table->index('product_id', 'idx_product_color_map_product');
                $table->index('color_id', 'idx_product_color_map_color');

                $table->foreign('product_id', 'fk_product_color_map_product')
                    ->references('id')
                    ->on('products')
                    ->cascadeOnDelete();

                $table->foreign('color_id', 'fk_product_color_map_color')
                    ->references('id')
                    ->on('product_colors')
                    ->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('product_size_map')) {
            Schema::create('product_size_map', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('size_id');
                $table->timestamps();

                $table->unique(['product_id', 'size_id'], 'uk_product_size_map');
                $table->index('product_id', 'idx_product_size_map_product');
                $table->index('size_id', 'idx_product_size_map_size');

                $table->foreign('product_id', 'fk_product_size_map_product')
                    ->references('id')
                    ->on('products')
                    ->cascadeOnDelete();

                $table->foreign('size_id', 'fk_product_size_map_size')
                    ->references('id')
                    ->on('product_sizes')
                    ->cascadeOnDelete();
            });
        }

        if (Schema::hasColumn('products', 'color_id')) {
            DB::table('products')
                ->select('id', 'color_id')
                ->whereNotNull('color_id')
                ->orderBy('id')
                ->get()
                ->each(function ($row): void {
                    $exists = DB::table('product_color_map')
                        ->where('product_id', $row->id)
                        ->where('color_id', $row->color_id)
                        ->exists();

                    if (!$exists) {
                        DB::table('product_color_map')->insert([
                            'product_id' => $row->id,
                            'color_id' => $row->color_id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                });

            try {
                DB::statement('ALTER TABLE products DROP FOREIGN KEY fk_products_color_id');
            } catch (\Throwable $e) {
            }

            try {
                DB::statement('DROP INDEX idx_products_color_id ON products');
            } catch (\Throwable $e) {
            }

            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('color_id');
            });
        }

        if (Schema::hasColumn('products', 'size_id')) {
            DB::table('products')
                ->select('id', 'size_id')
                ->whereNotNull('size_id')
                ->orderBy('id')
                ->get()
                ->each(function ($row): void {
                    $exists = DB::table('product_size_map')
                        ->where('product_id', $row->id)
                        ->where('size_id', $row->size_id)
                        ->exists();

                    if (!$exists) {
                        DB::table('product_size_map')->insert([
                            'product_id' => $row->id,
                            'size_id' => $row->size_id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                });

            try {
                DB::statement('ALTER TABLE products DROP FOREIGN KEY fk_products_size_id');
            } catch (\Throwable $e) {
            }

            try {
                DB::statement('DROP INDEX idx_products_size_id ON products');
            } catch (\Throwable $e) {
            }

            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('size_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('products', 'color_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedBigInteger('color_id')->nullable()->after('category_id');
                $table->index('color_id', 'idx_products_color_id');
                $table->foreign('color_id', 'fk_products_color_id')
                    ->references('id')
                    ->on('product_colors')
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('products', 'size_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedBigInteger('size_id')->nullable()->after('category_id');
                $table->index('size_id', 'idx_products_size_id');
                $table->foreign('size_id', 'fk_products_size_id')
                    ->references('id')
                    ->on('product_sizes')
                    ->nullOnDelete();
            });
        }

        DB::table('products')->select('id')->orderBy('id')->get()->each(function ($p): void {
            $colorId = DB::table('product_color_map')->where('product_id', $p->id)->value('color_id');
            $sizeId = DB::table('product_size_map')->where('product_id', $p->id)->value('size_id');

            DB::table('products')->where('id', $p->id)->update([
                'color_id' => $colorId,
                'size_id' => $sizeId,
            ]);
        });

        Schema::dropIfExists('product_size_map');
        Schema::dropIfExists('product_color_map');
    }
};
