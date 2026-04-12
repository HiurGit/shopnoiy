<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitor_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('visitor_token', 80)->unique();
            $table->string('session_id', 255)->nullable()->index();
            $table->string('ip_address', 45)->nullable()->index();
            $table->string('route_name', 190)->nullable()->index();
            $table->string('page_type', 80)->nullable()->index();
            $table->string('activity_label', 190)->nullable();
            $table->string('page_title', 255)->nullable();
            $table->string('current_path', 255)->nullable()->index();
            $table->text('current_url')->nullable();
            $table->text('referrer_url')->nullable();
            $table->unsignedInteger('cart_count')->default(0);
            $table->unsignedBigInteger('cart_value')->default(0);
            $table->json('meta_json')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('first_seen_at')->nullable()->index();
            $table->timestamp('last_seen_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_sessions');
    }
};
