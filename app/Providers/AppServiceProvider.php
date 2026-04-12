<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();
        $this->configureRateLimiters();
        $this->applyMailSettings();

        View::composer(['frontend.*', 'errors.*'], function ($view): void {
            $siteName = 'Shop noi y';
            $siteSlogan = 'Nang niu su tu tin cua ban moi ngay.';
            $siteLogoUrl = null;
            $siteFaviconUrl = null;
            $siteFaviconVersion = null;
            $siteMetaTitle = null;
            $siteMetaDescription = null;
            $siteMetaOgImage = null;
            $siteMetaRobots = 'index,follow';
            $contactPhone = '';
            $hotline = '';
            $zaloUrl = '';
            $zaloGroupUrl = '';
            $settings = collect();
            $paymentSettings = [];

            try {
                if (Schema::hasTable('site_settings')) {
                    $settings = DB::table('site_settings')
                        ->whereIn('setting_key', [
                            'site_name',
                            'site_slogan',
                            'site_logo_url',
                            'site_favicon_url',
                            'hotline',
                            'contact_phone',
                            'zalo_url',
                            'zalo_group_url',
                            'website_usage_guide',
                            'seo_meta_title',
                            'seo_meta_description',
                            'seo_og_image_url',
                            'seo_robots',
                            'payment_default_method',
                            'payment_checkout_note',
                            'payment_cod_enabled',
                            'payment_cod_title',
                            'payment_cod_description',
                            'payment_vietqr_enabled',
                            'payment_vietqr_title',
                            'payment_vietqr_description',
                            'payment_vietqr_bank_bin',
                            'payment_vietqr_bank_name',
                            'payment_vietqr_account_no',
                            'payment_vietqr_account_name',
                            'payment_vietqr_template',
                            'payment_vietqr_transfer_prefix',
                            'payment_vietqr_expire_minutes',
                        ])
                        ->pluck('setting_value', 'setting_key');

                    $siteName = trim((string) ($settings['site_name'] ?? '')) ?: $siteName;
                    $siteSlogan = trim((string) ($settings['site_slogan'] ?? '')) ?: $siteSlogan;
                    $siteLogoUrl = $this->resolveSettingAssetUrl($settings['site_logo_url'] ?? null);
                    $siteFaviconUrl = $this->resolveSettingAssetUrl($settings['site_favicon_url'] ?? null);
                    $siteFaviconVersion = $this->resolveAssetVersion($settings['site_favicon_url'] ?? null);
                    $siteMetaTitle = trim((string) ($settings['seo_meta_title'] ?? '')) ?: null;
                    $siteMetaDescription = trim((string) ($settings['seo_meta_description'] ?? '')) ?: $siteSlogan;
                    $siteMetaOgImage = $this->resolveSettingAssetUrl($settings['seo_og_image_url'] ?? null);
                    $siteMetaRobots = trim((string) ($settings['seo_robots'] ?? '')) ?: $siteMetaRobots;
                    $hotline = trim((string) ($settings['hotline'] ?? ''));
                    $contactPhone = trim((string) ($settings['contact_phone'] ?? ''));
                    $zaloUrl = trim((string) ($settings['zalo_url'] ?? ''));
                    $zaloGroupUrl = trim((string) ($settings['zalo_group_url'] ?? ''));
                    $paymentSettings = [
                        'default_method' => trim((string) ($settings['payment_default_method'] ?? 'cod')) ?: 'cod',
                        'checkout_note' => trim((string) ($settings['payment_checkout_note'] ?? '')),
                        'cod_enabled' => (string) ($settings['payment_cod_enabled'] ?? '1') !== '0',
                        'cod_title' => trim((string) ($settings['payment_cod_title'] ?? '')) ?: 'Thanh toán khi nhận hàng',
                        'cod_description' => trim((string) ($settings['payment_cod_description'] ?? '')),
                        'vietqr_enabled' => (string) ($settings['payment_vietqr_enabled'] ?? '0') === '1',
                        'vietqr_title' => trim((string) ($settings['payment_vietqr_title'] ?? '')) ?: 'Chuyển khoản VietQR',
                        'vietqr_description' => trim((string) ($settings['payment_vietqr_description'] ?? '')) ?: 'Quét mã QR ngân hàng để chuyển khoản.',
                        'vietqr_bank_bin' => trim((string) ($settings['payment_vietqr_bank_bin'] ?? '')),
                        'vietqr_bank_name' => trim((string) ($settings['payment_vietqr_bank_name'] ?? '')),
                        'vietqr_account_no' => trim((string) ($settings['payment_vietqr_account_no'] ?? '')),
                        'vietqr_account_name' => trim((string) ($settings['payment_vietqr_account_name'] ?? '')),
                        'vietqr_template' => trim((string) ($settings['payment_vietqr_template'] ?? '')) ?: 'compact2',
                        'vietqr_transfer_prefix' => trim((string) ($settings['payment_vietqr_transfer_prefix'] ?? 'TT')) ?: 'TT',
                        'vietqr_expire_minutes' => max(1, (int) ($settings['payment_vietqr_expire_minutes'] ?? 30)),
                    ];
                }
            } catch (\Throwable $exception) {
                // Fall back to defaults when settings are unavailable.
            }

            $parts = preg_split('/\s+/', trim((string) $siteName), -1, PREG_SPLIT_NO_EMPTY) ?: ['Shop', 'noi y'];
            $primary = array_shift($parts) ?: 'Shop';
            $accent = count($parts) ? implode(' ', $parts) : null;
            $preferredPhone = $hotline !== '' ? $hotline : $contactPhone;
            $normalizedPhone = preg_replace('/\D+/', '', $preferredPhone) ?: '';
            $phoneHref = $normalizedPhone !== '' ? 'tel:' . $normalizedPhone : null;
            $directZaloUrl = $normalizedPhone !== '' ? 'https://zalo.me/' . $normalizedPhone : null;
            $websiteUsageGuide = trim((string) ($settings['website_usage_guide'] ?? ''));

            $view->with('frontendSiteName', $siteName);
            $view->with('frontendSiteSlogan', $siteSlogan);
            $view->with('frontendLogoPrimary', $primary);
            $view->with('frontendLogoAccent', $accent);
            $view->with('frontendLogoUrl', $siteLogoUrl);
            $view->with('frontendFaviconUrl', $siteFaviconUrl);
            $view->with('frontendFaviconVersion', $siteFaviconVersion);
            $resolvedZaloGroupUrl = $zaloGroupUrl !== '' ? $zaloGroupUrl : $zaloUrl;

            $view->with('frontendMetaTitle', $siteMetaTitle ?: $siteName);
            $view->with('frontendMetaDescription', $siteMetaDescription ?: $siteSlogan);
            $view->with('frontendMetaOgImage', $siteMetaOgImage ?: $siteLogoUrl);
            $view->with('frontendMetaRobots', $siteMetaRobots);
            $view->with('frontendPaymentSettings', array_replace([
                'default_method' => 'cod',
                'checkout_note' => '',
                'cod_enabled' => true,
                'cod_title' => 'Thanh toán khi nhận hàng',
                'cod_description' => '',
                'vietqr_enabled' => false,
                'vietqr_title' => 'Chuyển khoản VietQR',
                'vietqr_description' => 'Quét mã QR ngân hàng để chuyển khoản.',
                'vietqr_bank_bin' => '',
                'vietqr_bank_name' => '',
                'vietqr_account_no' => '',
                'vietqr_account_name' => '',
                'vietqr_template' => 'compact2',
                'vietqr_transfer_prefix' => 'TT',
                'vietqr_expire_minutes' => 30,
            ], $paymentSettings));
            $view->with('frontendZaloGroupUrl', $resolvedZaloGroupUrl);
            $view->with('frontendContactLinks', [
                [
                    'label' => 'Giới thiệu',
                    'description' => $websiteUsageGuide !== '' ? 'Xem thông tin giới thiệu về shop' : 'Nội dung đang được cập nhật',
                    'icon' => 'bi-journal-text',
                    'href' => route('frontend.policy.guide'),
                    'theme' => 'guide',
                ],
                [
                    'label' => 'Gọi điện',
                    'description' => $preferredPhone !== '' ? $preferredPhone : 'Cập nhật số trong quản trị',
                    'icon' => 'bi-telephone-fill',
                    'href' => $phoneHref,
                    'theme' => 'phone',
                ],
                [
                    'label' => 'Chat Zalo',
                    'description' => $directZaloUrl ? 'Nhắn tin trực tiếp với shop' : 'Cập nhật Hotline hoặc Điện thoại trong quản trị',
                    'icon' => 'bi-chat-dots-fill',
                    'href' => $directZaloUrl,
                    'theme' => 'zalo',
                ],
                [
                    'label' => 'Nhóm Zalo',
                    'description' => $zaloUrl !== '' ? 'Tham gia cộng đồng khách hàng' : 'Cập nhật Link Zalo trong mục mạng xã hội',
                    'icon' => 'bi-people-fill',
                    'href' => $zaloUrl !== '' ? $zaloUrl : null,
                    'theme' => 'group',
                ],
            ]);
        });
    }

    private function applyMailSettings(): void
    {
        try {
            if (!Schema::hasTable('site_settings')) {
                return;
            }

            $settings = DB::table('site_settings')
                ->whereIn('setting_key', [
                    'mail_mailer',
                    'mail_scheme',
                    'mail_host',
                    'mail_port',
                    'mail_username',
                    'mail_password',
                    'mail_from_address',
                    'mail_from_name',
                ])
                ->pluck('setting_value', 'setting_key');

            $mailMailer = trim((string) ($settings['mail_mailer'] ?? ''));
            $mailScheme = $this->normalizeMailScheme((string) ($settings['mail_scheme'] ?? ''));
            $mailHost = trim((string) ($settings['mail_host'] ?? ''));
            $mailPort = trim((string) ($settings['mail_port'] ?? ''));
            $mailUsername = trim((string) ($settings['mail_username'] ?? ''));
            $mailPassword = (string) ($settings['mail_password'] ?? '');
            $mailFromAddress = trim((string) ($settings['mail_from_address'] ?? ''));
            $mailFromName = trim((string) ($settings['mail_from_name'] ?? ''));

            if ($mailMailer !== '') {
                config(['mail.default' => $mailMailer]);
            }

            if ($mailScheme !== '') {
                config(['mail.mailers.smtp.scheme' => $mailScheme]);
            }

            if ($mailHost !== '') {
                config(['mail.mailers.smtp.host' => $mailHost]);
            }

            if ($mailPort !== '') {
                config(['mail.mailers.smtp.port' => (int) $mailPort]);
            }

            if ($mailUsername !== '') {
                config(['mail.mailers.smtp.username' => $mailUsername]);
            }

            if ($mailPassword !== '') {
                config(['mail.mailers.smtp.password' => $mailPassword]);
            }

            if ($mailFromAddress !== '') {
                config(['mail.from.address' => $mailFromAddress]);
            }

            if ($mailFromName !== '') {
                config(['mail.from.name' => $mailFromName]);
            }
        } catch (\Throwable $exception) {
            // Fall back to .env mail settings when database settings are unavailable.
        }
    }

    private function normalizeMailScheme(string $scheme): string
    {
        $scheme = trim(mb_strtolower($scheme));

        return match ($scheme) {
            'tls', 'starttls', 'smtp' => 'smtp',
            'ssl', 'smtps' => 'smtps',
            default => $scheme,
        };
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

    private function resolveAssetVersion(?string $path): ?int
    {
        $path = trim((string) $path);

        if ($path === '' || Str::startsWith($path, ['http://', 'https://', '//', 'data:'])) {
            return null;
        }

        $fullPath = public_path(ltrim($path, '/'));

        return is_file($fullPath) ? filemtime($fullPath) : null;
    }

    private function configureRateLimiters(): void
    {
        RateLimiter::for('admin-login', function (Request $request) {
            $email = Str::lower((string) $request->input('email', ''));

            return [
                Limit::perMinute(5)->by($email . '|' . $request->ip()),
                Limit::perMinute(20)->by($request->ip()),
            ];
        });

        RateLimiter::for('place-order', function (Request $request) {
            $phone = preg_replace('/\D+/', '', (string) $request->input('customer_phone', '')) ?: 'guest';

            return [
                Limit::perMinute(5)->by($phone . '|' . $request->ip()),
                Limit::perMinute(20)->by($request->ip()),
            ];
        });

        RateLimiter::for('search-suggestions', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });
    }
}
