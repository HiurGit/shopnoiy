<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('customer_tracking_token', 24)->nullable()->unique()->after('order_code');
        });

        $orders = DB::table('orders')->select(['id', 'customer_tracking_token'])->orderBy('id')->get();

        foreach ($orders as $order) {
            if (!empty($order->customer_tracking_token)) {
                continue;
            }

            do {
                $token = strtoupper(Str::random(10));
            } while (DB::table('orders')->where('customer_tracking_token', $token)->exists());

            DB::table('orders')
                ->where('id', $order->id)
                ->update(['customer_tracking_token' => $token]);
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['customer_tracking_token']);
            $table->dropColumn('customer_tracking_token');
        });
    }
};
