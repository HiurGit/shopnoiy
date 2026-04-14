<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class FrontendResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $token
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $email = (string) ($notifiable->getEmailForPasswordReset() ?? '');
        $url = route('frontend.password.reset', [
            'token' => $this->token,
            'email' => $email,
        ]);

        $settings = DB::table('site_settings')
            ->whereIn('setting_key', [
                'site_name',
                'mail_from_name',
                'contact_phone',
                'hotline',
                'contact_email',
                'contact_address',
                'site_logo_url',
            ])
            ->pluck('setting_value', 'setting_key');

        $siteName = trim((string) ($settings['site_name'] ?? config('app.name', 'ShopNoiY')));
        $mailFromName = trim((string) ($settings['mail_from_name'] ?? ''));
        $brandName = $mailFromName !== '' ? $mailFromName : $siteName;
        $contactPhone = trim((string) ($settings['contact_phone'] ?? $settings['hotline'] ?? ''));
        $contactEmail = trim((string) ($settings['contact_email'] ?? config('mail.from.address')));
        $contactAddress = trim((string) ($settings['contact_address'] ?? ''));
        $logoPath = trim((string) ($settings['site_logo_url'] ?? ''));
        $logoUrl = $this->resolveImageUrl($logoPath);

        return (new MailMessage())
            ->from((string) config('mail.from.address'), (string) config('mail.from.name'))
            ->subject($siteName . ' - Đặt lại mật khẩu tài khoản')
            ->view('emails.auth.reset-password', [
                'brand_name' => $brandName,
                'site_name' => $siteName,
                'logo_url' => $logoUrl,
                'email' => $email,
                'reset_url' => $url,
                'expires_in' => (int) config('auth.passwords.users.expire', 60),
                'contact_phone' => $contactPhone,
                'contact_email' => $contactEmail,
                'contact_address' => $contactAddress,
            ]);
    }

    private function resolveImageUrl(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    }
}
