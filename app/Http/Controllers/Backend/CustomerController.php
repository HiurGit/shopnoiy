<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class CustomerController extends Controller
{
    private array $configFields = [
        'customer_tier_friendly_min_spent' => [
            'label' => 'Moc len rank Khach hang than thien',
            'default' => 3000000,
            'help' => 'Khach dat tong chi tieu tu muc nay tro len se vao rank Khach hang than thien.',
        ],
        'customer_tier_loyal_min_spent' => [
            'label' => 'Moc len rank Khach hang trung thanh',
            'default' => 10000000,
            'help' => 'Khach dat tong chi tieu tu muc nay tro len se vao rank Khach hang trung thanh.',
        ],
        'customer_tier_vip_min_spent' => [
            'label' => 'Moc len rank Khach hang VIP',
            'default' => 20000000,
            'help' => 'Khach dat tong chi tieu tu muc nay tro len se vao rank Khach hang VIP.',
        ],
        'customer_tier_diamond_min_spent' => [
            'label' => 'Moc len rank Khach hang Kim cuong',
            'default' => 30000000,
            'help' => 'Khach dat tong chi tieu tu muc nay tro len se vao rank Khach hang Kim cuong.',
        ],
    ];

    private function resolveCustomerTierFromSpent(float $totalSpent, array $settings = []): string
    {
        $friendlyMin = max(0, (float) ($settings['customer_tier_friendly_min_spent'] ?? $this->configFields['customer_tier_friendly_min_spent']['default']));
        $loyalMin = max($friendlyMin, (float) ($settings['customer_tier_loyal_min_spent'] ?? $this->configFields['customer_tier_loyal_min_spent']['default']));
        $vipMin = max($loyalMin, (float) ($settings['customer_tier_vip_min_spent'] ?? $this->configFields['customer_tier_vip_min_spent']['default']));
        $diamondMin = max($vipMin, (float) ($settings['customer_tier_diamond_min_spent'] ?? $this->configFields['customer_tier_diamond_min_spent']['default']));

        if ($totalSpent >= $diamondMin) {
            return 'diamond';
        }

        if ($totalSpent >= $vipMin) {
            return 'vip';
        }

        if ($totalSpent >= $loyalMin) {
            return 'loyal';
        }

        if ($totalSpent >= $friendlyMin) {
            return 'friendly';
        }

        return 'new';
    }

    private function syncAllCustomerRanks(array $settings): void
    {
        $profiles = DB::table('customer_profiles')->select('user_id', 'total_spent')->get();

        foreach ($profiles as $profile) {
            DB::table('customer_profiles')
                ->where('user_id', $profile->user_id)
                ->update([
                    'tier' => $this->resolveCustomerTierFromSpent((float) $profile->total_spent, $settings),
                    'updated_at' => now(),
                ]);
        }
    }

    public function index(): View
    {
        $customers = DB::table('users as u')
            ->leftJoin('customer_profiles as cp', 'cp.user_id', '=', 'u.id')
            ->select('u.*', 'cp.tier', 'cp.total_orders', 'cp.total_spent')
            ->where('u.role', 'customer')
            ->orderByDesc('u.id')
            ->paginate(20);

        return view('backend.customers.index', compact('customers'));
    }

    public function config(): View
    {
        $settings = DB::table('site_settings')
            ->whereIn('setting_key', array_keys($this->configFields))
            ->pluck('setting_value', 'setting_key');

        return view('backend.customers.config', [
            'settings' => $settings,
            'fields' => $this->configFields,
        ]);
    }

    public function updateConfig(Request $request): RedirectResponse
    {
        $friendlyMin = max(0, (float) $request->input('customer_tier_friendly_min_spent', $this->configFields['customer_tier_friendly_min_spent']['default']));
        $loyalMin = max($friendlyMin, (float) $request->input('customer_tier_loyal_min_spent', $this->configFields['customer_tier_loyal_min_spent']['default']));
        $vipMin = max($loyalMin, (float) $request->input('customer_tier_vip_min_spent', $this->configFields['customer_tier_vip_min_spent']['default']));
        $diamondMin = max($vipMin, (float) $request->input('customer_tier_diamond_min_spent', $this->configFields['customer_tier_diamond_min_spent']['default']));
        $normalizedSettings = [
            'customer_tier_friendly_min_spent' => $friendlyMin,
            'customer_tier_loyal_min_spent' => $loyalMin,
            'customer_tier_vip_min_spent' => $vipMin,
            'customer_tier_diamond_min_spent' => $diamondMin,
        ];

        DB::table('site_settings')->updateOrInsert(
            ['setting_key' => 'customer_tier_friendly_min_spent'],
            [
                'setting_value' => $friendlyMin,
                'setting_group' => 'customer',
                'description' => $this->configFields['customer_tier_friendly_min_spent']['label'],
                'updated_by' => 'admin',
                'updated_at' => now(),
            ]
        );

        DB::table('site_settings')->updateOrInsert(
            ['setting_key' => 'customer_tier_loyal_min_spent'],
            [
                'setting_value' => $loyalMin,
                'setting_group' => 'customer',
                'description' => $this->configFields['customer_tier_loyal_min_spent']['label'],
                'updated_by' => 'admin',
                'updated_at' => now(),
            ]
        );

        DB::table('site_settings')->updateOrInsert(
            ['setting_key' => 'customer_tier_vip_min_spent'],
            [
                'setting_value' => $vipMin,
                'setting_group' => 'customer',
                'description' => $this->configFields['customer_tier_vip_min_spent']['label'],
                'updated_by' => 'admin',
                'updated_at' => now(),
            ]
        );

        DB::table('site_settings')->updateOrInsert(
            ['setting_key' => 'customer_tier_diamond_min_spent'],
            [
                'setting_value' => $diamondMin,
                'setting_group' => 'customer',
                'description' => $this->configFields['customer_tier_diamond_min_spent']['label'],
                'updated_by' => 'admin',
                'updated_at' => now(),
            ]
        );

        $this->syncAllCustomerRanks($normalizedSettings);

        return redirect()->route('backend.customers.config')->with('success', 'Da luu cau hinh khach hang.');
    }

    public function ranking(Request $request): View
    {
        $sort = (string) $request->input('sort', 'total_spent');
        $tier = (string) $request->input('tier', 'all');
        $period = (string) $request->input('period', 'all');
        $keyword = trim((string) $request->input('q', ''));

        $allowedSorts = ['total_spent', 'total_orders', 'recent_spent', 'recent_orders'];
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'total_spent';
        }

        $allowedTiers = ['all', 'new', 'friendly', 'loyal', 'vip', 'diamond'];
        if (!in_array($tier, $allowedTiers, true)) {
            $tier = 'all';
        }

        $periodDays = match ($period) {
            '30' => 30,
            '90' => 90,
            default => null,
        };

        $recentStatsQuery = DB::table('orders')
            ->selectRaw('user_id, COUNT(*) as recent_orders, COALESCE(SUM(total_amount), 0) as recent_spent, MAX(created_at) as last_order_at')
            ->whereNotNull('user_id')
            ->when($periodDays, fn ($query) => $query->where('created_at', '>=', now()->subDays($periodDays)->startOfDay()))
            ->groupBy('user_id');

        $baseQuery = DB::table('users as u')
            ->leftJoin('customer_profiles as cp', 'cp.user_id', '=', 'u.id')
            ->leftJoinSub($recentStatsQuery, 'recent_stats', function ($join) {
                $join->on('recent_stats.user_id', '=', 'u.id');
            })
            ->selectRaw("
                u.id,
                u.full_name,
                u.email,
                u.phone,
                u.status,
                COALESCE(cp.tier, 'new') as tier,
                COALESCE(cp.total_orders, 0) as total_orders,
                COALESCE(cp.total_spent, 0) as total_spent,
                COALESCE(recent_stats.recent_orders, 0) as recent_orders,
                COALESCE(recent_stats.recent_spent, 0) as recent_spent,
                recent_stats.last_order_at
            ")
            ->where('u.role', 'customer')
            ->when($tier !== 'all', fn ($query) => $query->where('cp.tier', $tier))
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($inner) use ($keyword) {
                    $inner->where('u.full_name', 'like', '%' . $keyword . '%')
                        ->orWhere('u.email', 'like', '%' . $keyword . '%')
                        ->orWhere('u.phone', 'like', '%' . $keyword . '%');
                });
            });

        $sortColumn = match ($sort) {
            'total_orders' => 'total_orders',
            'recent_spent' => 'recent_spent',
            'recent_orders' => 'recent_orders',
            default => 'total_spent',
        };

        $leaderboardQuery = clone $baseQuery;
        $leaderboardQuery->orderByDesc($sortColumn)->orderByDesc('total_spent')->orderBy('full_name');

        $podiumCustomers = (clone $leaderboardQuery)
            ->limit(3)
            ->get();

        $leaderboard = $leaderboardQuery
            ->paginate(12)
            ->withQueryString();

        $summaryRows = (clone $baseQuery)->get();
        $summary = [
            'customers_count' => $summaryRows->count(),
            'diamond_count' => $summaryRows->where('tier', 'diamond')->count(),
            'vip_count' => $summaryRows->where('tier', 'vip')->count(),
            'loyal_count' => $summaryRows->where('tier', 'loyal')->count(),
            'friendly_count' => $summaryRows->where('tier', 'friendly')->count(),
            'new_count' => $summaryRows->where('tier', 'new')->count(),
            'total_spent' => (float) $summaryRows->sum('total_spent'),
            'total_orders' => (int) $summaryRows->sum('total_orders'),
            'recent_spent' => (float) $summaryRows->sum('recent_spent'),
            'recent_orders' => (int) $summaryRows->sum('recent_orders'),
            'top_value' => (float) $summaryRows->max($sortColumn),
        ];

        return view('backend.customers.ranking', compact(
            'leaderboard',
            'podiumCustomers',
            'summary',
            'sort',
            'tier',
            'period',
            'keyword'
        ));
    }

    public function create(): View
    {
        return view('backend.customers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = User::create([
            'full_name' => $request->input('full_name', 'Khách hàng mới'),
            'email' => $request->input('email', 'customer' . time() . '@shopnoiy.com'),
            'phone' => $request->input('phone'),
            'password_hash' => Hash::make((string) $request->input('password', '12345678')),
            'role' => 'customer',
            'status' => $request->input('status', 'active'),
            'last_login_at' => null,
        ]);

        DB::table('customer_profiles')->insert([
            'user_id' => $user->id,
            'tier' => $request->input('tier', 'new'),
            'total_orders' => (int) $request->input('total_orders', 0),
            'total_spent' => (float) $request->input('total_spent', 0),
            'marketing_opt_in' => $request->boolean('marketing_opt_in'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('backend.customers')->with('success', 'Đã tạo khách hàng.');
    }

    public function show(User $customer): View
    {
        $this->ensureCustomer($customer);
        $profile = DB::table('customer_profiles')->where('user_id', $customer->id)->first();
        $orders = Order::query()->where('user_id', $customer->id)->orderByDesc('id')->limit(20)->get();

        return view('backend.customers.show', compact('customer', 'profile', 'orders'));
    }

    public function edit(User $customer): View
    {
        $this->ensureCustomer($customer);
        $profile = DB::table('customer_profiles')->where('user_id', $customer->id)->first();

        return view('backend.customers.edit', compact('customer', 'profile'));
    }

    public function update(Request $request, User $customer): RedirectResponse
    {
        $this->ensureCustomer($customer);
        $data = [
            'full_name' => $request->input('full_name', $customer->full_name),
            'email' => $request->input('email', $customer->email),
            'phone' => $request->input('phone', $customer->phone),
            'status' => $request->input('status', $customer->status),
        ];

        if ($request->filled('password')) {
            $data['password_hash'] = Hash::make((string) $request->input('password'));
        }

        $customer->update($data);

        DB::table('customer_profiles')->updateOrInsert(
            ['user_id' => $customer->id],
            [
                'tier' => $request->input('tier', 'new'),
                'total_orders' => (int) $request->input('total_orders', 0),
                'total_spent' => (float) $request->input('total_spent', 0),
                'marketing_opt_in' => $request->boolean('marketing_opt_in'),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return redirect()->route('backend.customers')->with('success', 'Đã cập nhật khách hàng.');
    }

    public function destroy(Request $request, User $customer): RedirectResponse|JsonResponse
    {
        $this->ensureCustomer($customer);
        DB::table('customer_profiles')->where('user_id', $customer->id)->delete();
        DB::table('customer_addresses')->where('user_id', $customer->id)->delete();
        DB::table('wishlists')->where('user_id', $customer->id)->delete();
        DB::table('orders')->where('user_id', $customer->id)->update(['user_id' => null]);
        DB::table('carts')->where('user_id', $customer->id)->update(['user_id' => null]);
        $customer->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa khách hàng.',
                'customer_id' => $customer->id,
            ]);
        }

        return redirect()->route('backend.customers')->with('success', 'Đã xóa khách hàng.');
    }
    private function ensureCustomer(User $customer): void
    {
        abort_unless($customer->role === 'customer', 404);
    }
}
