<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitor_interest_events', function (Blueprint $table) {
            $table->id();
            $table->string('visitor_token', 80)->index();
            $table->string('event_type', 50)->index();
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->string('product_slug', 190)->nullable()->index();
            $table->string('product_name', 255)->nullable();
            $table->unsignedInteger('qty')->default(1);
            $table->string('page_type', 80)->nullable()->index();
            $table->string('source_route', 190)->nullable()->index();
            $table->string('ip_address', 45)->nullable()->index();
            $table->text('user_agent')->nullable();
            $table->json('meta_json')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_interest_events');
    }
};
