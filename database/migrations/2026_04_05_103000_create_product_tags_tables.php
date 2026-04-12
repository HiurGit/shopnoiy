<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 160)->unique();
            $table->integer('sort_order')->default(0);
            $table->string('status', 30)->default('active');
            $table->timestamps();
        });

        Schema::create('product_tag_map', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('tag_id');
            $table->timestamps();

            $table->unique(['product_id', 'tag_id'], 'uk_product_tag_map');
            $table->index('product_id', 'idx_product_tag_map_product');
            $table->index('tag_id', 'idx_product_tag_map_tag');

            $table->foreign('product_id', 'fk_product_tag_map_product')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();

            $table->foreign('tag_id', 'fk_product_tag_map_tag')
                ->references('id')
                ->on('product_tags')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_tag_map');
        Schema::dropIfExists('product_tags');
    }
};
