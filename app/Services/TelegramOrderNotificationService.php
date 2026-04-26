<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class TelegramOrderNotificationService
{
    public function notifyNewOrder(object $order): void
    {
        $settings = $this->settings();

        if (!$settings['enabled'] || $settings['bot_token'] === '' || $settings['chat_id'] === '') {
            return;
        }

        try {
            Http::asForm()
                ->timeout(10)
                ->post(
                    sprintf('https://api.telegram.org/bot%s/sendMessage', $settings['bot_token']),
                    [
                        'chat_id' => $settings['chat_id'],
                        'text' => $this->buildNewOrderMessage($order),
                        'disable_web_page_preview' => true,
                    ]
                )
                ->throw();
        } catch (\Throwable $exception) {
            Log::warning('Telegram order notification failed.', [
                'order_id' => (int) ($order->id ?? 0),
                'order_code' => (string) ($order->order_code ?? ''),
                'message' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * @return array{enabled: bool, bot_token: string, chat_id: string}
     */
    private function settings(): array
    {
        $settings = [];

        try {
            if (DB::getSchemaBuilder()->hasTable('site_settings')) {
                $settings = DB::table('site_settings')
                    ->whereIn('setting_key', [
                        'telegram_notifications_enabled',
                        'telegram_bot_token',
                        'telegram_chat_id',
                    ])
                    ->pluck('setting_value', 'setting_key')
                    ->all();
            }
        } catch (\Throwable $exception) {
            $settings = [];
        }

        $enabledSetting = $settings['telegram_notifications_enabled'] ?? config('services.telegram.notifications_enabled', false);
        $botToken = trim((string) ($settings['telegram_bot_token'] ?? config('services.telegram.bot_token', '')));
        $chatId = trim((string) ($settings['telegram_chat_id'] ?? config('services.telegram.chat_id', '')));

        return [
            'enabled' => filter_var($enabledSetting, FILTER_VALIDATE_BOOL) || (string) $enabledSetting === '1',
            'bot_token' => $botToken,
            'chat_id' => $chatId,
        ];
    }

    private function buildNewOrderMessage(object $order): string
    {
        $itemSummary = DB::table('order_items')
            ->where('order_id', (int) ($order->id ?? 0))
            ->selectRaw('COALESCE(SUM(qty), 0) as total_qty, COUNT(*) as line_count')
            ->first();

        $selectedStore = !empty($order->store_id)
            ? DB::table('stores')->where('id', (int) $order->store_id)->value('name')
            : null;

        $deliveryLabel = match ((string) ($order->delivery_type ?? 'delivery')) {
            'pickup' => 'Nhận tại cửa hàng',
            default => 'Giao hàng',
        };

        $paymentMethodCode = strtolower((string) ($order->payment_method ?? 'cod'));
        $paymentStatus = strtolower((string) ($order->payment_status ?? 'unpaid'));

        $paymentMethod = match ($paymentMethodCode) {
            'vietqr' => 'VietQR',
            'cod' => 'COD',
            default => (string) ($order->payment_method ?? 'Khác'),
        };

        $paymentStatusLabel = $paymentMethodCode === 'cod' && in_array($paymentStatus, ['unpaid', 'pending'], true)
            ? 'Thanh toán khi nhận hàng'
            : match ($paymentStatus) {
                'paid' => 'Đã thanh toán',
                'unpaid' => 'Chưa thanh toán',
                'pending' => 'Đang chờ thanh toán',
                'failed' => 'Thanh toán thất bại',
                'refunded' => 'Đã hoàn tiền',
                default => 'Chưa thanh toán',
            };

        $address = trim((string) ($order->shipping_address_text ?? ''));
        $note = trim((string) ($order->note ?? ''));
        $adminOrderUrl = $this->adminOrderUrl((int) ($order->id ?? 0));

        $lines = array_filter([
             $note !== '' ? 'GHI CHÚ: ' . $note : null,
            'THÔNG BÁO ĐƠN HÀNG MỚI',
            'Mã đơn: ' . ((string) ($order->order_code ?? ('#' . ($order->id ?? '')))),
            'Khách hàng: ' . trim((string) ($order->customer_name ?? 'Khách hàng')),
            'Số điện thoại: ' . trim((string) ($order->customer_phone ?? '')),
            !empty($order->customer_email) ? 'Email: ' . trim((string) $order->customer_email) : null,
            'Tổng thanh toán: ' . number_format((float) ($order->total_amount ?? 0), 0, ',', '.') . 'đ',
            'Thanh toán: ' . $paymentMethod . ' | Trạng thái: ' . $paymentStatusLabel,
            'Nhận hàng: ' . $deliveryLabel . ($selectedStore ? ' | Cửa hàng: ' . $selectedStore : ''),
            $address !== '' ? 'Địa chỉ: ' . $address : null,
            'Sản phẩm: ' . (int) ($itemSummary->line_count ?? 0) . ' món | Tổng SL: ' . (int) ($itemSummary->total_qty ?? 0),
           
            'Thời gian tạo đơn: ' . now()->format('d/m/Y H:i'),
            $adminOrderUrl !== null ? 'Mở đơn: ' . $adminOrderUrl : null,
        ]);

        return implode("\n", $lines);
    }

    private function adminOrderUrl(int $orderId): ?string
    {
        if ($orderId <= 0) {
            return null;
        }

        try {
            return URL::route('backend.orders.show', ['order' => $orderId], true);
        } catch (\Throwable $exception) {
            return null;
        }
    }
}
