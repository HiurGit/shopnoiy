<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SePayWebhookService
{
    public function settings(): array
    {
        $settings = DB::table('site_settings')
            ->whereIn('setting_key', [
                'payment_sepay_webhook_secret',
            ])
            ->pluck('setting_value', 'setting_key');

        return [
            'webhook_secret' => trim((string) ($settings['payment_sepay_webhook_secret'] ?? '')),
        ];
    }

    public function hasSecret(): bool
    {
        return $this->settings()['webhook_secret'] !== '';
    }

    public function isValidRequest(?string $secretHeader, ?string $authorizationHeader): bool
    {
        $expectedSecret = $this->settings()['webhook_secret'];

        if ($expectedSecret === '') {
            return false;
        }

        $secretHeader = trim((string) $secretHeader);
        $authorizationHeader = trim((string) $authorizationHeader);

        if ($secretHeader !== '' && hash_equals($expectedSecret, $secretHeader)) {
            return true;
        }

        if (preg_match('/^Apikey\s+(.+)$/i', $authorizationHeader, $matches) === 1) {
            return hash_equals($expectedSecret, trim((string) ($matches[1] ?? '')));
        }

        if (preg_match('/^Bearer\s+(.+)$/i', $authorizationHeader, $matches) === 1) {
            return hash_equals($expectedSecret, trim((string) ($matches[1] ?? '')));
        }

        return false;
    }
}
