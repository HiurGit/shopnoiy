<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\FooterLink;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductTarget;
use App\Models\PromoTicker;
use App\Models\Store;
use App\Models\User;
use App\Notifications\FrontendResetPasswordNotification;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use App\Services\OrderEmailService;
use App\Services\SePayWebhookService;
use App\Services\TelegramOrderNotificationService;
use App\Support\FrontendProductSearch;
use App\Support\UploadPath;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function __construct(
        private readonly OrderEmailService $orderEmailService,
        private readonly TelegramOrderNotificationService $telegramOrderNotificationService
    ) {
    }

    public function showCustomerLoginForm(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()?->role === 'customer') {
            return redirect()->route('frontend.home');
        }

        return $this->frontendView('auth.login');
    }

    public function customerLogin(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'Vui lòng nhập số điện thoại hoặc email.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->only([
                    'login',
                    'remember',
                ]));
        }

        $credentials = $validator->validated();

        $loginInput = trim((string) $credentials['login']);
        $normalizedPhone = $this->normalizePhone($loginInput);
        $normalizedEmail = $this->normalizeEmail($loginInput);

        $customerQuery = User::query()
            ->where('role', 'customer')
            ->where('status', 'active');

        $customer = $customerQuery
            ->where(function ($query) use ($normalizedPhone, $normalizedEmail): void {
                if ($normalizedPhone) {
                    $query->orWhere('phone', $normalizedPhone);
                }

                if ($normalizedEmail) {
                    $query->orWhereRaw('LOWER(email) = ?', [$normalizedEmail]);
                }
            })
            ->first();

        if (!$customer || !Hash::check((string) $credentials['password'], (string) $customer->getAuthPassword())) {
            return back()
                ->withInput($request->only([
                    'login',
                    'remember',
                ]))
                ->withErrors([
                    'login' => 'Thông tin đăng nhập hoặc mật khẩu chưa đúng.',
                ]);
        }

        Auth::login($customer, $request->boolean('remember'));
        $request->session()->regenerate();

        $customer->forceFill([
            'last_login_at' => now(),
        ])->save();

        return redirect()
            ->intended(route('frontend.home'))
            ->with('success', 'Đăng nhập thành công.')
            ->with('frontend_saved_login', $loginInput);
    }

    public function showCustomerRegisterForm(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()?->role === 'customer') {
            return redirect()->route('frontend.home');
        }

        return $this->frontendView('auth.register');
    }

    public function customerRegister(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu chưa khớp.',
        ]);

        $normalizedPhone = $this->normalizePhone((string) $request->input('phone'));
        $normalizedEmail = $this->normalizeEmail((string) $request->input('email'));

        $validator->after(function ($validator) use ($normalizedPhone, $normalizedEmail): void {
            if (!$normalizedPhone || !preg_match('/^0\d{9}$/', $normalizedPhone)) {
                $validator->errors()->add('phone', 'Số điện thoại phải đúng định dạng Việt Nam gồm 10 số.');
            }

            if ($normalizedPhone && User::query()->where('phone', $normalizedPhone)->exists()) {
                $validator->errors()->add('phone', 'Số điện thoại này đã được sử dụng.');
            }

            if ($normalizedEmail && User::query()->whereRaw('LOWER(email) = ?', [$normalizedEmail])->exists()) {
                $validator->errors()->add('email', 'Email này đã được sử dụng.');
            }
        });

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->only([
                    'phone',
                    'email',
                ]));
        }

        $customer = User::query()->create([
            'full_name' => 'Khách hàng mới',
            'email' => $normalizedEmail,
            'phone' => $normalizedPhone,
            'password_hash' => (string) $request->input('password'),
            'role' => 'customer',
            'status' => 'active',
            'last_login_at' => now(),
        ]);

        $this->syncCustomerProfileByUserId((int) $customer->id);

        $prefilledLogin = $normalizedPhone ?: $normalizedEmail;
        $plainPassword = (string) $request->input('password');

        return redirect()
            ->route('frontend.login')
            ->withInput([
                'login' => $prefilledLogin,
            ])
            ->with('frontend_prefill_password', $plainPassword)
            ->with('frontend_autocheck_remember', true)
            ->with('success', 'Tạo tài khoản thành công.');
    }

    public function showCustomerForgotPasswordForm(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()?->role === 'customer') {
            return redirect()->route('frontend.home');
        }

        return $this->frontendView('auth.forgot-password');
    }

    public function customerForgotPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'login' => ['required', 'string', 'max:255'],
        ], [
            'login.required' => 'Vui long nhap so dien thoai hoac email.',
        ]);

        $login = trim((string) $validated['login']);
        $normalizedPhone = $this->normalizePhone($login);
        $normalizedEmail = $this->normalizeEmail($login);

        $customer = User::query()
            ->where('role', 'customer')
            ->where('status', 'active')
            ->where(function ($query) use ($normalizedPhone, $normalizedEmail): void {
                if ($normalizedPhone) {
                    $query->orWhere('phone', $normalizedPhone);
                }

                if ($normalizedEmail) {
                    $query->orWhereRaw('LOWER(email) = ?', [$normalizedEmail]);
                }
            })
            ->first();

        if (!$customer || empty($customer->email)) {
            return back()->with('success', 'Nếu thông tin hợp lệ, hệ thống đã gửi email đặt lại mật khẩu. Vui lòng kiểm tra hộp thư của bạn.');
        }

        $status = Password::broker('users')->sendResetLink(
            ['email' => $this->normalizeEmail((string) $customer->email)],
            function (User $user, string $token): void {
                $user->notify(new FrontendResetPasswordNotification($token));
            }
        );

        if ($status === Password::RESET_THROTTLED) {
            return back()
                ->withInput($request->only('login'))
                ->withErrors([
                    'login' => 'Ban vua yeu cau dat lai mat khau. Vui long thu lai sau it phut.',
                ]);
        }

        return back()->with('success', 'Neu thong tin hop le, he thong da gui email dat lai mat khau. Vui long kiem tra hop thu cua ban.');
    }

    public function showCustomerResetPasswordForm(Request $request, string $token): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()?->role === 'customer') {
            return redirect()->route('frontend.home');
        }

        $email = $this->normalizeEmail((string) $request->query('email', ''));
        if (!$email) {
            return redirect()
                ->route('frontend.password.forgot')
                ->withErrors([
                    'login' => 'Lien ket dat lai mat khau khong hop le.',
                ]);
        }

        return $this->frontendView('auth.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    public function customerResetPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'email.required' => 'Thieu email khoi phuc mat khau.',
            'email.email' => 'Email khoi phuc khong hop le.',
            'password.required' => 'Vui long nhap mat khau moi.',
            'password.min' => 'Mat khau moi phai co it nhat 6 ky tu.',
            'password.confirmed' => 'Xac nhan mat khau chua khop.',
        ]);

        $validated['email'] = (string) $this->normalizeEmail((string) $validated['email']);

        $status = Password::broker('users')->reset(
            $validated,
            function (User $user, string $password): void {
                $user->forceFill([
                    'password_hash' => Hash::make($password),
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'Lien ket dat lai mat khau khong hop le hoac da het han.',
                ]);
        }

        return redirect()
            ->route('frontend.login')
            ->withInput([
                'login' => $validated['email'],
            ])
            ->with('success', 'Dat lai mat khau thanh cong. Ban co the dang nhap ngay.');
    }

    public function showCustomerChangePasswordForm(): View|RedirectResponse
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'customer') {
            return redirect()->route('frontend.login');
        }

        return $this->frontendView('auth.change-password');
    }

    public function customerChangePassword(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'customer') {
            return redirect()->route('frontend.login');
        }

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu chưa khớp.',
        ]);

        if (!Hash::check((string) $validated['current_password'], (string) $user->getAuthPassword())) {
            return back()->withErrors([
                'current_password' => 'Mật khẩu hiện tại không đúng.',
            ])->withInput($request->except(['current_password', 'password', 'password_confirmation']));
        }

        $user->forceFill([
            'password_hash' => Hash::make((string) $validated['password']),
        ])->save();

        return redirect()
            ->route('frontend.password.change')
            ->with('success', 'Đổi mật khẩu thành công.');
    }

    public function customerUpdateProfileInfo(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'customer') {
            return redirect()->route('frontend.login');
        }

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
        ], [
            'full_name.required' => 'Vui lòng nhập họ và tên.',
            'full_name.max' => 'Họ và tên tối đa 150 ký tự.',
        ]);

        $user->forceFill([
            'full_name' => trim((string) $validated['full_name']),
        ])->save();

        return redirect()
            ->route('frontend.profile')
            ->with('success', 'Cập nhật thông tin cá nhân thành công.');
    }

    public function customerUpdateAddress(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'customer') {
            return redirect()->route('frontend.login');
        }

        $validated = $request->validate([
            'province' => ['required', 'string', 'max:120'],
            'district' => ['required', 'string', 'max:120'],
            'ward' => ['required', 'string', 'max:120'],
            'address_line' => ['required', 'string', 'min:5', 'max:255'],
        ], [
            'province.required' => 'Vui lòng chọn tỉnh/thành phố.',
            'district.required' => 'Vui lòng chọn quận/huyện.',
            'ward.required' => 'Vui lòng chọn phường/xã.',
            'address_line.required' => 'Vui lòng nhập số nhà, tên đường.',
            'address_line.min' => 'Địa chỉ chi tiết cần ít nhất 5 ký tự.',
        ]);

        $this->upsertCustomerDefaultAddress(
            $user,
            [
                'province' => trim((string) $validated['province']),
                'district' => trim((string) $validated['district']),
                'ward' => trim((string) $validated['ward']),
                'address_line' => trim((string) $validated['address_line']),
            ],
            trim((string) ($user->full_name ?? '')) ?: 'Khách hàng',
            $this->normalizeVietnamPhone((string) ($user->phone ?? ''))
        );

        return redirect()
            ->route('frontend.profile')
            ->with('success', 'Cập nhật địa chỉ thành công.');
    }

    public function customerUpdateAvatar(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'customer') {
            return redirect()->route('frontend.login');
        }

        $validated = $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ], [
            'avatar.required' => 'Vui lòng chọn ảnh đại diện.',
            'avatar.image' => 'Tệp tải lên phải là hình ảnh.',
            'avatar.mimes' => 'Ảnh đại diện chỉ hỗ trợ JPG, PNG hoặc WEBP.',
            'avatar.max' => 'Ảnh đại diện tối đa 2MB.',
        ]);

        $file = $validated['avatar'];
        $directory = UploadPath::absolute('users');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $extension = strtolower((string) $file->getClientOriginalExtension());
        $fileName = 'avatar_' . $user->id . '_' . Str::random(12) . '.' . $extension;
        $file->move($directory, $fileName);

        $newAvatarPath = '/' . trim(UploadPath::relative('users') . '/' . $fileName, '/');
        $oldAvatarPath = trim((string) ($user->avatar_url ?? ''));
        $usersPrefix = '/' . trim(UploadPath::relative('users'), '/') . '/';
        if ($oldAvatarPath !== '' && str_starts_with($oldAvatarPath, $usersPrefix)) {
            $oldFile = public_path(ltrim($oldAvatarPath, '/'));
            if (is_file($oldFile)) {
                @unlink($oldFile);
            }
        }

        $user->forceFill([
            'avatar_url' => $newAvatarPath,
        ])->save();

        return redirect()
            ->route('frontend.profile')
            ->with('success', 'Cập nhật ảnh đại diện thành công.');
    }

    public function customerOrderHistory(): View|RedirectResponse
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'customer') {
            return redirect()->route('frontend.login');
        }

        $orders = Order::query()
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->get()
            ->map(function (Order $order) {
                $order->history_type = 'order';
                $order->history_tab = $this->customerOrderHistoryTab((string) $order->order_status, 'order');
                $order->history_code = $order->order_code ?: ('#' . $order->id);
                $order->history_amount = (float) $order->total_amount;
                $order->history_created_at = $order->created_at;
                $order->history_status_label = $order->order_status_label ?? ucfirst((string) $order->order_status);
                $order->history_payment_status_label = $this->customerPaymentStatusLabel((string) $order->payment_status, (string) $order->payment_method);
                $order->history_url = route('frontend.profile.orders.detail', ['order' => $order->id]);

                return $order;
            });

        $normalizedPhone = $this->normalizeVietnamPhone((string) ($user->phone ?? ''));
        $normalizedEmail = $this->normalizeEmail((string) ($user->email ?? ''));

        $pendingInvoices = DB::table('payment_invoices')
            ->where('payment_method', 'vietqr')
            ->whereNull('order_id')
            ->whereIn('invoice_status', ['pending_payment', 'expired', 'cancelled'])
            ->where(function ($query) use ($user, $normalizedPhone, $normalizedEmail): void {
                $query->where('user_id', $user->id);

                if ($normalizedPhone !== '') {
                    $query->orWhere('customer_phone', $normalizedPhone);
                }

                if ($normalizedEmail !== null) {
                    $query->orWhereRaw('LOWER(customer_email) = ?', [$normalizedEmail]);
                }
            })
            ->latest('created_at')
            ->get()
            ->map(function ($invoice) {
                $invoice = $this->expireVietqrInvoiceIfNeeded($invoice);
                $invoice->history_type = 'invoice';
                $invoice->history_tab = $this->customerOrderHistoryTab((string) $invoice->invoice_status, 'invoice');
                $invoice->history_code = $invoice->invoice_code ?: ('#INV-' . $invoice->id);
                $invoice->history_amount = (float) $invoice->total_amount;
                $invoice->history_created_at = Carbon::parse($invoice->created_at);
                $invoice->history_status_label = $this->customerInvoiceStatusLabel((string) $invoice->invoice_status);
                $invoice->history_payment_status_label = $this->customerPaymentStatusLabel((string) $invoice->payment_status, (string) $invoice->payment_method);
                $invoice->history_url = URL::signedRoute('frontend.vietqr.payment', ['invoice' => $invoice->id]);

                return $invoice;
            });

        $historyItems = collect($orders->all())
            ->merge($pendingInvoices)
            ->sortByDesc(fn ($item) => Carbon::parse($item->history_created_at ?? $item->created_at ?? now())->timestamp)
            ->values();

        $orderHistoryTabs = collect([
            ['key' => 'verified', 'label' => 'Đã xác minh'],
            ['key' => 'pending', 'label' => 'Chờ xác minh'],
            ['key' => 'cancelled', 'label' => 'Đã hủy'],
        ])->map(function (array $tab) use ($historyItems) {
            $tab['count'] = $historyItems->where('history_tab', $tab['key'])->count();

            return $tab;
        })->all();

        $activeOrderHistoryTab = (string) request()->query('tab', 'verified');
        if (!in_array($activeOrderHistoryTab, ['verified', 'pending', 'cancelled'], true)) {
            $activeOrderHistoryTab = 'verified';
        }

        $historyItems = $historyItems
            ->where('history_tab', $activeOrderHistoryTab)
            ->values();

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 12;
        $orders = new LengthAwarePaginator(
            $historyItems->forPage($currentPage, $perPage)->values(),
            $historyItems->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        return $this->frontendView('auth.order-history', [
            'customerOrders' => $orders,
            'orderHistoryTabs' => $orderHistoryTabs,
            'activeOrderHistoryTab' => $activeOrderHistoryTab,
        ]);
    }

    private function customerOrderHistoryTab(string $status, string $type): string
    {
        $status = strtolower($status);

        if (in_array($status, ['cancelled', 'expired'], true)) {
            return 'cancelled';
        }

        if ($type === 'invoice' || in_array($status, ['pending_verification', 'pending_payment', 'pending'], true)) {
            return 'pending';
        }

        return 'verified';
    }

    private function customerPaymentStatusLabel(string $status, string $paymentMethod = ''): string
    {
        $status = strtolower($status);
        $paymentMethod = strtolower($paymentMethod);

        if ($paymentMethod === 'cod' && in_array($status, ['unpaid', 'pending'], true)) {
            return 'Thanh toán khi nhận hàng';
        }

        return match ($status) {
            'unpaid' => 'Chưa thanh toán',
            'pending' => 'Chờ thanh toán',
            'paid' => 'Đã thanh toán',
            'failed' => 'Thanh toán thất bại',
            'cancelled' => 'Đã hủy',
            'refunded' => 'Đã hoàn tiền',
            'partial_refund', 'partially_refunded' => 'Hoàn tiền một phần',
            default => ucwords(str_replace('_', ' ', $status)),
        };
    }

    private function customerInvoiceStatusLabel(string $status): string
    {
        return match ($status) {
            'pending_payment' => 'Chờ thanh toán',
            'completed' => 'Hoàn tất',
            'expired' => 'Hết hạn thanh toán',
            'cancelled' => 'Đã hủy',
            default => ucwords(str_replace('_', ' ', $status)),
        };
    }

    public function customerOrderDetail(Order $order): View|RedirectResponse
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'customer') {
            return redirect()->route('frontend.login');
        }

        if ((int) ($order->user_id ?? 0) !== (int) $user->id) {
            abort(404);
        }

        $orderItems = DB::table('order_items')
            ->where('order_id', $order->id)
            ->orderBy('id')
            ->get();
        $orderItemImageFallback = 'https://images.unsplash.com/photo-1581044777550-4cfa60707c03?auto=format&fit=crop&w=300&q=80';
        $orderItemProductIds = $orderItems
            ->pluck('product_id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();
        $orderItemImageByProduct = $this->primaryImagesByProductIds($orderItemProductIds, $orderItemImageFallback);
        $orderItemSlugByProduct = $orderItemProductIds !== []
            ? DB::table('products')->whereIn('id', $orderItemProductIds)->pluck('slug', 'id')
            : collect();
        $orderItems = $orderItems->map(function ($item) use ($orderItemImageByProduct, $orderItemImageFallback, $orderItemSlugByProduct) {
            $productId = (int) ($item->product_id ?? 0);
            $productSlug = (string) ($orderItemSlugByProduct[$productId] ?? '');
            $item->image_url = $orderItemImageByProduct[$productId] ?? $orderItemImageFallback;
            $item->product_url = $productSlug !== ''
                ? route('frontend.product-detail', ['slug' => $productSlug])
                : null;

            return $item;
        });

        $orderPayment = DB::table('order_payments')
            ->where('order_id', $order->id)
            ->orderByDesc('id')
            ->first();

        $pickupStore = null;
        if ((string) $order->delivery_type === 'pickup' && !empty($order->store_id)) {
            $pickupStore = DB::table('stores')->where('id', $order->store_id)->first();
        }

        return $this->frontendView('auth.order-detail', [
            'customerOrder' => $order,
            'customerOrderItems' => $orderItems,
            'customerOrderPayment' => $orderPayment,
            'customerOrderPickupStore' => $pickupStore,
        ]);
    }

    public function customerProfile(): View|RedirectResponse
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'customer') {
            return redirect()->route('frontend.login');
        }

        $profile = DB::table('customer_profiles')
            ->where('user_id', $user->id)
            ->first();
        $defaultAddress = DB::table('customer_addresses')
            ->where('user_id', $user->id)
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->first();

        $allOrders = Order::query()
            ->where('user_id', $user->id);

        $verifiedOrders = Order::query()
            ->where('user_id', $user->id)
            ->where('order_status', 'verified');
        $placedOrdersCount = (clone $allOrders)->count();
        $verifiedOrdersCount = (clone $verifiedOrders)->count();
        $verifiedTotalSpent = (clone $verifiedOrders)->sum('total_amount');

        $recentOrders = Order::query()
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->limit(5)
            ->get();

        $tierLabels = [
            'new' => 'Khách hàng mới',
            'friendly' => 'Khách hàng thân thiện',
            'loyal' => 'Khách hàng trung thành',
            'vip' => 'Khách hàng VIP',
            'diamond' => 'Khách hàng Kim cương',
        ];

        $displayName = trim((string) ($user->full_name ?: $user->email ?: 'Khách hàng'));
        $nameParts = preg_split('/\s+/u', $displayName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $initials = collect($nameParts)
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');

        return $this->frontendView('auth.profile', [
            'customerUser' => $user,
            'customerDisplayName' => $displayName,
            'customerInitials' => $initials !== '' ? $initials : 'KH',
            'customerProfile' => $profile,
            'customerTierLabel' => $tierLabels[(string) ($profile->tier ?? 'new')] ?? 'Khách hàng mới',
            'customerJoinedLabel' => optional($user->created_at)->format('d/m/Y') ?: now()->format('d/m/Y'),
            'customerPlacedOrders' => (int) $placedOrdersCount,
            'customerTotalSpent' => (float) ($profile->total_spent ?? $verifiedTotalSpent),
            'customerTotalOrders' => (int) ($profile->total_orders ?? $verifiedOrdersCount),
            'customerRewardPoints' => (int) floor(((float) ($profile->total_spent ?? 0)) / 10000),
            'customerRecentOrders' => $recentOrders,
            'customerDefaultAddress' => $defaultAddress,
        ]);
    }

    public function customerLogout(Request $request): RedirectResponse
    {
        if (Auth::check() && Auth::user()?->role === 'customer') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()->route('frontend.home')->with('success', 'Da dang xuat.');
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

    private function upsertCustomerDefaultAddress(User $user, array $address, string $recipientName, string $recipientPhone): void
    {
        $existingDefault = DB::table('customer_addresses')
            ->where('user_id', $user->id)
            ->where('is_default', 1)
            ->orderByDesc('id')
            ->first();

        $payload = [
            'recipient_name' => $recipientName !== '' ? $recipientName : (trim((string) ($user->full_name ?? '')) ?: 'Khách hàng'),
            'recipient_phone' => $recipientPhone !== '' ? $recipientPhone : (string) ($user->phone ?? ''),
            'province' => trim((string) ($address['province'] ?? '')),
            'district' => trim((string) ($address['district'] ?? '')),
            'ward' => trim((string) ($address['ward'] ?? '')),
            'address_line' => trim((string) ($address['address_line'] ?? '')),
            'is_default' => 1,
            'updated_at' => now(),
        ];

        if ($existingDefault) {
            DB::table('customer_addresses')
                ->where('id', $existingDefault->id)
                ->update($payload);
            return;
        }

        DB::table('customer_addresses')
            ->where('user_id', $user->id)
            ->update(['is_default' => 0, 'updated_at' => now()]);

        DB::table('customer_addresses')->insert(array_merge($payload, [
            'user_id' => $user->id,
            'created_at' => now(),
        ]));
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

    private function autoVerifyPaidOrder(int $orderId, string $changedBy, string $note): void
    {
        $order = Order::query()->find($orderId);
        if (!$order) {
            return;
        }

        $shouldSendVerifiedEmail = false;

        DB::transaction(function () use ($order, $changedBy, $note, &$shouldSendVerifiedEmail): void {
            $freshOrder = Order::query()->lockForUpdate()->find($order->id);
            if (!$freshOrder) {
                return;
            }

            if ($freshOrder->order_status !== 'verified') {
                DB::table('order_status_logs')->insert([
                    'order_id' => $freshOrder->id,
                    'from_status' => $freshOrder->order_status,
                    'to_status' => 'verified',
                    'changed_by' => $changedBy,
                    'note' => $note,
                    'created_at' => now(),
                ]);

                $freshOrder->update([
                    'order_status' => 'verified',
                    'verified_at' => now(),
                ]);

                $this->ensureOrderCustomerLinked($freshOrder->fresh());
                $shouldSendVerifiedEmail = true;
            } elseif (!empty($freshOrder->user_id)) {
                $this->syncCustomerProfileByUserId((int) $freshOrder->user_id);
            }
        });

        if ($shouldSendVerifiedEmail) {
            $verifiedOrder = Order::query()->find($orderId);
            if ($verifiedOrder) {
                $verifiedOrder->ensureCustomerTrackingToken();
                $this->orderEmailService->sendOrderVerifiedEmail($verifiedOrder);
            }
        }
    }

    public function sitemap(): Response
    {
        $staticUrls = collect([
            [
                'loc' => route('frontend.home'),
                'lastmod' => now(),
                'changefreq' => 'daily',
                'priority' => '1.0',
            ],
            [
                'loc' => route('frontend.featured-products'),
                'lastmod' => now(),
                'changefreq' => 'daily',
                'priority' => '0.8',
            ],
            [
                'loc' => route('frontend.customer-ranking'),
                'lastmod' => now(),
                'changefreq' => 'weekly',
                'priority' => '0.6',
            ],
            [
                'loc' => route('frontend.customer-support'),
                'lastmod' => now(),
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ],
            [
                'loc' => route('frontend.policy.return-warranty'),
                'lastmod' => now(),
                'changefreq' => 'monthly',
                'priority' => '0.4',
            ],
            [
                'loc' => route('frontend.policy.privacy'),
                'lastmod' => now(),
                'changefreq' => 'monthly',
                'priority' => '0.4',
            ],
            [
                'loc' => route('frontend.policy.shipping'),
                'lastmod' => now(),
                'changefreq' => 'monthly',
                'priority' => '0.4',
            ],
        ]);

        $categories = Category::query()
            ->where('status', 'active')
            ->select(['id', 'parent_id', 'slug', 'updated_at'])
            ->orderByDesc('updated_at')
            ->get()
            ->map(function (Category $category) {
                $routeName = match (true) {
                    is_null($category->parent_id) => 'frontend.subcategories',
                    $this->categoryDepth($category) >= 2 => 'frontend.childcategories',
                    default => 'frontend.category',
                };

                return [
                    'loc' => route($routeName, ['slug' => $category->slug]),
                    'lastmod' => $category->updated_at ?? now(),
                    'changefreq' => 'weekly',
                    'priority' => is_null($category->parent_id) ? '0.8' : '0.7',
                ];
            });

        $products = Product::query()
            ->where('status', 'active')
            ->select(['slug', 'updated_at'])
            ->orderByDesc('updated_at')
            ->get()
            ->map(function (Product $product) {
                return [
                    'loc' => route('frontend.product-detail', ['slug' => $product->slug]),
                    'lastmod' => $product->updated_at ?? now(),
                    'changefreq' => 'weekly',
                    'priority' => '0.9',
                ];
            });

        $xml = view('frontend.sitemap', [
            'urls' => $staticUrls->concat($categories)->concat($products),
        ])->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    public function home(): View
    {
        $now = now();

        $promoTicker = $this->activePromoTicker($now);
        $heroSectionActive = DB::table('home_sections')
            ->where('section_key', 'hero')
            ->value('is_active');
        $heroSectionActive = is_null($heroSectionActive) ? true : (bool) $heroSectionActive;
        $featuredSectionActive = DB::table('home_sections')
            ->where('section_key', 'featured-products')
            ->value('is_active');
        $featuredSectionActive = is_null($featuredSectionActive) ? true : (bool) $featuredSectionActive;
        $contactSectionActive = DB::table('home_sections')
            ->where('section_key', 'contact')
            ->value('is_active');
        $contactSectionActive = is_null($contactSectionActive) ? true : (bool) $contactSectionActive;

        $heroSectionId = null;
        if ($heroSectionActive) {
            $heroSectionId = DB::table('home_sections')
                ->where('section_key', 'hero')
                ->where('is_active', 1)
                ->value('id');
        }

        $heroBanners = collect();
        if ($heroSectionId) {
            $heroBanners = DB::table('home_section_items')
                ->where('section_id', $heroSectionId)
                ->where('item_type', 'banner')
                ->where('is_active', 1)
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->get()
                ->map(function ($banner) {
                    $banner->image_url = $this->resolveImageUrl(
                        $banner->image_url,
                        'https://images.unsplash.com/photo-1617137968427-85924c800a22?auto=format&fit=crop&w=1200&q=80'
                    );

                    return $banner;
                });
        }

        if (false && $heroSectionActive) {
        $featuredBannerProducts = Product::query()
            ->where('status', 'active')
            ->where('is_featured', true)
            ->inRandomOrder()
            ->limit(5)
            ->get();

        $featuredBannerProducts = $this->attachPrimaryImage(
            $featuredBannerProducts,
            'https://images.unsplash.com/photo-1581044777550-4cfa60707c03?auto=format&fit=crop&w=900&q=80'
        );

        $productBanners = $featuredBannerProducts->map(function (Product $product) {
            return (object) [
                'title' => $product->name,
                'subtitle' => 'Sản phẩm thông dụng',
                'image_url' => $product->primary_image_url,
                'target_url' => route('frontend.product-detail', ['slug' => $product->slug]),
            ];
        });

        $heroBanners = $heroBanners->concat($productBanners)->values();
        }

        $parentCategories = Category::query()
            ->whereNull('parent_id')
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function (Category $category) {
                $category->display_image = $this->categoryDisplayImage(
                    $category,
                    'https://images.unsplash.com/photo-1603252109303-2751441dd157?auto=format&fit=crop&w=300&q=80'
                );
                $category->navigation_url = is_null($category->parent_id)
                    ? route('frontend.subcategories', ['slug' => $category->slug])
                    : route('frontend.childcategories', ['slug' => $category->slug]);

                return $category;
            });

        $femaleCategories = $this->filterHomeCategoriesByTargetSlug($parentCategories, ['female'])->take(12)->values();
        $maleCategories = $this->filterHomeCategoriesByTargetSlug($parentCategories, ['male'])->take(12)->values();

        $featuredProducts = Product::query()
            ->where('status', 'active')
            ->where('is_featured', true)
            ->inRandomOrder()
            ->limit(12)
            ->get();

        $featuredProducts = $this->attachPrimaryImage($featuredProducts, 'https://images.unsplash.com/photo-1581044777550-4cfa60707c03?auto=format&fit=crop&w=900&q=80');
        $footerGroups = $this->footerGroups();
        $footerInfo = $this->footerInfo();
        $siteName = $footerInfo['site_name'];

        return $this->frontendView('home', compact('promoTicker', 'heroBanners', 'parentCategories', 'femaleCategories', 'maleCategories', 'featuredProducts', 'footerGroups', 'footerInfo', 'siteName', 'heroSectionActive', 'featuredSectionActive', 'contactSectionActive'));
    }

    public function favicon(): RedirectResponse
    {
        $faviconUrl = null;

        try {
            $faviconUrl = DB::table('site_settings')
                ->where('setting_key', 'site_favicon_url')
                ->value('setting_value');
        } catch (\Throwable $exception) {
            $faviconUrl = null;
        }

        $resolvedFaviconUrl = $this->resolveSettingAssetUrl($faviconUrl);

        if ($resolvedFaviconUrl) {
            return redirect()->away($resolvedFaviconUrl, 302);
        }

        abort(404);
    }

    public function search(): View
    {
        $queryText = trim((string) request('q', ''));
        $preferredProductId = max(0, (int) request('product_id', 0));

        return $this->frontendView('search', compact('queryText', 'preferredProductId'));
    }

    public function policyPage(string $slug): View
    {
        $settings = DB::table('site_settings')
            ->whereIn('setting_key', ['product_return_policy', 'privacy_policy', 'shipping_policy', 'website_usage_guide'])
            ->pluck('setting_value', 'setting_key');

        $policies = [
            'doi-tra-bao-hanh' => [
                'title' => 'Chính sách đổi trả và bảo hành sản phẩm',
                'description' => 'Thông tin về điều kiện đổi trả, thời gian tiếp nhận và phạm vi hỗ trợ bảo hành khi sản phẩm phát sinh lỗi.',
                'content' => trim((string) ($settings['product_return_policy'] ?? '')),
            ],
            'bao-mat-thong-tin' => [
                'title' => 'Chính sách bảo mật thông tin',
                'description' => 'Mô tả cách website thu thập, sử dụng, lưu trữ và bảo vệ thông tin cá nhân của khách hàng trong quá trình mua sắm.',
                'content' => trim((string) ($settings['privacy_policy'] ?? '')),
            ],
            'van-chuyen' => [
                'title' => 'Chính sách vận chuyển',
                'description' => 'Quy định về thời gian giao hàng, khu vực phục vụ, phí vận chuyển và trách nhiệm của các bên trong quá trình giao nhận.',
                'content' => trim((string) ($settings['shipping_policy'] ?? '')),
            ],
        ];

        $policies['huong-dan'] = [
            'title' => 'Hướng dẫn',
            'description' => 'Hướng dẫn sử dụng website đặt hàng được quản lý từ phần Cài đặt chung trong backend.',
            'content' => trim((string) ($settings['website_usage_guide'] ?? '')),
        ];

        abort_unless(isset($policies[$slug]), 404);

        $policy = $policies[$slug];
        $footerGroups = $this->footerGroups();
        $footerInfo = $this->footerInfo();
        $siteName = $footerInfo['site_name'];
        $policy['content'] = $policy['content'] !== '' ? $policy['content'] : 'Nội dung đang được cập nhật.';
        $policyLinks = collect([
            [
                'title' => 'Hướng dẫn và Thông Báo',
                'url' => route('frontend.policy.guide'),
                'is_active' => $slug === 'huong-dan',
            ],
            [
                'title' => 'Đổi trả và bảo hành',
                'url' => route('frontend.policy.return-warranty'),
                'is_active' => $slug === 'doi-tra-bao-hanh',
            ],
            [
                'title' => 'Bảo mật thông tin',
                'url' => route('frontend.policy.privacy'),
                'is_active' => $slug === 'bao-mat-thong-tin',
            ],
            [
                'title' => 'Vận chuyển',
                'url' => route('frontend.policy.shipping'),
                'is_active' => $slug === 'van-chuyen',
            ],
        ]);

        $breadcrumbSchema = $this->buildBreadcrumbSchema([
            ['name' => 'Trang chủ', 'url' => route('frontend.home')],
            ['name' => 'Chính sách', 'url' => route('frontend.policy.return-warranty')],
            [
                'name' => $policy['title'],
                'url' => match ($slug) {
                    'doi-tra-bao-hanh' => route('frontend.policy.return-warranty'),
                    'bao-mat-thong-tin' => route('frontend.policy.privacy'),
                    'van-chuyen' => route('frontend.policy.shipping'),
                    default => route('frontend.policy.guide'),
                },
            ],
        ]);

        return $this->frontendView('policy', compact('policy', 'policyLinks', 'footerGroups', 'footerInfo', 'siteName', 'breadcrumbSchema'));
    }

    public function customerSupport(): View
    {
        $guideLinks = [
            [
                'title' => 'Hướng dẫn mua hàng',
                'description' => 'Xem các bước chọn sản phẩm, đặt hàng và theo dõi đơn.',
                'icon' => 'bi-journal-text',
                'url' => route('frontend.policy.guide'),
            ],
            [
                'title' => 'Chính sách đổi trả',
                'description' => 'Kiểm tra điều kiện đổi trả, bảo hành và thời gian xử lý.',
                'icon' => 'bi-arrow-repeat',
                'url' => route('frontend.policy.return-warranty'),
            ],
            [
                'title' => 'Chính sách vận chuyển',
                'description' => 'Xem thông tin giao hàng, nhận tại cửa hàng và phí vận chuyển.',
                'icon' => 'bi-truck',
                'url' => route('frontend.policy.shipping'),
            ],
        ];

        return $this->frontendView('customer-support', compact('guideLinks'));
    }

    public function customerRanking(): View
    {
        $customers = DB::table('users as u')
            ->leftJoin('customer_profiles as cp', 'cp.user_id', '=', 'u.id')
            ->selectRaw("
                u.id,
                u.full_name,
                u.phone,
                u.avatar_url,
                COALESCE(cp.tier, 'new') as tier,
                COALESCE(cp.total_orders, 0) as total_orders,
                COALESCE(cp.total_spent, 0) as total_spent
            ")
            ->where('u.role', 'customer')
            ->where(function ($query) {
                $query->where('cp.total_orders', '>', 0)
                    ->orWhere('cp.total_spent', '>', 0);
            })
            ->orderByDesc('cp.total_spent')
            ->orderByDesc('cp.total_orders')
            ->orderBy('u.full_name')
            ->limit(50)
            ->get()
            ->values()
            ->map(function ($customer, int $index) {
                $customer->rank = $index + 1;
                $customer->display_name = trim((string) $customer->full_name) !== '' ? $customer->full_name : 'Khach #' . $customer->id;
                $customer->masked_phone = $this->maskPhoneNumber($customer->phone);
                $avatarPath = trim((string) ($customer->avatar_url ?? ''));
                $customer->avatar_src = $avatarPath !== ''
                    ? (str_starts_with($avatarPath, 'http://') || str_starts_with($avatarPath, 'https://')
                        ? $avatarPath
                        : asset(ltrim($avatarPath, '/')))
                    : null;

                return $customer;
            });

        $topThree = $customers->take(3)->values();
        $otherCustomers = $customers->slice(3)->values();

        $summary = [
            'customers_count' => $customers->count(),
            'total_spent' => (float) $customers->sum('total_spent'),
            'total_orders' => (int) $customers->sum('total_orders'),
        ];

        $footerGroups = $this->footerGroups();
        $footerInfo = $this->footerInfo();
        $siteName = $footerInfo['site_name'];

        $breadcrumbSchema = $this->buildBreadcrumbSchema([
            ['name' => 'Trang chủ', 'url' => route('frontend.home')],
            ['name' => 'Xếp hạng khách hàng', 'url' => route('frontend.customer-ranking')],
        ]);

        return $this->frontendView('customer-ranking', compact(
            'customers',
            'topThree',
            'otherCustomers',
            'summary',
            'footerGroups',
            'footerInfo',
            'siteName',
            'breadcrumbSchema'
        ));
    }

    public function category(?string $slug = null): View
    {
        $targetSlug = trim((string) request('target', ''));
        $selectedTarget = $targetSlug !== ''
            ? ProductTarget::query()
                ->select(['id', 'name', 'slug'])
                ->where('slug', $targetSlug)
                ->where('status', 'active')
                ->first()
            : null;

        $selectedCategory = null;
        if (!empty($slug)) {
            $selectedCategory = Category::query()
                ->where('slug', $slug)
                ->where('status', 'active')
                ->first();
        }

        $queryText = trim((string) request('q', ''));
        $productsQuery = Product::query()->where('status', 'active');

        if ($selectedTarget) {
            $productsQuery->whereIn('category_id', $this->categoryIdsByTargetId((int) $selectedTarget->id));
        }

        if ($selectedCategory) {
            $categoryIds = $this->descendantCategoryIds((int) $selectedCategory->id);
            $categoryIds[] = (int) $selectedCategory->id;
            $productsQuery->whereIn('category_id', $categoryIds);
        }

        FrontendProductSearch::applyToQuery($productsQuery, $queryText);

        $childCategories = collect();
        $showProducts = !$selectedCategory || is_null($selectedCategory->parent_id);
        $productsTotal = $showProducts ? (clone $productsQuery)->count() : 0;

        $topCategories = Category::query()
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->when($selectedTarget, function (Collection $categories) use ($selectedTarget) {
                return $this->filterHomeCategoriesByTargetSlug($categories, [$selectedTarget->slug]);
            })
            ->map(function (Category $category) {
                $category->display_image = $this->categoryDisplayImage(
                    $category,
                    'https://images.unsplash.com/photo-1603252109303-2751441dd157?auto=format&fit=crop&w=300&q=80'
                );

                return $category;
            });
        $topCategories = $this->sortCategoriesByParentTree($topCategories);
        $categoryIdsWithChildren = $topCategories
            ->pluck('parent_id')
            ->filter(fn ($parentId) => !is_null($parentId))
            ->map(fn ($parentId) => (int) $parentId)
            ->unique()
            ->all();
        $topCategories = $topCategories->map(function (Category $category) use ($categoryIdsWithChildren) {
            $hasChildren = in_array((int) $category->id, $categoryIdsWithChildren, true);
            $category->navigation_url = $hasChildren
                ? route('frontend.subcategories', ['slug' => $category->slug])
                : (
                    is_null($category->parent_id)
                        ? route('frontend.subcategories', ['slug' => $category->slug])
                        : route('frontend.childcategories', ['slug' => $category->slug])
                );

            return $category;
        });
        $categoryGroups = $this->groupCategoriesByParentTree($topCategories);

        if ($selectedCategory) {
            $selectedCategory->display_image = $this->categoryDisplayImage(
                $selectedCategory,
                'https://images.unsplash.com/photo-1603252109303-2751441dd157?auto=format&fit=crop&w=700&q=80'
            );

            $childCategories = Category::query()
                ->where('parent_id', $selectedCategory->id)
                ->where('status', 'active')
                ->when($selectedTarget, function ($query) use ($selectedTarget) {
                    $query->where('product_target_id', $selectedTarget->id);
                })
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
                ->map(function (Category $category) {
                    $category->display_image = $this->categoryDisplayImage(
                        $category,
                        'https://images.unsplash.com/photo-1603252109303-2751441dd157?auto=format&fit=crop&w=300&q=80'
                    );

                    return $category;
                });
        }

        $breadcrumbItems = [
            ['name' => 'Trang chủ', 'url' => route('frontend.home')],
            ['name' => 'Danh mục', 'url' => route('frontend.category')],
        ];

        if ($selectedTarget) {
            $breadcrumbItems[] = [
                'name' => $selectedTarget->name,
                'url' => route('frontend.category', ['target' => $selectedTarget->slug]),
            ];
        }

        if ($selectedCategory) {
            $breadcrumbItems = array_merge($breadcrumbItems, $this->categoryBreadcrumbItems($selectedCategory));
        }

        $breadcrumbSchema = $this->buildBreadcrumbSchema($breadcrumbItems);

        return $this->frontendView('category', compact('selectedCategory', 'selectedTarget', 'queryText', 'productsTotal', 'topCategories', 'categoryGroups', 'childCategories', 'showProducts', 'breadcrumbSchema'));
    }

    public function featuredProducts(): View
    {
        $featuredProductsTotal = Product::query()
            ->where('status', 'active')
            ->where('is_featured', true)
            ->count();

        $breadcrumbSchema = $this->buildBreadcrumbSchema([
            ['name' => 'Trang chủ', 'url' => route('frontend.home')],
            ['name' => 'Sản phẩm nổi bật', 'url' => route('frontend.featured-products')],
        ]);

        return $this->frontendView('featured-products', compact('featuredProductsTotal', 'breadcrumbSchema'));
    }

    public function merchantFeed()
    {
        $feedBaseUrl = rtrim((string) config('app.url'), '/');
        if ($feedBaseUrl === '') {
            $feedBaseUrl = rtrim(url('/'), '/');
        }

        $products = Product::query()
            ->with('category:id,name')
            ->where('status', 'active')
            ->orderByDesc('is_featured')
            ->orderByDesc('id')
            ->get();

        $products = $this->attachPrimaryImage(
            $products,
            'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?auto=format&fit=crop&w=1200&q=80'
        );

        $siteName = $this->footerInfo()['site_name'];
        $items = $products->map(function (Product $product) use ($siteName, $feedBaseUrl) {
            $description = trim(strip_tags((string) ($product->description ?: $product->name)));
            $description = Str::limit(preg_replace('/\s+/u', ' ', $description) ?: $product->name, 500, '');

            return [
                'id' => (string) $product->id,
                'title' => (string) $product->name,
                'description' => $description,
                'link' => $feedBaseUrl . route('frontend.product-detail', ['slug' => $product->slug], false),
                'image_link' => $this->merchantFeedUrl((string) ($product->primary_image_url ?? ''), $feedBaseUrl),
                'availability' => (int) $product->stock_qty > 0 ? 'in_stock' : 'out_of_stock',
                'price' => number_format((float) $product->price, 0, '.', '') . ' VND',
                'brand' => trim((string) ($product->brand ?: $siteName)),
                'product_type' => (string) optional($product->category)->name,
            ];
        })->values();

        return response()
            ->view('frontend.merchant-feed', compact('items', 'siteName', 'feedBaseUrl'))
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function searchSuggestions(): JsonResponse
    {
        $queryText = trim((string) request('q', ''));
        $categoryId = (int) request('category_id', 0);
        $limit = max(1, min(50, (int) request('limit', 10)));
        $offset = max(0, (int) request('offset', 0));
        $productsQuery = Product::query()->where('status', 'active');

        if ($categoryId > 0) {
            $categoryIds = $this->descendantCategoryIds($categoryId);
            $categoryIds[] = $categoryId;
            $productsQuery->whereIn('category_id', array_values(array_unique($categoryIds)));
        }

        $total = FrontendProductSearch::suggestionsCount($productsQuery, $queryText);
        $suggestions = FrontendProductSearch::suggestions($productsQuery, $queryText, $limit, $offset)
            ->map(fn ($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'url' => route('frontend.search', [
                    'q' => $queryText,
                    'product_id' => $product->id,
                ]),
            ])
            ->values();

        return response()->json([
            'query' => $queryText,
            'normalized_query' => FrontendProductSearch::normalize($queryText),
            'suggestions' => $suggestions,
            'offset' => $offset,
            'limit' => $limit,
            'total' => $total,
            'has_more' => ($offset + $suggestions->count()) < $total,
        ]);
    }

    public function subcategories(?string $slug = null): View
    {
        $parentCategories = Category::query()
            ->where('status', 'active')
            ->whereIn('id', function ($query) {
                $query->from('categories')
                    ->select('parent_id')
                    ->whereNotNull('parent_id')
                    ->where('status', 'active')
                    ->distinct();
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function (Category $category) {
                $category->display_image = $this->categoryDisplayImage(
                    $category,
                    'https://images.unsplash.com/photo-1496747611176-843222e1e57c?auto=format&fit=crop&w=300&q=80'
                );

                return $category;
            });

        $selectedParent = null;
        if (!empty($slug)) {
            $selectedParent = $parentCategories->firstWhere('slug', $slug);
        }
        if (!$selectedParent) {
            $selectedParent = $parentCategories->first();
        }

        if ($selectedParent) {
            $selectedParent->display_image = $this->categoryDisplayImage(
                $selectedParent,
                'https://images.unsplash.com/photo-1496747611176-843222e1e57c?auto=format&fit=crop&w=700&q=80'
            );
        }

        $childCategories = collect();

        if ($selectedParent) {
            $childCategories = Category::query()
                ->where('parent_id', $selectedParent->id)
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
                ->map(function (Category $category) {
                    $category->display_image = $this->categoryDisplayImage(
                        $category,
                        'https://images.unsplash.com/photo-1496747611176-843222e1e57c?auto=format&fit=crop&w=300&q=80'
                    );

                    return $category;
                });

        }

        $breadcrumbItems = [
            ['name' => 'Trang chủ', 'url' => route('frontend.home')],
            ['name' => 'Danh mục', 'url' => route('frontend.category')],
        ];

        if ($selectedParent) {
            $breadcrumbItems[] = [
                'name' => $selectedParent->name,
                'url' => route('frontend.subcategories', ['slug' => $selectedParent->slug]),
            ];
        }

        $breadcrumbSchema = $this->buildBreadcrumbSchema($breadcrumbItems);

        return $this->frontendView('subcategories', compact('parentCategories', 'selectedParent', 'childCategories', 'breadcrumbSchema'));
    }

    public function childcategories(?string $slug = null): View
    {
        $selectedChild = null;
        if (!empty($slug)) {
            $selectedChild = Category::query()
                ->where('slug', $slug)
                ->whereNotNull('parent_id')
                ->where('status', 'active')
                ->first();
        }

        if (!$selectedChild) {
            $selectedChild = Category::query()
                ->whereNotNull('parent_id')
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->first();
        }

        abort_unless($selectedChild, 404);

        $selectedParent = Category::query()
            ->whereKey($selectedChild->parent_id)
            ->where('status', 'active')
            ->first();

        $siblingChildCategories = collect();
        if ($selectedParent) {
            $siblingChildCategories = Category::query()
                ->where('parent_id', $selectedParent->id)
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
                ->map(function (Category $category) {
                    $category->display_image = $this->categoryDisplayImage(
                        $category,
                        'https://images.unsplash.com/photo-1496747611176-843222e1e57c?auto=format&fit=crop&w=300&q=80'
                    );

                    return $category;
                });
        }

        $selectedChild->display_image = $this->categoryDisplayImage(
            $selectedChild,
            'https://images.unsplash.com/photo-1496747611176-843222e1e57c?auto=format&fit=crop&w=700&q=80'
        );

        $breadcrumbItems = [
            ['name' => 'Trang chủ', 'url' => route('frontend.home')],
            ['name' => 'Danh mục', 'url' => route('frontend.category')],
        ];

        if ($selectedParent) {
            $breadcrumbItems[] = [
                'name' => $selectedParent->name,
                'url' => route('frontend.subcategories', ['slug' => $selectedParent->slug]),
            ];
        }

        $breadcrumbItems[] = [
            'name' => $selectedChild->name,
            'url' => route('frontend.childcategories', ['slug' => $selectedChild->slug]),
        ];

        $breadcrumbSchema = $this->buildBreadcrumbSchema($breadcrumbItems);

        return $this->frontendView('childcategories', compact('selectedParent', 'selectedChild', 'siblingChildCategories', 'breadcrumbSchema'));
    }

    public function productDetail(?string $slug = null): View
    {
        $productQuery = Product::query()->where('status', 'active');
        $product = $slug
            ? $productQuery->where('slug', $slug)->first()
            : $productQuery->orderByDesc('is_featured')->orderByDesc('id')->first();

        abort_unless($product, 404);

        $product->increment('view_count');
        $product->load([
            'colors:id,name,hex_code',
            'sizes:id,name',
            'category:id,name,slug,parent_id',
            'tags:id,name,slug',
        ]);

        $gallery = DB::table('product_images')
            ->where('product_id', $product->id)
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function ($image) {
                $image->resolved_url = $this->resolveImageUrl(
                    $image->image_url,
                    'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?auto=format&fit=crop&w=1200&q=80'
                );

                return $image;
            });

        if ($gallery->isEmpty()) {
            $gallery = collect([(object) ['resolved_url' => 'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?auto=format&fit=crop&w=1200&q=80']]);
        }

        $relatedProducts = Product::query()
            ->where('status', 'active')
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->orderByDesc('sold_count')
            ->limit(8)
            ->get();

        if ($relatedProducts->isEmpty()) {
            $relatedProducts = Product::query()
                ->where('status', 'active')
                ->where('id', '!=', $product->id)
                ->orderByDesc('is_featured')
                ->limit(8)
                ->get();
        }

        $relatedProducts = $this->attachPrimaryImage($relatedProducts, 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=700&q=80');

        $detailPolicies = DB::table('site_settings')
            ->whereIn('setting_key', ['product_size_guide', 'product_care_policy', 'product_return_policy'])
            ->pluck('setting_value', 'setting_key');

        $productSizeGuide = trim((string) ($detailPolicies['product_size_guide'] ?? ''));
        $productCarePolicy = trim((string) ($detailPolicies['product_care_policy'] ?? ''));
        $productReturnPolicy = trim((string) ($detailPolicies['product_return_policy'] ?? ''));
        $productSizeGuide = $productSizeGuide !== '' ? $productSizeGuide : "Cân nặng\tSize (VN/Quốc tế)\tGợi ý\n35 - 42 kg\tXS / S\tNgười nhỏ, dáng mảnh\n43 - 50 kg\tS / M\tDáng trung bình nhỏ\n51 - 57 kg\tM\tPhổ biến\n58 - 65 kg\tL\tHơi đầy đặn\n66 - 75 kg\tXL\tNgười tròn\n76 - 85 kg\tXXL\tNgoại cỡ";
        $productSizeGuideRows = $this->parseProductSizeGuideRows($productSizeGuide);
        $breadcrumbItems = [
            ['name' => 'Trang chủ', 'url' => route('frontend.home')],
            ['name' => 'Danh mục', 'url' => route('frontend.category')],
        ];

        if ($product->category) {
            $breadcrumbItems = array_merge($breadcrumbItems, $this->categoryBreadcrumbItems($product->category));
        }

        $breadcrumbItems[] = [
            'name' => $product->name,
            'url' => route('frontend.product-detail', ['slug' => $product->slug]),
        ];

        $breadcrumbSchema = $this->buildBreadcrumbSchema($breadcrumbItems);

        return $this->frontendView('product-detail', [
            'product' => $product,
            'gallery' => $gallery,
            'relatedProducts' => $relatedProducts,
            'productSizeGuideRows' => $productSizeGuideRows,
            'productSizeGuide' => $productSizeGuide,
            'productCarePolicy' => $productCarePolicy !== '' ? $productCarePolicy : 'Giat nhe, tranh chat tay manh va nhiet do cao. Phan loai mau truoc khi giat va uu tien phoi trong bong ram.',
            'productReturnPolicy' => $productReturnPolicy !== '' ? $productReturnPolicy : 'Doi tra trong 3 ngay voi san pham con nguyen tem mac, chua qua su dung va con day du hoa don mua hang.',
            'breadcrumbSchema' => $breadcrumbSchema,
        ]);
    }

    public function cart(): View
    {
        $cartItems = collect();
        $subtotal = 0;

        return $this->frontendView('cart', compact('cartItems', 'subtotal'));
    }

    public function productConfig(Product $product): JsonResponse
    {
        abort_unless($product->status === 'active', 404);

        $product->load([
            'colors:id,name,hex_code',
            'sizes:id,name',
        ]);

        $primaryImage = DB::table('product_images')
            ->where('product_id', $product->id)
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->value('image_url');

        return response()->json([
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
                'image_url' => $this->resolveImageUrl(
                    $primaryImage,
                    'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?auto=format&fit=crop&w=700&q=80'
                ),
            ],
            'colors' => $product->colors->map(fn ($color) => [
                'id' => $color->id,
                'name' => $color->name,
                'hex_code' => $color->hex_code ?: '#cccccc',
                'image_url' => $this->resolveImageUrl(
                    $color->pivot->image_url ?? '',
                    ''
                ),
            ])->values(),
            'sizes' => $product->sizes->map(fn ($size) => [
                'id' => $size->id,
                'name' => $size->name,
            ])->values(),
        ]);
    }

    public function checkout(): View
    {
        $authUser = Auth::user();
        $checkoutCustomer = ($authUser && $authUser->role === 'customer') ? $authUser : null;
        $checkoutPrefillName = $checkoutCustomer ? trim((string) $checkoutCustomer->full_name) : '';
        $checkoutPrefillPhone = $checkoutCustomer ? $this->normalizeVietnamPhone((string) ($checkoutCustomer->phone ?? '')) : '';
        $checkoutPrefillEmail = $checkoutCustomer ? (string) ($this->normalizeEmail((string) ($checkoutCustomer->email ?? '')) ?? '') : '';
        $checkoutLockContact = $checkoutCustomer !== null;

        $cartItems = collect();
        $stores = DB::table('stores')
            ->where('status', 'active')
            ->orderBy('priority_order')
            ->orderBy('name')
            ->get();
        $checkoutDefaultAddress = null;
        if ($checkoutCustomer) {
            $checkoutDefaultAddress = DB::table('customer_addresses')
                ->where('user_id', $checkoutCustomer->id)
                ->orderByDesc('is_default')
                ->orderByDesc('id')
                ->first();
        }
        $subtotal = 0;

        return $this->frontendView('checkout', compact(
            'cartItems',
            'stores',
            'subtotal',
            'checkoutPrefillName',
            'checkoutPrefillPhone',
            'checkoutPrefillEmail',
            'checkoutLockContact',
            'checkoutDefaultAddress'
        ));
    }

    public function placeOrder(Request $request): JsonResponse
    {
        $authUser = Auth::user();
        $checkoutCustomer = ($authUser && $authUser->role === 'customer') ? $authUser : null;

        $accountPhone = $checkoutCustomer
            ? $this->normalizeVietnamPhone((string) ($checkoutCustomer->phone ?? ''))
            : '';
        $accountEmail = $checkoutCustomer
            ? $this->normalizeEmail((string) ($checkoutCustomer->email ?? ''))
            : null;

        $normalizedPhone = $accountPhone !== ''
            ? $accountPhone
            : $this->normalizeVietnamPhone((string) $request->input('customer_phone', ''));
        $normalizedEmail = $accountEmail ?: $this->normalizeEmail((string) $request->input('customer_email', ''));

        $request->merge([
            'customer_name' => trim((string) $request->input('customer_name', '')),
            'customer_phone' => $normalizedPhone,
            'customer_email' => $normalizedEmail,
            'province_name' => trim((string) $request->input('province_name', '')),
            'district_name' => trim((string) $request->input('district_name', '')),
            'ward_name' => trim((string) $request->input('ward_name', '')),
            'address_line' => trim((string) $request->input('address_line', '')),
            'shipping_address_text' => trim((string) $request->input('shipping_address_text', '')),
            'note' => trim((string) $request->input('note', '')),
            'payment_method' => trim((string) $request->input('payment_method', 'cod')),
        ]);

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'min:2', 'max:150'],
            'customer_phone' => ['required', 'string', 'regex:/^0(3|5|7|8|9)\d{8}$/'],
            'customer_email' => ['nullable', 'email', 'max:190'],
            'delivery_type' => ['required', 'in:delivery,pickup'],
            'province_name' => ['nullable', 'string', 'max:120'],
            'district_name' => ['nullable', 'string', 'max:120'],
            'ward_name' => ['nullable', 'string', 'max:120'],
            'address_line' => ['nullable', 'string', 'max:255'],
            'shipping_address_text' => ['nullable', 'string', 'min:10', 'max:500'],
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
            'payment_method' => ['required', 'in:cod,vietqr'],
            'note' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:99'],
            'items.*.color' => ['nullable', 'string', 'max:120'],
            'items.*.size' => ['nullable', 'string', 'max:120'],
        ], [
            'customer_name.required' => 'Vui lòng nhập họ tên người nhận.',
            'customer_name.min' => 'Họ tên cần có ít nhất 2 ký tự.',
            'customer_phone.required' => 'Vui lòng nhập số điện thoại.',
            'customer_phone.regex' => 'Số điện thoại chưa đúng định dạng di động Việt Nam.',
            'shipping_address_text.min' => 'Địa chỉ nhận hàng cần chi tiết hơn.',
            'store_id.exists' => 'Cửa hàng nhận hàng không hợp lệ.',
            'items.required' => 'Giỏ hàng đang trống.',
            'items.min' => 'Giỏ hàng đang trống.',
        ]);

        if (($validated['delivery_type'] ?? 'delivery') === 'delivery' && blank($validated['shipping_address_text'] ?? null)) {
            return response()->json([
                'message' => 'Vui lòng nhập địa chỉ nhận hàng.',
            ], 422);
        }

        if (($validated['delivery_type'] ?? 'delivery') === 'pickup' && empty($validated['store_id'])) {
            return response()->json([
                'message' => 'Vui lòng chọn cửa hàng nhận hàng.',
            ], 422);
        }

        if (
            $checkoutCustomer
            && ($validated['delivery_type'] ?? 'delivery') === 'delivery'
            && !blank($validated['address_line'] ?? null)
            && !blank($validated['province_name'] ?? null)
            && !blank($validated['district_name'] ?? null)
            && !blank($validated['ward_name'] ?? null)
        ) {
            $this->upsertCustomerDefaultAddress(
                $checkoutCustomer,
                [
                    'province' => trim((string) ($validated['province_name'] ?? '')),
                    'district' => trim((string) ($validated['district_name'] ?? '')),
                    'ward' => trim((string) ($validated['ward_name'] ?? '')),
                    'address_line' => trim((string) ($validated['address_line'] ?? '')),
                ],
                trim((string) ($validated['customer_name'] ?? '')),
                $validated['customer_phone'] ?? ''
            );
        }

        $requestedItems = collect($validated['items']);
        $products = Product::query()
            ->whereIn('id', $requestedItems->pluck('product_id')->map(fn ($id) => (int) $id)->unique()->all())
            ->where('status', 'active')
            ->get()
            ->keyBy('id');

        if ($products->count() !== $requestedItems->pluck('product_id')->unique()->count()) {
            return response()->json([
                'message' => 'Mot hoac nhieu san pham trong gio hang khong con hop le.',
            ], 422);
        }

        if (($validated['payment_method'] ?? 'cod') === 'vietqr' && !$this->hasVietqrConfig()) {
            return response()->json([
                'message' => 'VietQR chua duoc cau hinh day du. Vui long lien he quan tri vien.',
            ], 422);
        }

        $normalizedItems = $this->normalizeCheckoutItems($requestedItems, $products);
        $orderUserId = $checkoutCustomer ? (int) $checkoutCustomer->id : null;
        $validated['user_id'] = $orderUserId;

        $existingPendingInvoice = $this->findActivePendingVietqrInvoiceByPhone($validated['customer_phone']);

        if ($existingPendingInvoice) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đang có một hóa đơn VietQR chưa hoàn thành. Vui lòng thanh toán tiếp hoặc hủy hóa đơn cũ trước khi tạo hóa đơn mới.',
                'existing_invoice' => [
                    'invoice_id' => (int) $existingPendingInvoice->id,
                    'invoice_code' => $existingPendingInvoice->invoice_code,
                    'redirect_url' => URL::signedRoute('frontend.vietqr.payment', ['invoice' => $existingPendingInvoice->id]),
                    'status_url' => URL::signedRoute('frontend.vietqr.payment-status', ['invoice' => $existingPendingInvoice->id]),
                    'cancel_url' => URL::signedRoute('frontend.vietqr.payment-cancel', ['invoice' => $existingPendingInvoice->id]),
                    'expires_at' => Carbon::parse($existingPendingInvoice->created_at)
                        ->addMinutes($this->vietqrInvoiceExpireMinutes())
                        ->getTimestampMs(),
                ],
                'requires_existing_invoice' => true,
            ], 409);
        }

        if (($validated['payment_method'] ?? 'cod') === 'vietqr') {
            $invoice = $this->createPaymentInvoiceRecord($validated, $normalizedItems);

            return response()->json([
                'message' => 'Da tao hoa don chuyen khoan VietQR.',
                'invoice_id' => $invoice->id,
                'invoice_code' => $invoice->invoice_code,
                'redirect_url' => URL::signedRoute('frontend.vietqr.payment', ['invoice' => $invoice->id]),
                'status_url' => URL::signedRoute('frontend.vietqr.payment-status', ['invoice' => $invoice->id]),
                'clear_cart' => false,
            ]);
        }

        $order = $this->createOrderRecord(
            $validated,
            $normalizedItems,
            [
                'payment_status' => 'unpaid',
                'payment_record_status' => 'pending',
                'transaction_code' => null,
                'paid_at' => null,
                'raw_payment_json' => null,
                'status_log_note' => 'Dat hang tu website',
                'status_log_changed_by' => 'frontend',
            ]
        );

        $this->telegramOrderNotificationService->notifyNewOrder($order);

        return response()->json([
            'message' => 'Dat hang thanh cong.',
            'order_id' => $order->id,
            'order_code' => $order->order_code,
            'redirect_url' => URL::signedRoute('frontend.order-success', ['order' => $order->id]),
            'clear_cart' => true,
        ]);
    }

    private function normalizeVietnamPhone(string $phone): string
    {
        $phone = preg_replace('/[^\d+]/', '', trim($phone)) ?? '';

        if (Str::startsWith($phone, '+84')) {
            $phone = '0' . substr($phone, 3);
        } elseif (Str::startsWith($phone, '84')) {
            $phone = '0' . substr($phone, 2);
        }

        return $phone;
    }

    private function normalizeCheckoutItems(Collection $requestedItems, Collection $products): Collection
    {
        return $requestedItems->map(function (array $item) use ($products) {
            /** @var Product $product */
            $product = $products->get((int) $item['product_id']);
            $qty = max(1, min(99, (int) $item['qty']));
            $variantParts = array_filter([
                trim((string) ($item['color'] ?? '')),
                trim((string) ($item['size'] ?? '')),
            ]);

            return [
                'product_id' => (int) $product->id,
                'variant_id' => 0,
                'sku_snapshot' => $product->sku,
                'product_name_snapshot' => $product->name,
                'variant_name_snapshot' => count($variantParts) ? implode(' | ', $variantParts) : null,
                'unit_price' => (float) $product->price,
                'qty' => $qty,
                'discount_amount' => 0,
                'line_total' => (float) $product->price * $qty,
            ];
        })->values();
    }

    private function nextDocumentCode(string $table, string $prefix): string
    {
        $nextId = ((int) (DB::table($table)->max('id') ?? 1000)) + 1;

        return $prefix . '-' . now()->format('Ymd') . '-' . $nextId;
    }

    private function createPaymentInvoiceRecord(array $validated, Collection $normalizedItems): object
    {
        return DB::transaction(function () use ($validated, $normalizedItems) {
            $invoiceCode = $this->nextDocumentCode('payment_invoices', 'INV');
            $transferPrefix = trim((string) ($this->vietqrSettings()['transfer_prefix'] ?? 'TT'));
            $transferContent = $transferPrefix . $this->formatTransferReferenceCode($invoiceCode);
            $subtotal = (float) $normalizedItems->sum('line_total');
            $invoiceId = DB::table('payment_invoices')->insertGetId([
                'invoice_code' => $invoiceCode,
                'order_id' => null,
                'user_id' => !empty($validated['user_id']) ? (int) $validated['user_id'] : null,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $validated['customer_email'] ?? null,
                'delivery_type' => $validated['delivery_type'],
                'store_id' => !empty($validated['store_id']) ? (int) $validated['store_id'] : null,
                'shipping_address_text' => $validated['shipping_address_text'] ?? null,
                'payment_method' => 'vietqr',
                'invoice_status' => 'pending_payment',
                'payment_status' => 'unpaid',
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'shipping_fee' => 0,
                'total_amount' => $subtotal,
                'transfer_content' => $transferContent,
                'note' => $validated['note'] ?? null,
                'items_json' => json_encode($normalizedItems->values()->all(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'raw_payment_json' => null,
                'paid_at' => null,
                'converted_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return DB::table('payment_invoices')->where('id', $invoiceId)->first();
        });
    }

    private function createOrderRecord(array $validated, Collection $normalizedItems, array $paymentMeta = []): object
    {
        return DB::transaction(function () use ($validated, $normalizedItems, $paymentMeta) {
            $orderCode = $this->nextDocumentCode('orders', 'ODR');
            $subtotal = (float) $normalizedItems->sum('line_total');
            $paymentStatus = (string) ($paymentMeta['payment_status'] ?? 'unpaid');
            $paymentRecordStatus = (string) ($paymentMeta['payment_record_status'] ?? 'pending');
            $transactionCode = $paymentMeta['transaction_code'] ?? null;
            $paidAt = $paymentMeta['paid_at'] ?? null;
            $rawPaymentJson = $paymentMeta['raw_payment_json'] ?? null;
            $statusLogNote = (string) ($paymentMeta['status_log_note'] ?? 'Dat hang tu website');
            $statusLogChangedBy = (string) ($paymentMeta['status_log_changed_by'] ?? 'frontend');

            $orderId = DB::table('orders')->insertGetId([
                'order_code' => $orderCode,
                'customer_tracking_token' => Order::generateCustomerTrackingToken(),
                'user_id' => !empty($validated['user_id']) ? (int) $validated['user_id'] : null,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $validated['customer_email'] ?? null,
                'delivery_type' => $validated['delivery_type'],
                'store_id' => !empty($validated['store_id']) ? (int) $validated['store_id'] : null,
                'shipping_address_text' => $validated['shipping_address_text'] ?? null,
                'payment_method' => $validated['payment_method'] ?? 'cod',
                'order_status' => 'pending_verification',
                'verified_at' => null,
                'payment_status' => $paymentStatus,
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'promotion_id' => null,
                'coupon_code' => null,
                'shipping_fee' => 0,
                'total_amount' => $subtotal,
                'note' => $validated['note'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('order_items')->insert(
                $normalizedItems->map(fn (array $item) => array_merge($item, [
                    'order_id' => $orderId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]))->all()
            );

            DB::table('order_status_logs')->insert([
                'order_id' => $orderId,
                'from_status' => null,
                'to_status' => 'pending_verification',
                'changed_by' => $statusLogChangedBy,
                'note' => $statusLogNote,
                'created_at' => now(),
            ]);

            DB::table('order_payments')->insert([
                'order_id' => $orderId,
                'payment_method' => $validated['payment_method'] ?? 'cod',
                'transaction_code' => $transactionCode,
                'amount' => $subtotal,
                'status' => $paymentRecordStatus,
                'paid_at' => $paidAt,
                'raw_response_json' => $rawPaymentJson ? json_encode($rawPaymentJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return DB::table('orders')->where('id', $orderId)->first();
        });
    }

    private function maskPhoneNumber(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?: '';

        if ($digits === '') {
            return 'Khach than thiet';
        }

        if (strlen($digits) <= 5) {
            return $digits;
        }

        return substr($digits, 0, 2) . str_repeat('*', max(strlen($digits) - 5, 3)) . substr($digits, -3);
    }

    private function parseProductSizeGuideRows(string $content): array
    {
        $rows = [];

        foreach (preg_split("/\r\n|\n|\r/", trim($content)) ?: [] as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $columns = preg_split('/\t+|\s*\|\s*|\s{2,}/u', $line) ?: [];
            $columns = array_values(array_filter(array_map('trim', $columns), static fn ($value) => $value !== ''));

            if (count($columns) < 3) {
                continue;
            }

            if (count($rows) === 0) {
                $header = mb_strtolower(implode(' ', array_slice($columns, 0, 3)), 'UTF-8');
                if (str_contains($header, 'cân nặng') && str_contains($header, 'size') && str_contains($header, 'gợi ý')) {
                    continue;
                }
            }

            $rows[] = [
                'weight' => $columns[0],
                'size' => $columns[1],
                'suggestion' => implode(' | ', array_slice($columns, 2)),
            ];
        }

        return $rows;
    }

    public function orderSuccess(int $order): View
    {
        $order = DB::table('orders')->where('id', $order)->first();
        abort_unless($order, 404);

        if ($order->payment_method === 'vietqr' && $order->payment_status !== 'paid') {
            abort(404);
        }

        $selectedStore = null;
        if (!empty($order->store_id)) {
            $selectedStore = DB::table('stores')->where('id', $order->store_id)->first();
        }

        $orderItems = DB::table('order_items')
            ->where('order_id', $order->id)
            ->orderBy('id')
            ->get();

        $productIds = $orderItems->pluck('product_id')->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();
        $imageByProduct = $this->primaryImagesByProductIds($productIds, 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?auto=format&fit=crop&w=500&q=80');
        $orderItems = $orderItems->map(function ($item) use ($imageByProduct) {
            $item->image_url = $imageByProduct[$item->product_id] ?? 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?auto=format&fit=crop&w=500&q=80';

            return $item;
        });

        $vietqrPayment = null;

        if ($order->payment_method === 'vietqr') {
            $vietqrPayment = $this->buildVietqrPaymentData($order);
        }

        return $this->frontendView('order-success', compact('order', 'orderItems', 'selectedStore', 'vietqrPayment'));
    }

    public function orderTracking(string $token): View
    {
        $normalizedToken = strtoupper(trim($token));
        if (preg_match('/^[A-Z0-9]{10,24}$/', $normalizedToken) !== 1) {
            abort(404);
        }

        $order = Order::query()
            ->where('customer_tracking_token', $normalizedToken)
            ->firstOrFail();

        $selectedStore = null;
        if (!empty($order->store_id)) {
            $selectedStore = DB::table('stores')->where('id', $order->store_id)->first();
        }

        $orderItems = DB::table('order_items')
            ->where('order_id', $order->id)
            ->orderBy('id')
            ->get();

        $trackingSettings = DB::table('site_settings')
            ->whereIn('setting_key', ['site_name', 'site_logo_url', 'contact_phone', 'hotline', 'contact_address', 'zalo_url'])
            ->pluck('setting_value', 'setting_key');

        $trackingLink = URL::temporarySignedRoute('frontend.order-tracking', now()->addDays(7), ['token' => $order->customer_tracking_token]);
        $qrRenderer = new ImageRenderer(
            new RendererStyle(180, 1),
            new SvgImageBackEnd()
        );
        $qrSvg = (new Writer($qrRenderer))->writeString($trackingLink);

        return $this->frontendView('order-tracking', compact('order', 'orderItems', 'selectedStore', 'trackingSettings', 'trackingLink', 'qrSvg'));
    }

    public function vietqrPaymentPage(int $invoice): View
    {
        $invoice = DB::table('payment_invoices')->where('id', $invoice)->first();
        abort_unless($invoice, 404);
        abort_unless($invoice->payment_method === 'vietqr', 404);
        $invoice = $this->expireVietqrInvoiceIfNeeded($invoice);

        $selectedStore = null;
        if (!empty($invoice->store_id)) {
            $selectedStore = DB::table('stores')->where('id', $invoice->store_id)->first();
        }

        $invoiceItems = $this->invoiceItems($invoice);
        $productIds = $invoiceItems->pluck('product_id')->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();
        $imageByProduct = $this->primaryImagesByProductIds($productIds, 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?auto=format&fit=crop&w=500&q=80');
        $slugByProduct = $productIds !== []
            ? DB::table('products')->whereIn('id', $productIds)->pluck('slug', 'id')
            : collect();
        $invoiceItems = $invoiceItems->map(function ($item) use ($imageByProduct, $slugByProduct) {
            $productId = (int) ($item->product_id ?? 0);
            $productSlug = (string) ($slugByProduct[$productId] ?? '');
            $item->image_url = $imageByProduct[$productId] ?? 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?auto=format&fit=crop&w=500&q=80';
            $item->product_url = $productSlug !== ''
                ? route('frontend.product-detail', ['slug' => $productSlug])
                : null;

            return $item;
        });

        $linkedOrder = !empty($invoice->order_id)
            ? DB::table('orders')->where('id', $invoice->order_id)->first()
            : null;
        $vietqrPayment = $invoice->invoice_status === 'expired' ? null : $this->buildVietqrPaymentData($invoice);
        $successUrl = $linkedOrder && $invoice->payment_status === 'paid'
            ? URL::signedRoute('frontend.order-success', ['order' => $linkedOrder->id])
            : null;

        return $this->frontendView('vietqr-payment', compact('invoice', 'invoiceItems', 'selectedStore', 'vietqrPayment', 'successUrl', 'linkedOrder'));
    }

    public function vietqrPaymentStatus(int $invoice): JsonResponse
    {
        $invoice = DB::table('payment_invoices')->where('id', $invoice)->first();
        abort_unless($invoice, 404);
        abort_unless($invoice->payment_method === 'vietqr', 404);
        $invoice = $this->expireVietqrInvoiceIfNeeded($invoice);

        $linkedOrder = !empty($invoice->order_id)
            ? DB::table('orders')->where('id', $invoice->order_id)->first()
            : null;

        return response()->json([
            'success' => true,
            'invoice_id' => (int) $invoice->id,
            'invoice_code' => $invoice->invoice_code,
            'invoice_status' => $invoice->invoice_status,
            'payment_status' => $invoice->payment_status,
            'is_expired' => $invoice->invoice_status === 'expired',
            'is_cancelled' => $invoice->invoice_status === 'cancelled',
            'amount' => (float) $invoice->total_amount,
            'success_url' => $linkedOrder && $invoice->payment_status === 'paid'
                ? URL::signedRoute('frontend.order-success', ['order' => $linkedOrder->id])
                : null,
        ]);
    }

    public function vietqrPaymentCancel(Request $request, int $invoice): JsonResponse
    {
        $invoice = DB::table('payment_invoices')->where('id', $invoice)->first();
        abort_unless($invoice, 404);
        abort_unless($invoice->payment_method === 'vietqr', 404);
        $invoice = $this->expireVietqrInvoiceIfNeeded($invoice);

        if (($invoice->payment_status ?? '') === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Hóa đơn này đã được thanh toán, không thể hủy.',
            ], 422);
        }

        if (($invoice->invoice_status ?? '') === 'expired') {
            return response()->json([
                'success' => true,
                'message' => 'Hóa đơn đã hết hạn.',
                'invoice_status' => 'expired',
            ]);
        }

        if (($invoice->invoice_status ?? '') === 'cancelled') {
            return response()->json([
                'success' => true,
                'message' => 'Hóa đơn đã được hủy trước đó.',
                'invoice_status' => 'cancelled',
            ]);
        }

        DB::table('payment_invoices')
            ->where('id', $invoice->id)
            ->update([
                'invoice_status' => 'cancelled',
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã hủy hóa đơn VietQR.',
            'invoice_status' => 'cancelled',
            'redirect_url' => route('frontend.checkout'),
        ]);
    }

    public function vietqrPaymentDownload(int $invoice)
    {
        $invoice = DB::table('payment_invoices')->where('id', $invoice)->first();
        abort_unless($invoice, 404);
        abort_unless($invoice->payment_method === 'vietqr', 404);
        $invoice = $this->expireVietqrInvoiceIfNeeded($invoice);
        abort_if(in_array((string) $invoice->invoice_status, ['expired', 'cancelled'], true), 410);

        $vietqrPayment = $this->buildVietqrPaymentData($invoice);
        abort_unless(!empty($vietqrPayment['qr_url']), 404);

        $response = Http::timeout(15)->get($vietqrPayment['qr_url']);
        abort_unless($response->successful(), 502);

        $downloadName = $this->sanitizeDownloadFilename((string) ($invoice->transfer_content ?? $invoice->invoice_code), 'png');

        return response($response->body(), 200, [
            'Content-Type' => $response->header('Content-Type', 'image/png'),
            'Content-Disposition' => 'attachment; filename="' . $downloadName . '"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    public function sepayWebhook(Request $request, SePayWebhookService $sePayWebhookService): JsonResponse
    {
        $payload = $this->normalizeSePayPayload($request->all());
        $logId = $this->createSePayWebhookLog($request, $payload);

        if (!$sePayWebhookService->isValidRequest(
            $request->header('X-Secret-Key'),
            $request->header('Authorization')
        )) {
            $this->updateSePayWebhookLog($logId, [
                'http_status' => 401,
                'status' => 'unauthorized',
                'message' => 'Secret key hoac Authorization khong hop le.',
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        $transactionId = trim((string) ($payload['transaction_id'] ?? ''));

        if ($transactionId === '') {
            $this->updateSePayWebhookLog($logId, [
                'http_status' => 422,
                'status' => 'missing_transaction_id',
                'message' => 'Khong co transaction_id trong payload.',
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Missing transaction_id.',
            ], 422);
        }

        $this->processSePayWebhook($payload, $logId);

        return response()->json([
            'success' => true,
        ]);
    }

    private function hasVietqrConfig(): bool
    {
        $settings = $this->vietqrSettings();

        return $settings['bank_bin'] !== ''
            && $settings['account_no'] !== ''
            && $settings['account_name'] !== '';
    }

    private function vietqrSettings(): array
    {
        $settings = DB::table('site_settings')
            ->whereIn('setting_key', [
                'payment_vietqr_bank_bin',
                'payment_vietqr_bank_name',
                'payment_vietqr_account_no',
                'payment_vietqr_account_name',
                'payment_vietqr_template',
                'payment_vietqr_transfer_prefix',
                'payment_vietqr_expire_minutes',
            ])
            ->pluck('setting_value', 'setting_key');

        return [
            'bank_bin' => trim((string) ($settings['payment_vietqr_bank_bin'] ?? '')),
            'bank_name' => trim((string) ($settings['payment_vietqr_bank_name'] ?? '')),
            'account_no' => preg_replace('/\s+/', '', trim((string) ($settings['payment_vietqr_account_no'] ?? ''))) ?: '',
            'account_name' => trim((string) ($settings['payment_vietqr_account_name'] ?? '')),
            'template' => trim((string) ($settings['payment_vietqr_template'] ?? '')) ?: 'compact2',
            'transfer_prefix' => trim((string) ($settings['payment_vietqr_transfer_prefix'] ?? 'TT')) ?: 'TT',
            'expire_minutes' => max(1, (int) ($settings['payment_vietqr_expire_minutes'] ?? 30)),
        ];
    }

    private function vietqrInvoiceExpireMinutes(): int
    {
        return max(1, (int) ($this->vietqrSettings()['expire_minutes'] ?? 30));
    }

    private function isVietqrInvoiceExpired(object $invoice): bool
    {
        if (($invoice->payment_status ?? '') === 'paid') {
            return false;
        }

        $currentStatus = (string) ($invoice->invoice_status ?? '');
        if (in_array($currentStatus, ['completed', 'cancelled'], true)) {
            return false;
        }

        if ($currentStatus === 'expired') {
            return true;
        }

        try {
            return Carbon::parse($invoice->created_at)
                ->addMinutes($this->vietqrInvoiceExpireMinutes())
                ->isPast();
        } catch (\Throwable $exception) {
            return false;
        }
    }

    private function findActivePendingVietqrInvoiceByPhone(string $phone): ?object
    {
        $normalizedPhone = $this->normalizeVietnamPhone($phone);

        if ($normalizedPhone === '') {
            return null;
        }

        $candidates = DB::table('payment_invoices')
            ->where('payment_method', 'vietqr')
            ->where('customer_phone', $normalizedPhone)
            ->where('payment_status', 'unpaid')
            ->whereIn('invoice_status', ['pending_payment', 'expired'])
            ->orderByDesc('id')
            ->get();

        foreach ($candidates as $candidate) {
            $candidate = $this->expireVietqrInvoiceIfNeeded($candidate);

            if ((string) ($candidate->invoice_status ?? '') === 'pending_payment') {
                return $candidate;
            }
        }

        return null;
    }

    private function expireVietqrInvoiceIfNeeded(object $invoice): object
    {
        if (!$this->isVietqrInvoiceExpired($invoice)) {
            return $invoice;
        }

        if ((string) ($invoice->invoice_status ?? '') !== 'expired') {
            DB::table('payment_invoices')
                ->where('id', $invoice->id)
                ->update([
                    'invoice_status' => 'expired',
                    'updated_at' => now(),
                ]);

            $invoice = DB::table('payment_invoices')->where('id', $invoice->id)->first() ?? $invoice;
        }

        return $invoice;
    }

    private function buildVietqrPaymentData(object $order): ?array
    {
        $settings = $this->vietqrSettings();

        if ($settings['bank_bin'] === '' || $settings['account_no'] === '' || $settings['account_name'] === '') {
            return null;
        }

        $transferContent = $this->resolveTransferContentForPayment($order, $settings);
        $qrUrl = sprintf(
            'https://img.vietqr.io/image/%s-%s-%s.png?amount=%s&addInfo=%s&accountName=%s',
            rawurlencode($settings['bank_bin']),
            rawurlencode($settings['account_no']),
            rawurlencode($settings['template']),
            rawurlencode((string) round((float) $order->total_amount)),
            rawurlencode($transferContent),
            rawurlencode($settings['account_name'])
        );

        return [
            'qr_url' => $qrUrl,
            'bank_name' => $settings['bank_name'] !== '' ? $settings['bank_name'] : $settings['bank_bin'],
            'bank_bin' => $settings['bank_bin'],
            'account_no' => $settings['account_no'],
            'account_name' => $settings['account_name'],
            'amount' => (float) ($order->total_amount ?? 0),
            'transfer_content' => $transferContent,
        ];
    }

    private function resolveTransferContentForPayment(object $record, array $settings): string
    {
        $currentContent = trim((string) ($record->transfer_content ?? ''));
        if ($currentContent !== '') {
            return $currentContent;
        }

        $transferPrefix = trim((string) ($settings['transfer_prefix'] ?? 'TT'));
        $referenceCode = trim((string) ($record->invoice_code ?? $record->order_code ?? ''));

        return $transferPrefix . $this->formatTransferReferenceCode($referenceCode);
    }

    private function formatTransferReferenceCode(string $referenceCode): string
    {
        return preg_replace('/[^A-Za-z0-9]/', '', strtoupper(trim($referenceCode))) ?: '';
    }

    private function invoiceItems(object $invoice): Collection
    {
        $items = json_decode((string) ($invoice->items_json ?? '[]'), true);

        if (!is_array($items)) {
            return collect();
        }

        return collect($items)->map(function ($item) {
            return (object) $item;
        });
    }

    private function processSePayWebhook(array $payload, ?int $logId = null): void
    {
        $transactionId = trim((string) ($payload['transaction_id'] ?? ''));
        $transferType = $this->normalizeSePayTransferType((string) ($payload['transfer_type'] ?? ''));
        $amount = (float) ($payload['amount'] ?? 0);
        $content = trim((string) ($payload['content'] ?? ''));
        $referenceCode = trim((string) ($payload['reference_code'] ?? ''));
        $gateway = trim((string) ($payload['gateway'] ?? ''));
        $accountNumber = trim((string) ($payload['account_number'] ?? ''));
        $transactionDate = $this->parseSePayTransactionDate((string) ($payload['transaction_date'] ?? '')) ?? now();

        DB::transaction(function () use (
            $transactionId,
            $transferType,
            $amount,
            $content,
            $referenceCode,
            $gateway,
            $accountNumber,
            $payload,
            $transactionDate,
            $logId
        ) {
            $inserted = DB::table('sepay_webhook_receipts')->insertOrIgnore([
                'transaction_id' => $transactionId,
                'invoice_id' => null,
                'order_id' => null,
                'order_payment_id' => null,
                'gateway' => $gateway !== '' ? $gateway : null,
                'account_number' => $accountNumber !== '' ? $accountNumber : null,
                'reference_code' => $referenceCode !== '' ? $referenceCode : null,
                'amount' => $amount,
                'transfer_type' => $transferType !== '' ? $transferType : null,
                'content' => $content !== '' ? $content : null,
                'payload_json' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'processed_at' => now(),
            ]);

            if ($inserted === 0) {
                $this->updateSePayWebhookLog($logId, [
                    'transaction_id' => $transactionId ?: null,
                    'status' => 'duplicate_transaction',
                    'message' => 'Giao dich nay da duoc xu ly truoc do.',
                ]);
                return;
            }

            if ($transferType !== 'credit' || $amount <= 0) {
                $this->updateSePayWebhookLog($logId, [
                    'transaction_id' => $transactionId ?: null,
                    'status' => 'ignored_transfer',
                    'message' => 'Webhook khong phai giao dich tien vao hop le.',
                ]);
                return;
            }

            $paymentReference = $this->extractPaymentReferenceFromTransferContent($content);

            if ($paymentReference === '') {
                $this->updateSePayWebhookLog($logId, [
                    'transaction_id' => $transactionId ?: null,
                    'status' => 'reference_not_found',
                    'message' => 'Khong tach duoc ma hoa don hoac ma don tu noi dung chuyen khoan.',
                ]);
                return;
            }

            $invoice = $this->findInvoiceByPaymentReference($paymentReference, $content);

            if ($invoice) {
                $invoice = $this->expireVietqrInvoiceIfNeeded($invoice);

                DB::table('sepay_webhook_receipts')
                    ->where('transaction_id', $transactionId)
                    ->update([
                        'invoice_id' => $invoice->id,
                    ]);

                if ($invoice->invoice_status === 'expired') {
                    $this->updateSePayWebhookLog($logId, [
                        'invoice_id' => $invoice->id,
                        'transaction_id' => $transactionId ?: null,
                        'status' => 'invoice_expired',
                        'message' => 'Hoa don VietQR da het han, khong chuyen thanh don hang.',
                    ]);
                    return;
                }

                if (abs(((float) $invoice->total_amount) - $amount) >= 1) {
                    $this->updateSePayWebhookLog($logId, [
                        'invoice_id' => $invoice->id,
                        'transaction_id' => $transactionId ?: null,
                        'status' => 'amount_mismatch',
                        'message' => 'So tien chuyen khoan khong khop voi tong hoa don.',
                    ]);
                    return;
                }

                $order = !empty($invoice->order_id)
                    ? DB::table('orders')->where('id', $invoice->order_id)->first()
                    : null;

                if (!$order) {
                    $order = $this->createOrderFromInvoice($invoice, [
                        'transaction_code' => $referenceCode !== '' ? $referenceCode : $transactionId,
                        'amount' => $amount,
                        'paid_at' => $transactionDate,
                        'raw_payment_json' => $payload,
                    ]);
                }

                $this->autoVerifyPaidOrder(
                    (int) $order->id,
                    'sepay-webhook',
                    'SePay xac nhan thanh toan thanh cong va tu dong xac minh don hang'
                );

                $payment = DB::table('order_payments')
                    ->where('order_id', $order->id)
                    ->where('payment_method', 'vietqr')
                    ->orderByDesc('id')
                    ->first();

                DB::table('sepay_webhook_receipts')
                    ->where('transaction_id', $transactionId)
                    ->update([
                        'invoice_id' => $invoice->id,
                        'order_id' => $order->id,
                        'order_payment_id' => $payment?->id,
                    ]);

                $this->updateSePayWebhookLog($logId, [
                    'invoice_id' => $invoice->id,
                    'order_id' => $order->id,
                    'transaction_id' => $transactionId ?: null,
                    'status' => 'paid_and_converted',
                    'message' => 'Da xac nhan thanh toan va tao don hang tu hoa don.',
                ]);

                return;
            }

            $orderCode = $this->extractOrderCodeFromTransferContent($paymentReference);

            if ($orderCode === '') {
                $this->updateSePayWebhookLog($logId, [
                    'transaction_id' => $transactionId ?: null,
                    'status' => 'invoice_not_found',
                    'message' => 'Khong tim thay hoa don tu noi dung chuyen khoan.',
                ]);
                return;
            }

            $order = DB::table('orders')
                ->where('order_code', $orderCode)
                ->where('payment_method', 'vietqr')
                ->first();

            if (!$order) {
                $this->updateSePayWebhookLog($logId, [
                    'transaction_id' => $transactionId ?: null,
                    'status' => 'order_not_found',
                    'message' => 'Khong tim thay don hang VietQR phu hop.',
                ]);
                return;
            }

            $payment = DB::table('order_payments')
                ->where('order_id', $order->id)
                ->where('payment_method', 'vietqr')
                ->orderByDesc('id')
                ->first();

            DB::table('sepay_webhook_receipts')
                ->where('transaction_id', $transactionId)
                ->update([
                    'order_id' => $order->id,
                    'order_payment_id' => $payment?->id,
                ]);

            if (!$payment || $order->payment_status === 'paid') {
                $this->updateSePayWebhookLog($logId, [
                    'order_id' => $order->id,
                    'transaction_id' => $transactionId ?: null,
                    'status' => 'order_already_paid',
                    'message' => 'Don hang da thanh toan hoac khong co ban ghi thanh toan cho don.',
                ]);
                return;
            }

            if (abs(((float) $order->total_amount) - $amount) >= 1) {
                $this->updateSePayWebhookLog($logId, [
                    'order_id' => $order->id,
                    'transaction_id' => $transactionId ?: null,
                    'status' => 'order_amount_mismatch',
                    'message' => 'So tien chuyen khoan khong khop voi tong don hang.',
                ]);
                return;
            }

            DB::table('order_payments')
                ->where('id', $payment->id)
                ->update([
                    'transaction_code' => $referenceCode !== '' ? $referenceCode : $transactionId,
                    'amount' => $amount,
                    'status' => 'paid',
                    'paid_at' => $transactionDate,
                    'raw_response_json' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'updated_at' => now(),
                ]);

            DB::table('orders')
                ->where('id', $order->id)
                ->update([
                    'payment_status' => 'paid',
                    'updated_at' => now(),
                ]);

            DB::table('order_status_logs')->insert([
                'order_id' => $order->id,
                'from_status' => $order->order_status,
                'to_status' => $order->order_status,
                'changed_by' => 'sepay-webhook',
                'note' => 'SePay xac nhan thanh toan thanh cong',
                'created_at' => now(),
            ]);

            $this->autoVerifyPaidOrder(
                (int) $order->id,
                'sepay-webhook',
                'SePay xac nhan thanh toan thanh cong va tu dong xac minh don hang'
            );

            $this->updateSePayWebhookLog($logId, [
                'order_id' => $order->id,
                'transaction_id' => $transactionId ?: null,
                'status' => 'paid_existing_order',
                'message' => 'Da xac nhan thanh toan cho don hang VietQR cu.',
            ]);
        });
    }

    private function createSePayWebhookLog(Request $request, array $payload): ?int
    {
        try {
            $authorization = trim((string) $request->header('Authorization'));
            $secretHeader = trim((string) $request->header('X-Secret-Key'));

            return DB::table('sepay_webhook_logs')->insertGetId([
                'invoice_id' => null,
                'order_id' => null,
                'transaction_id' => trim((string) ($payload['transaction_id'] ?? '')) ?: null,
                'http_status' => 200,
                'status' => 'received',
                'message' => 'Da nhan webhook SePay.',
                'auth_type' => $this->resolveSePayAuthType($secretHeader, $authorization),
                'secret_preview' => $this->maskSePaySecretPreview($secretHeader !== '' ? $secretHeader : $authorization),
                'headers_json' => json_encode($this->extractSePayDebugHeaders($request), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'payload_json' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'created_at' => now(),
            ]);
        } catch (\Throwable $exception) {
            return null;
        }
    }

    private function updateSePayWebhookLog(?int $logId, array $data): void
    {
        if (!$logId) {
            return;
        }

        try {
            DB::table('sepay_webhook_logs')
                ->where('id', $logId)
                ->update(array_filter([
                    'invoice_id' => $data['invoice_id'] ?? null,
                    'order_id' => $data['order_id'] ?? null,
                    'transaction_id' => $data['transaction_id'] ?? null,
                    'http_status' => $data['http_status'] ?? null,
                    'status' => $data['status'] ?? null,
                    'message' => $data['message'] ?? null,
                ], static fn ($value) => $value !== null));
        } catch (\Throwable $exception) {
            // Ignore debug-log failures so payment flow is not interrupted.
        }
    }

    private function resolveSePayAuthType(string $secretHeader, string $authorizationHeader): string
    {
        if ($secretHeader !== '') {
            return 'x-secret-key';
        }

        if (preg_match('/^Apikey\s+/i', $authorizationHeader) === 1) {
            return 'apikey';
        }

        if (preg_match('/^Bearer\s+/i', $authorizationHeader) === 1) {
            return 'bearer';
        }

        return 'none';
    }

    private function maskSePaySecretPreview(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^(Apikey|Bearer)\s+(.+)$/i', $value, $matches) === 1) {
            $token = trim((string) ($matches[2] ?? ''));
            return $matches[1] . ' ' . substr($token, 0, 4) . '***' . substr($token, -2);
        }

        return substr($value, 0, 4) . '***' . substr($value, -2);
    }

    private function extractSePayDebugHeaders(Request $request): array
    {
        return array_filter([
            'x-secret-key' => $this->maskSePaySecretPreview((string) $request->header('X-Secret-Key')),
            'authorization' => $this->maskSePaySecretPreview((string) $request->header('Authorization')),
            'user-agent' => $request->userAgent(),
            'content-type' => $request->header('Content-Type'),
            'x-forwarded-for' => $request->header('X-Forwarded-For'),
        ], static fn ($value) => $value !== null && $value !== '');
    }

    private function createOrderFromInvoice(object $invoice, array $paymentMeta): object
    {
        $normalizedItems = $this->invoiceItems($invoice)->map(function ($item) {
            return [
                'product_id' => (int) ($item->product_id ?? 0),
                'variant_id' => (int) ($item->variant_id ?? 0),
                'sku_snapshot' => $item->sku_snapshot ?? null,
                'product_name_snapshot' => (string) ($item->product_name_snapshot ?? ''),
                'variant_name_snapshot' => $item->variant_name_snapshot ?? null,
                'unit_price' => (float) ($item->unit_price ?? 0),
                'qty' => (int) ($item->qty ?? 1),
                'discount_amount' => (float) ($item->discount_amount ?? 0),
                'line_total' => (float) ($item->line_total ?? 0),
            ];
        })->values();

        $order = $this->createOrderRecord([
            'user_id' => !empty($invoice->user_id) ? (int) $invoice->user_id : null,
            'customer_name' => $invoice->customer_name,
            'customer_phone' => $invoice->customer_phone,
            'customer_email' => $invoice->customer_email,
            'delivery_type' => $invoice->delivery_type,
            'store_id' => $invoice->store_id,
            'shipping_address_text' => $invoice->shipping_address_text,
            'payment_method' => $invoice->payment_method,
            'note' => $invoice->note,
        ], $normalizedItems, [
            'payment_status' => 'paid',
            'payment_record_status' => 'paid',
            'transaction_code' => $paymentMeta['transaction_code'] ?? null,
            'paid_at' => $paymentMeta['paid_at'] ?? now(),
            'raw_payment_json' => $paymentMeta['raw_payment_json'] ?? null,
            'status_log_note' => 'Tao don hang tu hoa don da thanh toan ' . $invoice->invoice_code,
            'status_log_changed_by' => 'sepay-webhook',
        ]);

        $this->telegramOrderNotificationService->notifyNewOrder($order);

        DB::table('payment_invoices')
            ->where('id', $invoice->id)
            ->update([
                'order_id' => $order->id,
                'invoice_status' => 'completed',
                'payment_status' => 'paid',
                'paid_at' => $paymentMeta['paid_at'] ?? now(),
                'converted_at' => now(),
                'raw_payment_json' => json_encode($paymentMeta['raw_payment_json'] ?? null, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'updated_at' => now(),
            ]);

        return $order;
    }

    private function extractPaymentReferenceFromTransferContent(string $content): string
    {
        if ($content === '') {
            return '';
        }

        if (preg_match('/(INV-?\d{8}-?\d+|ODR-?\d{8}-?\d+)/i', strtoupper($content), $matches) === 1) {
            return $this->formatTransferReferenceCode((string) ($matches[1] ?? $matches[0]));
        }

        return '';
    }

    private function extractOrderCodeFromTransferContent(string $content): string
    {
        if ($content === '') {
            return '';
        }

        if (preg_match('/ODR-?\d{8}-?\d+/i', strtoupper($content), $matches) === 1) {
            return $this->formatTransferReferenceCode((string) ($matches[0] ?? ''));
        }

        return '';
    }

    private function findInvoiceByPaymentReference(string $paymentReference, string $content): ?object
    {
        $normalizedReference = $this->formatTransferReferenceCode($paymentReference);
        $normalizedContent = $this->formatTransferReferenceCode($content);

        return DB::table('payment_invoices')
            ->where('payment_method', 'vietqr')
            ->orderByDesc('id')
            ->get()
            ->first(function ($invoice) use ($normalizedReference, $normalizedContent) {
                $invoiceCode = $this->formatTransferReferenceCode((string) ($invoice->invoice_code ?? ''));
                $transferContent = $this->formatTransferReferenceCode((string) ($invoice->transfer_content ?? ''));

                return $invoiceCode === $normalizedReference
                    || $transferContent === $normalizedContent
                    || $transferContent === $normalizedReference;
            });
    }

    private function sanitizeDownloadFilename(string $value, string $extension = 'png'): string
    {
        $base = trim($value);
        $base = preg_replace('/[\\\\\\/:*?"<>|]+/', '-', $base) ?? '';
        $base = preg_replace('/\s+/', '-', $base) ?? '';
        $base = trim($base, '.- ');

        if ($base === '') {
            $base = 'vietqr';
        }

        return $base . '.' . ltrim($extension, '.');
    }

    private function normalizeSePayPayload(array $payload): array
    {
        $transactionId = trim((string) ($payload['transaction_id'] ?? $payload['id'] ?? ''));
        $referenceCode = trim((string) ($payload['reference_code'] ?? $payload['referenceCode'] ?? ''));

        if ($transactionId === '' && $referenceCode !== '') {
            $transactionId = $referenceCode;
        }

        return [
            'transaction_id' => $transactionId,
            'transaction_date' => (string) ($payload['transaction_date'] ?? $payload['transactionDate'] ?? ''),
            'account_number' => (string) ($payload['account_number'] ?? $payload['accountNumber'] ?? ''),
            'transfer_type' => (string) ($payload['transfer_type'] ?? $payload['transferType'] ?? ''),
            'amount' => $payload['amount'] ?? $payload['transferAmount'] ?? 0,
            'content' => (string) ($payload['content'] ?? $payload['transaction_content'] ?? ''),
            'reference_code' => $referenceCode,
            'gateway' => (string) ($payload['gateway'] ?? ''),
            'raw_payload' => $payload,
        ];
    }

    private function normalizeSePayTransferType(string $value): string
    {
        $value = strtolower(trim($value));

        return match ($value) {
            'in', 'credit' => 'credit',
            'out', 'debit' => 'debit',
            default => $value,
        };
    }

    private function parseSePayTransactionDate(string $value): ?Carbon
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $exception) {
            return null;
        }
    }

    private function footerGroups(): Collection
    {
        return FooterLink::query()
            ->where('is_active', 1)
            ->orderBy('group_name')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('group_name');
    }

    private function footerInfo(): array
    {
        $settings = DB::table('site_settings')
            ->whereIn('setting_key', ['site_name', 'site_slogan', 'hotline', 'contact_phone', 'contact_email'])
            ->pluck('setting_value', 'setting_key');

        $siteName = $settings['site_name'] ?? 'Shop Nội Y';
        $phone = $settings['contact_phone'] ?? $settings['hotline'] ?? '1900 9999';
        $stores = Store::query()
            ->where('status', 'active')
            ->orderBy('priority_order')
            ->orderBy('name')
            ->get()
            ->map(function (Store $store) {
                $parts = array_filter([
                    $store->address_line,
                    $store->ward,
                    $store->district,
                    $store->province,
                ]);

                return [
                    'name' => $store->name,
                    'address' => implode(', ', $parts),
                ];
            })
            ->values()
            ->all();

        return [
            'site_name' => $siteName,
            'site_slogan' => $settings['site_slogan'] ?? 'Nâng niu sự tự tin của bạn mỗi ngày.',
            'phone' => $phone,
            'email' => $settings['contact_email'] ?? 'support@shopnoiy.com',
            'stores' => $stores,
        ];
    }

    private function activePromoTicker($now): ?PromoTicker
    {
        return PromoTicker::query()
            ->where('status', 'active')
            ->where(function ($query) use ($now) {
                $query->whereNull('start_at')->orWhere('start_at', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('end_at')->orWhere('end_at', '>=', $now);
            })
            ->orderByDesc('id')
            ->first();
    }

    private function attachPrimaryImage(Collection $products, string $fallback): Collection
    {
        $productIds = $products->pluck('id')->map(fn ($id) => (int) $id)->all();
        $imageByProduct = $this->primaryImagesByProductIds($productIds, $fallback);

        return $products->map(function (Product $product) use ($imageByProduct, $fallback) {
            $product->primary_image_url = $imageByProduct[$product->id] ?? $fallback;

            return $product;
        });
    }

    private function primaryImagesByProductIds(array $productIds, string $fallback): array
    {
        if (count($productIds) === 0) {
            return [];
        }

        $images = DB::table('product_images')
            ->whereIn('product_id', $productIds)
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $imageByProduct = [];
        foreach ($images as $image) {
            if (!array_key_exists((int) $image->product_id, $imageByProduct)) {
                $imageByProduct[(int) $image->product_id] = $this->resolveImageUrl($image->image_url, $fallback);
            }
        }

        return $imageByProduct;
    }

    private function resolveImageUrl(?string $path, string $fallback): string
    {
        if (empty($path)) {
            return $fallback;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return url($path);
    }

    private function resolveSettingAssetUrl(?string $path): ?string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '//', 'data:'])) {
            return $path;
        }

        return url($path);
    }

    /**
     * @param  array<int, array{name:string,url:string}>  $items
     * @return array<string, mixed>|null
     */
    private function buildBreadcrumbSchema(array $items): ?array
    {
        $items = array_values(array_filter($items, function ($item) {
            return is_array($item)
                && !empty($item['name'])
                && !empty($item['url']);
        }));

        if (count($items) < 2) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(function (array $item, int $index) {
                return [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $item['name'],
                    'item' => $item['url'],
                ];
            }, $items, array_keys($items)),
        ];
    }

    /**
     * @return array<int, array{name:string,url:string}>
     */
    private function categoryBreadcrumbItems(Category $category): array
    {
        $ancestors = collect();
        $current = $category;

        while (!is_null($current?->parent_id)) {
            $parent = Category::query()
                ->select(['id', 'parent_id', 'name', 'slug'])
                ->whereKey($current->parent_id)
                ->where('status', 'active')
                ->first();

            if (!$parent) {
                break;
            }

            $ancestors->prepend($parent);
            $current = $parent;
        }

        $items = [];

        foreach ($ancestors as $ancestor) {
            $items[] = [
                'name' => $ancestor->name,
                'url' => route('frontend.subcategories', ['slug' => $ancestor->slug]),
            ];
        }

        $items[] = [
            'name' => $category->name,
            'url' => match (true) {
                is_null($category->parent_id) => route('frontend.subcategories', ['slug' => $category->slug]),
                $this->categoryDepth($category) >= 2 => route('frontend.childcategories', ['slug' => $category->slug]),
                default => route('frontend.category', ['slug' => $category->slug]),
            },
        ];

        return $items;
    }

    private function categoryDepth(Category $category): int
    {
        $depth = 0;
        $currentParentId = $category->parent_id;

        while (! is_null($currentParentId)) {
            $depth++;
            $currentParentId = Category::query()->whereKey($currentParentId)->value('parent_id');
        }

        return $depth;
    }

    private function merchantFeedUrl(string $url, string $baseUrl): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }

        if (Str::startsWith($url, ['http://', 'https://'])) {
            $host = parse_url($url, PHP_URL_HOST);

            if (in_array($host, ['localhost', '127.0.0.1'], true)) {
                $path = parse_url($url, PHP_URL_PATH) ?: '';

                return $baseUrl . $path;
            }

            return $url;
        }

        return $baseUrl . '/' . ltrim($url, '/');
    }

    private function categoryDisplayImage(Category $category, string $fallback): string
    {
        if (!empty($category->image_url)) {
            return $this->resolveImageUrl($category->image_url, $fallback);
        }

        $categoryIds = $this->descendantCategoryIds((int) $category->id);
        $categoryIds[] = (int) $category->id;
        $randomImage = $this->randomCategoryProductImageUrl(array_values(array_unique($categoryIds)));

        return $randomImage ?: $fallback;
    }

    private function randomCategoryProductImageUrl(array $categoryIds): ?string
    {
        if (count($categoryIds) === 0) {
            return null;
        }

        $imageUrl = DB::table('product_images')
            ->join('products', 'product_images.product_id', '=', 'products.id')
            ->whereIn('products.category_id', $categoryIds)
            ->where('products.status', 'active')
            ->inRandomOrder()
            ->orderByDesc('product_images.is_primary')
            ->orderBy('product_images.sort_order')
            ->orderBy('product_images.id')
            ->value('product_images.image_url');

        return $imageUrl ? $this->resolveImageUrl($imageUrl, '') : null;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\Category>  $categories
     * @param  array<int, string>  $targetGenders
     * @return \Illuminate\Support\Collection<int, \App\Models\Category>
     */
    private function filterHomeCategoriesByTargetSlug(Collection $categories, array $targetSlugs): Collection
    {
        $targetIds = ProductTarget::query()
            ->whereIn('slug', $targetSlugs)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($targetIds === []) {
            return collect();
        }

        return $categories->filter(function (Category $category) use ($targetIds) {
            return in_array((int) $category->product_target_id, $targetIds, true);
        })->values();
    }

    private function categoryIdsByTargetId(int $targetId): array
    {
        if ($targetId <= 0) {
            return [0];
        }

        $categoryIds = Category::query()
            ->where('status', 'active')
            ->where('product_target_id', $targetId)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return $categoryIds === [] ? [0] : $categoryIds;
    }

    private function descendantCategoryIds(int $categoryId): array
    {
        $descendantIds = [];
        $queue = [$categoryId];

        while (!empty($queue)) {
            $children = Category::query()
                ->whereIn('parent_id', $queue)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if (empty($children)) {
                break;
            }

            $descendantIds = array_merge($descendantIds, $children);
            $queue = $children;
        }

        return array_values(array_unique($descendantIds));
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\Category>  $categories
     * @return \Illuminate\Support\Collection<int, array{parent:\App\Models\Category,items:\Illuminate\Support\Collection<int,\App\Models\Category>}>
     */
    private function groupCategoriesByParentTree(Collection $categories): Collection
    {
        $categoryById = $categories->keyBy(fn (Category $category) => (int) $category->id);
        $childrenByParentId = $categories
            ->filter(fn (Category $category) => !is_null($category->parent_id))
            ->groupBy(fn (Category $category) => (int) $category->parent_id);

        $rootCategories = $categories->filter(function (Category $category) use ($categoryById) {
            return is_null($category->parent_id) || !$categoryById->has((int) $category->parent_id);
        })->values();

        $collectBranchItems = function (Category $category) use (&$collectBranchItems, $childrenByParentId): Collection {
            $items = collect([$category]);
            /** @var \Illuminate\Support\Collection<int, \App\Models\Category> $children */
            $children = $childrenByParentId->get((int) $category->id, collect());

            foreach ($children as $childCategory) {
                $items = $items->merge($collectBranchItems($childCategory));
            }

            return $items;
        };

        $groups = collect();
        foreach ($rootCategories as $rootCategory) {
            $groups->push([
                'parent' => $rootCategory,
                'items' => $collectBranchItems($rootCategory),
            ]);
        }

        return $groups;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\Category>  $categories
     * @return \Illuminate\Support\Collection<int, \App\Models\Category>
     */
    private function sortCategoriesByParentTree(Collection $categories): Collection
    {
        $ordered = $categories
            ->sortBy([
                ['sort_order', 'asc'],
                ['name', 'asc'],
                ['id', 'asc'],
            ])
            ->values();

        $childrenByParentId = $ordered
            ->filter(fn (Category $category) => !is_null($category->parent_id))
            ->groupBy(fn (Category $category) => (int) $category->parent_id);

        $result = collect();
        $visited = [];

        $appendCategory = function (Category $category) use (&$appendCategory, $childrenByParentId, &$result, &$visited) {
            $categoryId = (int) $category->id;
            if (isset($visited[$categoryId])) {
                return;
            }

            $visited[$categoryId] = true;
            $result->push($category);

            /** @var \Illuminate\Support\Collection<int, \App\Models\Category> $children */
            $children = $childrenByParentId->get($categoryId, collect());
            foreach ($children as $childCategory) {
                $appendCategory($childCategory);
            }
        };

        foreach ($ordered->whereNull('parent_id') as $parentCategory) {
            $appendCategory($parentCategory);
        }

        foreach ($ordered as $category) {
            $appendCategory($category);
        }

        return $result->values();
    }

    private function frontendView(string $view, array $data = []): View
    {
        return view("frontend.{$view}", $data);
    }
}
