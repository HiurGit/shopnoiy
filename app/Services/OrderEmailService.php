<?php

namespace App\Services;

use App\Mail\OrderStatusMail;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class OrderEmailService
{
    private const PRODUCT_IMAGE_FALLBACK = 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?auto=format&fit=crop&w=500&q=80';
    private const SEND_DELAY_SECONDS = 5;

    public function sendOrderVerifiedEmail(int|Order $order): void
    {
        $this->dispatchOrderStatusEmail($order, 'verified');
    }

    public function sendOrderStatusEmailNow(int|Order $order, string $type): void
    {
        $orderModel = $order instanceof Order ? $order->fresh() : Order::query()->find($order);
        if (!$orderModel) {
            return;
        }

        $recipient = trim((string) $orderModel->customer_email);
        if ($recipient === '') {
            return;
        }

        try {
            Mail::to($recipient)->send(new OrderStatusMail($this->buildPayload($orderModel, $type)));
        } catch (\Throwable $exception) {
            Log::warning('Gửi email thông báo đơn hàng thất bại.', [
                'order_id' => $orderModel->id,
                'order_code' => $orderModel->order_code,
                'recipient' => $recipient,
                'type' => $type,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function dispatchOrderStatusEmail(int|Order $order, string $type): void
    {
        $orderId = $order instanceof Order ? (int) $order->id : (int) $order;

        if ($orderId <= 0) {
            return;
        }

        app()->terminating(function () use ($orderId, $type): void {
            sleep(self::SEND_DELAY_SECONDS);
            app(self::class)->sendOrderStatusEmailNow($orderId, $type);
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPayload(Order $order, string $type): array
    {
        $order->ensureCustomerTrackingToken();

        $settings = DB::table('site_settings')
            ->whereIn('setting_key', [
                'site_name',
                'mail_from_name',
                'contact_phone',
                'hotline',
                'contact_email',
                'contact_address',
                'site_logo_url',
                'zalo_url',
                'zalo_group_url',
            ])
            ->pluck('setting_value', 'setting_key');

        $store = null;
        if (!empty($order->store_id)) {
            $store = DB::table('stores')->where('id', $order->store_id)->first();
        }

        $itemRows = DB::table('order_items')
            ->where('order_id', $order->id)
            ->orderBy('id')
            ->get();

        $productIds = $itemRows->pluck('product_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

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
                    $imageMap[$productId] = (string) $imageRow->image_url;
                }
            }
        }

        $items = $itemRows->map(function ($item) use ($imageMap) {
            $imageSource = $imageMap[(int) ($item->product_id ?? 0)] ?? self::PRODUCT_IMAGE_FALLBACK;

            return [
                'name' => (string) $item->product_name_snapshot,
                'variant' => trim((string) ($item->variant_name_snapshot ?? '')),
                'qty' => (int) $item->qty,
                'unit_price' => $this->money((float) $item->unit_price),
                'line_total' => $this->money((float) $item->line_total),
                'image' => $this->resolveImageUrl($imageSource, self::PRODUCT_IMAGE_FALLBACK),
            ];
        })->all();

        $siteName = trim((string) ($settings['site_name'] ?? config('app.name', 'ShopNoiY')));
        $mailFromName = trim((string) ($settings['mail_from_name'] ?? ''));
        $brandName = $mailFromName !== '' ? $mailFromName : $siteName;
        $contactPhone = trim((string) ($settings['contact_phone'] ?? $settings['hotline'] ?? ''));
        $contactEmail = trim((string) ($settings['contact_email'] ?? config('mail.from.address')));
        $contactAddress = trim((string) ($settings['contact_address'] ?? ''));
        $zaloUrl = trim((string) ($settings['zalo_url'] ?? ''));
        $zaloGroupUrl = trim((string) ($settings['zalo_group_url'] ?? ''));
        $logoUrl = $this->resolveImageUrl((string) ($settings['site_logo_url'] ?? ''), '');
        $trackingUrl = route('frontend.order-tracking', ['token' => $order->customer_tracking_token]);
        $orderSuccessUrl = URL::signedRoute('frontend.order-success', ['order' => $order->id]);

        $deliveryLabel = $order->delivery_type === 'pickup'
            ? 'Nhận tại cửa hàng'
            : 'Địa chỉ giao hàng';

        $deliveryValue = $order->delivery_type === 'pickup'
            ? trim(implode(', ', array_filter([
                $store->name ?? 'Nhận tại cửa hàng',
                $store->address_line ?? null,
                $store->district ?? null,
                $store->province ?? null,
            ])))
            : ((string) ($order->shipping_address_text ?: '-'));

        $statusConfig = match ($type) {
            'verified' => [
                'subject' => $siteName . ' xác nhận đơn hàng ' . $order->order_code,
                'heading' => 'Đơn hàng của bạn đã được xác nhận',
                'intro' => 'Shop đã xác minh đơn hàng và đang chuẩn bị các bước tiếp theo để xử lý nhanh nhất cho bạn.',
                'status_label' => 'Đã xác minh',
                'summary_label' => 'Đang xử lý',
                'hero_note' => 'Bạn có thể theo dõi tiến trình đơn hàng bằng nút bên dưới.',
            ],
            default => [
                'subject' => $siteName . ' đã nhận đơn hàng ' . $order->order_code,
                'heading' => 'Cảm ơn bạn đã đặt hàng',
                'intro' => 'Đơn hàng của bạn đã được ghi nhận thành công. Shop sẽ sớm kiểm tra và gửi xác nhận trong thời gian ngắn.',
                'status_label' => 'Chờ xác minh',
                'summary_label' => 'Mới tiếp nhận',
                'hero_note' => 'Vui lòng chờ trong giây lát để hệ thống cập nhật theo dõi đơn hàng đầy đủ.',
            ],
        };

        return [
            'subject' => $statusConfig['subject'],
            'heading' => $statusConfig['heading'],
            'intro' => $statusConfig['intro'],
            'status_label' => $statusConfig['status_label'],
            'summary_label' => $statusConfig['summary_label'],
            'hero_note' => $statusConfig['hero_note'],
            'site_name' => $brandName,
            'logo_url' => $logoUrl,
            'logo_image' => $this->resolveImageUrl((string) ($settings['site_logo_url'] ?? ''), ''),
            'customer_name' => (string) $order->customer_name,
            'order_code' => (string) $order->order_code,
            'order_date' => optional($order->created_at)->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i'),
            'verified_at' => optional($order->verified_at)->format('d/m/Y H:i'),
            'payment_method' => $this->paymentMethodLabel((string) $order->payment_method),
            'payment_status' => $this->paymentStatusLabel((string) $order->payment_status),
            'delivery_label' => $deliveryLabel,
            'delivery_value' => $deliveryValue,
            'customer_phone' => (string) $order->customer_phone,
            'customer_email' => (string) $order->customer_email,
            'note' => trim((string) ($order->note ?? '')),
            'subtotal' => $this->money((float) $order->subtotal),
            'shipping_fee' => $this->money((float) $order->shipping_fee),
            'total_amount' => $this->money((float) $order->total_amount),
            'tracking_url' => $trackingUrl,
            'order_success_url' => $orderSuccessUrl,
            'contact_phone' => $contactPhone,
            'contact_email' => $contactEmail,
            'contact_address' => $contactAddress,
            'zalo_url' => $zaloUrl,
            'zalo_group_url' => $zaloGroupUrl,
            'items' => $items,
            'is_verified' => $type === 'verified',
        ];
    }

    private function paymentMethodLabel(string $paymentMethod): string
    {
        return match ($paymentMethod) {
            'cod' => 'Thanh toán khi nhận hàng (COD)',
            'vietqr' => 'Chuyển khoản VietQR',
            default => strtoupper($paymentMethod),
        };
    }

    private function paymentStatusLabel(string $paymentStatus): string
    {
        return match ($paymentStatus) {
            'paid' => 'Đã thanh toán',
            'unpaid' => 'Chưa thanh toán',
            default => ucwords(str_replace('_', ' ', $paymentStatus)),
        };
    }

    private function money(float $amount): string
    {
        return number_format($amount, 0, ',', '.') . 'đ';
    }

    private function resolveImageUrl(?string $path, string $fallback): string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return $fallback;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    }

}
