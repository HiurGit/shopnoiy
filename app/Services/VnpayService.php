<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class VnpayService
{
    private ?array $settings = null;

    public function settings(): array
    {
        if ($this->settings !== null) {
            return $this->settings;
        }

        $dbSettings = [];

        try {
            $dbSettings = DB::table('site_settings')
                ->whereIn('setting_key', [
                    'payment_vnpay_tmn_code',
                    'payment_vnpay_hash_secret',
                    'payment_vnpay_gateway_url',
                    'payment_vnpay_expire_minutes',
                ])
                ->pluck('setting_value', 'setting_key')
                ->all();
        } catch (\Throwable $exception) {
            $dbSettings = [];
        }

        $gatewayUrl = trim((string) ($dbSettings['payment_vnpay_gateway_url'] ?? config('vnpay.gateway_url', '')));
        $tmnCode = trim((string) ($dbSettings['payment_vnpay_tmn_code'] ?? config('vnpay.tmn_code', '')));
        $hashSecret = trim((string) ($dbSettings['payment_vnpay_hash_secret'] ?? config('vnpay.hash_secret', '')));
        $expireMinutes = (int) ($dbSettings['payment_vnpay_expire_minutes'] ?? config('vnpay.expire_minutes', 15));

        $this->settings = [
            'gateway_url' => $gatewayUrl !== '' ? $gatewayUrl : 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html',
            'tmn_code' => $tmnCode,
            'hash_secret' => $hashSecret,
            'version' => (string) config('vnpay.version', '2.1.0'),
            'command' => (string) config('vnpay.command', 'pay'),
            'currency' => (string) config('vnpay.currency', 'VND'),
            'locale' => (string) config('vnpay.locale', 'vn'),
            'order_type' => (string) config('vnpay.order_type', 'other'),
            'expire_minutes' => max(5, $expireMinutes),
            'ipn_url' => $this->resolveRouteUrl('frontend.vnpay.ipn'),
            'return_url' => $this->resolveRouteUrl('frontend.vnpay.return'),
        ];

        return $this->settings;
    }

    public function isConfigured(): bool
    {
        $settings = $this->settings();

        return $settings['tmn_code'] !== '' && $settings['hash_secret'] !== '' && $settings['gateway_url'] !== '';
    }

    public function createPaymentUrl(object $order, string $clientIp): string
    {
        $settings = $this->settings();
        $createdAt = now();
        $expireAt = $createdAt->copy()->addMinutes((int) $settings['expire_minutes']);

        $payload = [
            'vnp_Version' => $settings['version'],
            'vnp_Command' => $settings['command'],
            'vnp_TmnCode' => $settings['tmn_code'],
            'vnp_Amount' => $this->formatAmount((float) $order->total_amount),
            'vnp_CreateDate' => $createdAt->format('YmdHis'),
            'vnp_CurrCode' => $settings['currency'],
            'vnp_IpAddr' => $this->normalizeIpAddress($clientIp),
            'vnp_Locale' => $settings['locale'],
            'vnp_OrderInfo' => 'Thanh toan don hang ' . $order->order_code,
            'vnp_OrderType' => $settings['order_type'],
            'vnp_ReturnUrl' => $settings['return_url'],
            'vnp_TxnRef' => (string) $order->order_code,
            'vnp_ExpireDate' => $expireAt->format('YmdHis'),
        ];

        if ($settings['ipn_url'] !== null) {
            $payload['vnp_IpnUrl'] = $settings['ipn_url'];
        }

        $query = $this->buildQuery($payload);
        $secureHash = hash_hmac('sha512', $query, $settings['hash_secret']);

        return rtrim($settings['gateway_url'], '?') . '?' . $query . '&vnp_SecureHash=' . $secureHash;
    }

    public function validateResponse(array $input): array
    {
        $settings = $this->settings();
        $providedHash = (string) ($input['vnp_SecureHash'] ?? '');
        $payload = $this->extractPayload($input);
        $computedHash = hash_hmac('sha512', $this->buildQuery($payload), $settings['hash_secret']);
        $isSignatureValid = $providedHash !== '' && hash_equals($computedHash, $providedHash);

        return [
            'is_signature_valid' => $isSignatureValid,
            'is_success' => $isSignatureValid
                && (string) ($input['vnp_ResponseCode'] ?? '') === '00'
                && (string) ($input['vnp_TransactionStatus'] ?? '') === '00',
            'txn_ref' => trim((string) ($input['vnp_TxnRef'] ?? '')),
            'transaction_no' => trim((string) ($input['vnp_TransactionNo'] ?? '')),
            'bank_code' => trim((string) ($input['vnp_BankCode'] ?? '')),
            'pay_date' => trim((string) ($input['vnp_PayDate'] ?? '')),
            'response_code' => trim((string) ($input['vnp_ResponseCode'] ?? '')),
            'transaction_status' => trim((string) ($input['vnp_TransactionStatus'] ?? '')),
            'amount' => $this->normalizeReturnedAmount($input['vnp_Amount'] ?? null),
            'message' => $this->messageForResponseCode((string) ($input['vnp_ResponseCode'] ?? '')),
            'raw' => $input,
        ];
    }

    public function messageForResponseCode(string $code): string
    {
        return match ($code) {
            '00' => 'Giao dich thanh cong.',
            '07' => 'Tien da bi tru nhung giao dich nghi ngo.',
            '09' => 'The tai khoan chua dang ky Internet Banking.',
            '10' => 'Xac thuc thong tin the tai khoan khong dung qua 3 lan.',
            '11' => 'Da het han cho thanh toan.',
            '12' => 'The tai khoan bi khoa.',
            '13' => 'Sai ma OTP.',
            '24' => 'Khach hang huy giao dich.',
            '51' => 'Tai khoan khong du so du.',
            '65' => 'Tai khoan vuot han muc giao dich trong ngay.',
            '75' => 'Ngan hang thanh toan dang bao tri.',
            '79' => 'Sai mat khau thanh toan qua so lan quy dinh.',
            default => 'Thanh toan chua hoan tat.',
        };
    }

    private function extractPayload(array $input): array
    {
        $payload = [];

        foreach ($input as $key => $value) {
            if (!Str::startsWith((string) $key, 'vnp_')) {
                continue;
            }

            if (in_array($key, ['vnp_SecureHash', 'vnp_SecureHashType'], true)) {
                continue;
            }

            $payload[$key] = (string) $value;
        }

        ksort($payload);

        return $payload;
    }

    private function buildQuery(array $payload): string
    {
        ksort($payload);

        return http_build_query($payload, '', '&', PHP_QUERY_RFC3986);
    }

    private function formatAmount(float $amount): int
    {
        return (int) round(max(0, $amount) * 100);
    }

    private function normalizeReturnedAmount(mixed $amount): float
    {
        return max(0, ((float) $amount) / 100);
    }

    private function resolveRouteUrl(string $routeName): ?string
    {
        if (!Route::has($routeName)) {
            return null;
        }

        return route($routeName);
    }

    private function normalizeIpAddress(string $clientIp): string
    {
        $clientIp = trim($clientIp);

        return filter_var($clientIp, FILTER_VALIDATE_IP) ? $clientIp : '127.0.0.1';
    }
}
