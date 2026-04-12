<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use App\Services\OrderEmailService;
use App\Services\TelegramOrderNotificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderEmailService $orderEmailService,
        private readonly TelegramOrderNotificationService $telegramOrderNotificationService
    ) {
    }

    private function normalizePhone(?string $phone): ?string
    {
        $normalized = preg_replace('/\D+/', '', (string) $phone) ?: '';

        return $normalized !== '' ? $normalized : null;
    }

    private function normalizeEmail(?string $email): ?string
    {
        $normalized = trim(mb_strtolower((string) $email));

        return $normalized !== '' ? $normalized : null;
    }

    private function resolveCustomerTier(float $totalSpent): string
    {
        $settings = DB::table('site_settings')
            ->whereIn('setting_key', [
                'customer_tier_friendly_min_spent',
                'customer_tier_loyal_min_spent',
                'customer_tier_vip_min_spent',
                'customer_tier_diamond_min_spent',
            ])
            ->pluck('setting_value', 'setting_key');

        $friendlyMin = max(0, (float) ($settings['customer_tier_friendly_min_spent'] ?? 3000000));
        $loyalMin = max($friendlyMin, (float) ($settings['customer_tier_loyal_min_spent'] ?? 10000000));
        $vipMin = max($loyalMin, (float) ($settings['customer_tier_vip_min_spent'] ?? 20000000));
        $diamondMin = max($vipMin, (float) ($settings['customer_tier_diamond_min_spent'] ?? 30000000));

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

    private function syncCustomerProfileByUserId(?int $userId): void
    {
        if (!$userId) {
            return;
        }

        $customer = User::query()->whereKey($userId)->where('role', 'customer')->first();
        if (!$customer) {
            return;
        }

        $verifiedOrdersQuery = Order::query()
            ->where('user_id', $customer->id)
            ->where('order_status', 'verified');

        $totalSpent = (float) $verifiedOrdersQuery->sum('total_amount');
        $totalOrders = (int) $verifiedOrdersQuery->count();
        $latestVerifiedOrder = (clone $verifiedOrdersQuery)->orderByDesc('verified_at')->orderByDesc('id')->first();

        DB::table('customer_profiles')->updateOrInsert(
            ['user_id' => $customer->id],
            [
                'tier' => $this->resolveCustomerTier($totalSpent),
                'total_orders' => $totalOrders,
                'total_spent' => $totalSpent,
                'marketing_opt_in' => false,
                'note' => $latestVerifiedOrder ? 'Cap nhat tu don hang da xac minh gan nhat: ' . $latestVerifiedOrder->order_code : null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    private function ensureOrderCustomerLinked(Order $order): ?User
    {
        $phone = $this->normalizePhone($order->customer_phone);
        $email = $this->normalizeEmail($order->customer_email);
        $customerName = trim((string) $order->customer_name) ?: 'Khach hang';
        $customer = null;

        if (!empty($order->user_id)) {
            $customer = User::query()->whereKey($order->user_id)->where('role', 'customer')->first();
        }

        if (!$customer && $phone) {
            $customer = User::query()->where('role', 'customer')->where('phone', $phone)->first();
        }

        if (!$customer && $email) {
            $customer = User::query()->where('role', 'customer')->whereRaw('LOWER(email) = ?', [$email])->first();
        }

        if (!$customer) {
            $email = $email ?: ('guest-order-' . $order->id . '@shopnoiy.local');
            $existingEmailOwner = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
            if ($existingEmailOwner) {
                $email = 'guest-order-' . $order->id . '+' . time() . '@shopnoiy.local';
            }

            $customer = User::query()->create([
                'full_name' => $customerName,
                'email' => $email,
                'phone' => $phone,
                'password_hash' => Hash::make('12345678'),
                'role' => 'customer',
                'status' => 'active',
                'last_login_at' => null,
            ]);
        } else {
            $updates = [];

            if (trim((string) $customer->full_name) === '' && $customerName !== '') {
                $updates['full_name'] = $customerName;
            }

            if (!$customer->phone && $phone) {
                $updates['phone'] = $phone;
            }

            if ($updates !== []) {
                $customer->update($updates);
            }
        }

        if ((int) $order->user_id !== (int) $customer->id) {
            $order->update(['user_id' => $customer->id]);
        }

        $this->syncCustomerProfileByUserId((int) $customer->id);

        return $customer;
    }

    private function parseVerifiedAt(Request $request): ?Carbon
    {
        if (! $request->filled('verified_at')) {
            return null;
        }

        return Carbon::parse($request->input('verified_at'));
    }

    private function resolveOrderStatus(Request $request, string $fallback = 'pending_verification'): string
    {
        return (string) $request->input('order_status', $fallback);
    }

    private function resolveVerifiedAt(Request $request, ?Order $order = null): ?Carbon
    {
        if ($request->filled('verified_at')) {
            return $this->parseVerifiedAt($request);
        }

        $status = $this->resolveOrderStatus($request, $order?->order_status ?? 'pending_verification');

        if ($status === 'verified') {
            return $order?->verified_at ?: now();
        }

        return null;
    }

    private function resolveImageUrl(?string $path): ?string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    }

    public function index(): View
    {
        $orders = Order::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return view('backend.orders.index', compact('orders'));
    }

    public function create(): View
    {
        $users = User::query()->orderBy('full_name')->get();
        $stores = Store::query()->orderBy('name')->get();

        return view('backend.orders.create', compact('users', 'stores'));
    }

    public function store(Request $request): RedirectResponse
    {
        $id = ((int) (DB::table('orders')->max('id') ?? 1000)) + 1;
        $orderCode = $request->input('order_code') ?: 'ODR-' . now()->format('Ymd') . '-' . $id;
        $orderStatus = $this->resolveOrderStatus($request);

        $order = Order::create([
            'order_code' => $orderCode,
            'customer_tracking_token' => Order::generateCustomerTrackingToken(),
            'user_id' => $request->filled('user_id') ? (int) $request->input('user_id') : null,
            'customer_name' => $request->input('customer_name', 'Khach le'),
            'customer_phone' => $request->input('customer_phone', ''),
            'customer_email' => $request->input('customer_email'),
            'delivery_type' => $request->input('delivery_type', 'delivery'),
            'store_id' => $request->filled('store_id') ? (int) $request->input('store_id') : null,
            'shipping_address_text' => $request->input('shipping_address_text'),
            'payment_method' => $request->input('payment_method', 'cod'),
            'order_status' => $orderStatus,
            'verified_at' => $this->resolveVerifiedAt($request),
            'payment_status' => $request->input('payment_status', 'unpaid'),
            'subtotal' => (float) $request->input('subtotal', 0),
            'discount_amount' => (float) $request->input('discount_amount', 0),
            'promotion_id' => $request->filled('promotion_id') ? (int) $request->input('promotion_id') : null,
            'coupon_code' => $request->input('coupon_code'),
            'shipping_fee' => (float) $request->input('shipping_fee', 0),
            'total_amount' => (float) $request->input('total_amount', 0),
            'note' => $request->input('note'),
        ]);

        $this->telegramOrderNotificationService->notifyNewOrder($order);

        return redirect()->route('backend.orders')->with('success', 'Da tao don hang.');
    }

    public function show(Order $order): View
    {
        $items = DB::table('order_items')->where('order_id', $order->id)->orderBy('id')->get();
        $payments = DB::table('order_payments')->where('order_id', $order->id)->orderByDesc('id')->get();
        $shipments = DB::table('order_shipments')->where('order_id', $order->id)->orderByDesc('id')->get();
        $linkedCustomer = null;
        $customerProfile = null;
        $selectedStore = null;
        if (! empty($order->store_id)) {
            $selectedStore = DB::table('stores')->where('id', $order->store_id)->first();
        }

        if (!empty($order->user_id)) {
            $linkedCustomer = User::query()->whereKey($order->user_id)->where('role', 'customer')->first();
            if ($linkedCustomer) {
                $customerProfile = DB::table('customer_profiles')->where('user_id', $linkedCustomer->id)->first();
            }
        }

        $productIds = $items->pluck('product_id')->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();
        $imageMap = [];

        if ($productIds !== []) {
            $imageRows = DB::table('product_images')
                ->whereIn('product_id', $productIds)
                ->orderByDesc('is_primary')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            foreach ($imageRows as $imageRow) {
                $productId = (int) $imageRow->product_id;
                if (!array_key_exists($productId, $imageMap)) {
                    $imageMap[$productId] = $this->resolveImageUrl($imageRow->image_url);
                }
            }
        }

        $items = $items->map(function ($item) use ($imageMap) {
            $item->preview_image_url = $imageMap[(int) $item->product_id] ?? null;

            return $item;
        });

        return view('backend.orders.show', compact('order', 'items', 'payments', 'shipments', 'selectedStore', 'linkedCustomer', 'customerProfile'));
    }

    public function edit(Order $order): View
    {
        $users = User::query()->orderBy('full_name')->get();
        $stores = Store::query()->orderBy('name')->get();

        return view('backend.orders.edit', compact('order', 'users', 'stores'));
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $originalUserId = $order->user_id ? (int) $order->user_id : null;
        $originalStatus = (string) $order->order_status;
        $orderStatus = $this->resolveOrderStatus($request, $order->order_status);

        $order->update([
            'order_code' => $request->input('order_code', $order->order_code),
            'user_id' => $request->filled('user_id') ? (int) $request->input('user_id') : null,
            'customer_name' => $request->input('customer_name', $order->customer_name),
            'customer_phone' => $request->input('customer_phone', $order->customer_phone),
            'customer_email' => $request->input('customer_email'),
            'delivery_type' => $request->input('delivery_type', $order->delivery_type),
            'store_id' => $request->filled('store_id') ? (int) $request->input('store_id') : null,
            'shipping_address_text' => $request->input('shipping_address_text'),
            'payment_method' => $request->input('payment_method', $order->payment_method),
            'order_status' => $orderStatus,
            'verified_at' => $this->resolveVerifiedAt($request, $order),
            'payment_status' => $request->input('payment_status', $order->payment_status),
            'subtotal' => (float) $request->input('subtotal', $order->subtotal),
            'discount_amount' => (float) $request->input('discount_amount', $order->discount_amount),
            'promotion_id' => $request->filled('promotion_id') ? (int) $request->input('promotion_id') : null,
            'coupon_code' => $request->input('coupon_code'),
            'shipping_fee' => (float) $request->input('shipping_fee', $order->shipping_fee),
            'total_amount' => (float) $request->input('total_amount', $order->total_amount),
            'note' => $request->input('note'),
        ]);

        $order = $order->fresh();

        if ($orderStatus === 'verified') {
            $this->ensureOrderCustomerLinked($order);
            $order = $order->fresh();
        }

        if ($originalStatus !== 'verified' && $orderStatus === 'verified') {
            $this->orderEmailService->sendOrderVerifiedEmail($order);
        }

        $currentUserId = $order->user_id ? (int) $order->user_id : null;
        foreach (array_values(array_unique(array_filter([$originalUserId, $currentUserId]))) as $userId) {
            $this->syncCustomerProfileByUserId((int) $userId);
        }

        return redirect()->route('backend.orders')->with('success', 'Da cap nhat don hang.');
    }

    public function markVerified(Request $request, Order $order): RedirectResponse
    {
        $shouldSendVerifiedEmail = false;

        if ($order->order_status !== 'verified') {
            DB::transaction(function () use ($order): void {
                DB::table('order_status_logs')->insert([
                    'order_id' => $order->id,
                    'from_status' => $order->order_status,
                    'to_status' => 'verified',
                    'changed_by' => 'admin',
                    'note' => 'Da xac minh don hang tu danh sach',
                    'created_at' => now(),
                ]);

                $order->update([
                    'order_status' => 'verified',
                    'verified_at' => now(),
                ]);

                $this->ensureOrderCustomerLinked($order->fresh());
            });

            $shouldSendVerifiedEmail = true;
        }

        $order->ensureCustomerTrackingToken();

        if ($shouldSendVerifiedEmail) {
            $this->orderEmailService->sendOrderVerifiedEmail($order);
        }

        $redirectTo = (string) $request->input('redirect_to', '');

        if ($redirectTo !== '' && str_starts_with($redirectTo, url('/'))) {
            return redirect()->to($redirectTo)->with('success', 'Da xac minh don hang.');
        }

        return redirect()->route('backend.orders')->with('success', 'Da xac minh don hang.');
    }

    public function destroy(Request $request, Order $order): RedirectResponse|JsonResponse
    {
        $linkedUserId = $order->user_id ? (int) $order->user_id : null;

        DB::transaction(function () use ($order): void {
            DB::table('order_items')->where('order_id', $order->id)->delete();
            DB::table('order_status_logs')->where('order_id', $order->id)->delete();
            DB::table('order_payments')->where('order_id', $order->id)->delete();
            DB::table('order_shipments')->where('order_id', $order->id)->delete();
            DB::table('coupon_usages')->where('order_id', $order->id)->delete();
            $order->delete();
        });

        $this->syncCustomerProfileByUserId($linkedUserId);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Da xoa don hang.',
                'order_id' => $order->id,
            ]);
        }

        return redirect()->route('backend.orders')->with('success', 'Da xoa don hang.');
    }
}
