<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = now()->startOfDay();

        $kpis = [
            'total_orders' => Order::query()->count(),
            'today_orders' => Order::query()->where('created_at', '>=', $today)->count(),
            'today_revenue' => (float) Order::query()->where('created_at', '>=', $today)->sum('total_amount'),
            'today_paid_revenue' => (float) Order::query()
                ->where('created_at', '>=', $today)
                ->where('payment_status', 'paid')
                ->sum('total_amount'),
            'pending_orders' => Order::query()->where('order_status', 'pending_verification')->count(),
            'total_customers' => User::query()->where('role', 'customer')->count(),
            'total_products' => Product::query()->count(),
            'low_stock_products' => Product::query()->where('stock_qty', '<=', 5)->count(),
        ];

        $recentOrders = Order::query()
            ->select(['id', 'order_code', 'customer_name', 'order_status', 'payment_method', 'payment_status', 'total_amount', 'created_at'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $dailyRevenueRaw = Order::query()
            ->selectRaw('DATE(created_at) as date_key, COALESCE(SUM(total_amount), 0) as revenue, COUNT(*) as orders_count')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date_key')
            ->get()
            ->keyBy('date_key');

        $dailyRevenue = collect(range(0, 6))->map(function (int $offset) use ($dailyRevenueRaw) {
            $date = now()->subDays(6 - $offset)->toDateString();
            $found = $dailyRevenueRaw->get($date);

            return [
                'date' => $date,
                'revenue' => (float) ($found->revenue ?? 0),
                'orders_count' => (int) ($found->orders_count ?? 0),
            ];
        });

        return view('backend.dashboard.index', compact('kpis', 'recentOrders', 'dailyRevenue'));
    }
}
