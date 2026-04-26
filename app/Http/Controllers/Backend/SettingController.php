<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Support\UploadPath;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SettingController extends Controller
{
    private array $fields = [
        'site_name' => ['group' => 'general', 'label' => 'Ten website'],
        'site_slogan' => ['group' => 'general', 'label' => 'Slogan'],
        'site_logo_url' => ['group' => 'general', 'label' => 'Logo URL'],
        'site_favicon_url' => ['group' => 'general', 'label' => 'Favicon URL'],
        'website_usage_guide' => ['group' => 'general', 'label' => 'Huong dan su dung website dat hang'],
        'product_size_guide' => ['group' => 'general', 'label' => 'Huong dan chon size san pham'],
        'product_care_policy' => ['group' => 'general', 'label' => 'Chinh sach bao quan san pham'],
        'product_return_policy' => ['group' => 'general', 'label' => 'Chinh sach doi tra san pham'],
        'privacy_policy' => ['group' => 'general', 'label' => 'Chinh sach bao mat thong tin'],
        'shipping_policy' => ['group' => 'general', 'label' => 'Chinh sach van chuyen'],
        'hotline' => ['group' => 'contact', 'label' => 'Hotline'],
        'contact_phone' => ['group' => 'contact', 'label' => 'Dien thoai'],
        'contact_email' => ['group' => 'contact', 'label' => 'Email lien he'],
        'contact_address' => ['group' => 'contact', 'label' => 'Dia chi'],
        'zalo_url' => ['group' => 'social', 'label' => 'Link Zalo'],
        'zalo_group_url' => ['group' => 'social', 'label' => 'Link nhom Zalo'],
        'facebook_url' => ['group' => 'social', 'label' => 'Link Facebook'],
        'instagram_url' => ['group' => 'social', 'label' => 'Link Instagram'],
        'youtube_url' => ['group' => 'social', 'label' => 'Link Youtube'],
        'tiktok_url' => ['group' => 'social', 'label' => 'Link TikTok'],
        'seo_meta_title' => ['group' => 'seo', 'label' => 'Meta title mac dinh'],
        'seo_meta_description' => ['group' => 'seo', 'label' => 'Meta description mac dinh'],
        'seo_meta_keywords' => ['group' => 'seo', 'label' => 'Meta keywords mac dinh'],
        'seo_robots' => ['group' => 'seo', 'label' => 'Robots mac dinh'],
        'seo_canonical_url' => ['group' => 'seo', 'label' => 'Canonical mac dinh'],
        'seo_og_image_url' => ['group' => 'seo', 'label' => 'OG image URL'],
        'payment_default_method' => ['group' => 'payment', 'label' => 'Phuong thuc thanh toan mac dinh'],
        'payment_checkout_note' => ['group' => 'payment', 'label' => 'Ghi chu chung thanh toan'],
        'payment_cod_enabled' => ['group' => 'payment', 'label' => 'Bat COD'],
        'payment_cod_title' => ['group' => 'payment', 'label' => 'Tieu de COD'],
        'payment_cod_description' => ['group' => 'payment', 'label' => 'Mo ta COD'],
        'payment_vietqr_enabled' => ['group' => 'payment', 'label' => 'Bat VietQR'],
        'payment_vietqr_title' => ['group' => 'payment', 'label' => 'Tieu de VietQR'],
        'payment_vietqr_description' => ['group' => 'payment', 'label' => 'Mo ta VietQR'],
        'payment_vietqr_bank_bin' => ['group' => 'payment', 'label' => 'VietQR Bank BIN'],
        'payment_vietqr_bank_name' => ['group' => 'payment', 'label' => 'VietQR Bank name'],
        'payment_vietqr_account_no' => ['group' => 'payment', 'label' => 'VietQR Account number'],
        'payment_vietqr_account_name' => ['group' => 'payment', 'label' => 'VietQR Account name'],
        'payment_vietqr_template' => ['group' => 'payment', 'label' => 'VietQR Template'],
        'payment_vietqr_transfer_prefix' => ['group' => 'payment', 'label' => 'VietQR Transfer prefix'],
        'payment_vietqr_expire_minutes' => ['group' => 'payment', 'label' => 'VietQR Expire minutes'],
        'payment_sepay_webhook_secret' => ['group' => 'payment', 'label' => 'SePay Webhook secret'],
        'telegram_notifications_enabled' => ['group' => 'notification', 'label' => 'Bat thong bao Telegram'],
        'telegram_bot_token' => ['group' => 'notification', 'label' => 'Telegram bot token'],
        'telegram_chat_id' => ['group' => 'notification', 'label' => 'Telegram chat id'],
        'mail_mailer' => ['group' => 'email', 'label' => 'Mail mailer'],
        'mail_scheme' => ['group' => 'email', 'label' => 'Mail scheme'],
        'mail_host' => ['group' => 'email', 'label' => 'Mail host'],
        'mail_port' => ['group' => 'email', 'label' => 'Mail port'],
        'mail_username' => ['group' => 'email', 'label' => 'Mail username'],
        'mail_password' => ['group' => 'email', 'label' => 'Mail password'],
        'mail_from_address' => ['group' => 'email', 'label' => 'Mail from address'],
        'mail_from_name' => ['group' => 'email', 'label' => 'Mail from name'],
    ];

    public function index(): View
    {
        $settings = DB::table('site_settings')->pluck('setting_value', 'setting_key');

        return view('backend.settings.index', [
            'settings' => $settings,
            'fields' => $this->fields,
            'emailPreviewPayload' => $this->emailPreviewPayload($settings->all()),
        ]);
    }

    /**
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    private function emailPreviewPayload(array $settings): array
    {
        $siteName = trim((string) ($settings['site_name'] ?? config('app.name', 'ShopNoiY')));
        $mailFromName = trim((string) ($settings['mail_from_name'] ?? ''));
        $brandName = $mailFromName !== '' ? $mailFromName : $siteName;
        $contactPhone = trim((string) ($settings['contact_phone'] ?? $settings['hotline'] ?? '0901 234 567'));
        $contactEmail = trim((string) ($settings['contact_email'] ?? $settings['mail_from_address'] ?? 'shop@example.com'));
        $contactAddress = trim((string) ($settings['contact_address'] ?? '123 Đường Mẫu, Phường Mẫu, TP. Buôn Hồ'));
        $zaloUrl = trim((string) ($settings['zalo_url'] ?? 'https://zalo.me/0901234567'));
        $zaloGroupUrl = trim((string) ($settings['zalo_group_url'] ?? 'https://zalo.me/g/examplegroup'));

        return [
            'subject' => $siteName . ' đã nhận đơn hàng ODR-20260408-1029',
            'heading' => 'Cảm ơn bạn đã đặt hàng',
            'intro' => 'Đơn hàng của bạn đã được ghi nhận thành công. Shop sẽ sớm kiểm tra và gửi xác nhận trong thời gian ngắn.',
            'status_label' => 'Chờ xác minh',
            'summary_label' => 'Mới tiếp nhận',
            'hero_note' => 'Vui lòng chờ trong giây lát để hệ thống cập nhật theo dõi đơn hàng đầy đủ.',
            'site_name' => $brandName,
            'logo_url' => !empty($settings['site_logo_url']) ? asset(ltrim((string) $settings['site_logo_url'], '/')) : '',
            'customer_name' => 'Nguyễn Thị Thảo',
            'order_code' => 'ODR-20260408-1029',
            'order_date' => now()->format('d/m/Y H:i'),
            'verified_at' => null,
            'payment_method' => 'Thanh toán khi nhận hàng (COD)',
            'payment_status' => 'Thanh toán khi nhận hàng',
            'delivery_label' => 'Địa chỉ giao hàng',
            'delivery_value' => '25 Nguyễn Huệ, Phường An Bình, TP. Buôn Hồ',
            'customer_phone' => '0964 918 047',
            'customer_email' => 'khachhang@example.com',
            'note' => 'Gọi trước khi giao hàng giúp em.',
            'subtotal' => '420.000đ',
            'shipping_fee' => '30.000đ',
            'total_amount' => '450.000đ',
            'tracking_url' => '#',
            'order_success_url' => '#',
            'contact_phone' => $contactPhone,
            'contact_email' => $contactEmail,
            'contact_address' => $contactAddress,
            'zalo_url' => $zaloUrl,
            'zalo_group_url' => $zaloGroupUrl,
            'items' => [
                [
                    'name' => 'Áo lót ren mềm nâng nhẹ',
                    'variant' => 'Màu kem | Size 34',
                    'qty' => 1,
                    'unit_price' => '220.000đ',
                    'line_total' => '220.000đ',
                    'image_url' => 'https://images.unsplash.com/photo-1581044777550-4cfa60707c03?auto=format&fit=crop&w=300&q=80',
                ],
                [
                    'name' => 'Quần lót cotton co giãn',
                    'variant' => 'Màu hồng | Size M',
                    'qty' => 2,
                    'unit_price' => '100.000đ',
                    'line_total' => '200.000đ',
                    'image_url' => 'https://images.unsplash.com/photo-1617331721458-bd3bd3f9c7f8?auto=format&fit=crop&w=300&q=80',
                ],
            ],
            'is_verified' => false,
        ];
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'site_logo_file' => ['nullable', 'image', 'max:4096'],
            'site_favicon_file' => ['nullable', 'image', 'max:2048'],
            'seo_og_image_file' => ['nullable', 'image', 'max:4096'],
            'payment_vietqr_expire_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'mail_mailer' => ['nullable', 'string', 'max:50'],
            'mail_scheme' => ['nullable', 'string', 'max:20'],
            'mail_host' => ['nullable', 'string', 'max:190'],
            'mail_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'mail_username' => ['nullable', 'string', 'max:190'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_from_address' => ['nullable', 'email', 'max:190'],
            'mail_from_name' => ['nullable', 'string', 'max:190'],
            'telegram_bot_token' => ['nullable', 'string', 'max:255'],
            'telegram_chat_id' => ['nullable', 'string', 'max:100'],
        ]);

        $settings = DB::table('site_settings')->pluck('setting_value', 'setting_key');
        $values = [];
        $booleanFields = [
            'payment_cod_enabled',
            'payment_vietqr_enabled',
            'telegram_notifications_enabled',
        ];

        foreach ($this->fields as $key => $meta) {
            if (in_array($key, $booleanFields, true)) {
                $values[$key] = $request->boolean($key) ? '1' : '0';
                continue;
            }

            $values[$key] = $request->input($key, $settings[$key] ?? null);
        }

        $values['site_logo_url'] = $this->resolveImageSettingValue(
            $request,
            'site_logo_file',
            'remove_site_logo',
            (string) ($settings['site_logo_url'] ?? ''),
            'logo'
        );

        $values['site_favicon_url'] = $this->resolveImageSettingValue(
            $request,
            'site_favicon_file',
            'remove_site_favicon',
            (string) ($settings['site_favicon_url'] ?? ''),
            'favicon'
        );

        $values['seo_og_image_url'] = $this->resolveImageSettingValue(
            $request,
            'seo_og_image_file',
            'remove_seo_og_image',
            (string) ($settings['seo_og_image_url'] ?? ''),
            'og-image'
        );

        foreach ($this->fields as $key => $meta) {
            DB::table('site_settings')->updateOrInsert(
                ['setting_key' => $key],
                [
                    'setting_value' => $values[$key],
                    'setting_group' => $meta['group'],
                    'description' => $meta['label'],
                    'updated_by' => 'admin',
                    'updated_at' => now(),
                ]
            );
        }

        return redirect()->route('backend.settings')->with('success', 'Da luu cau hinh website.');
    }

    private function resolveImageSettingValue(
        Request $request,
        string $fileField,
        string $removeField,
        string $currentValue,
        string $type
    ): ?string {
        $currentValue = trim($currentValue);

        if ($request->boolean($removeField)) {
            $this->deleteSettingImage($currentValue);
            $currentValue = '';
        }

        if ($request->hasFile($fileField)) {
            $newImage = $this->storeUploadedImage($request->file($fileField), $type);
            $this->deleteSettingImage($currentValue);

            return $newImage;
        }

        return $currentValue !== '' ? $currentValue : null;
    }

    private function storeUploadedImage(UploadedFile $file, string $type): string
    {
        $directory = UploadPath::absolute('settings');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $extension = strtolower($file->extension() ?: 'png');
        $filename = $type . '-' . now()->format('YmdHis') . '-' . Str::random(8) . '.' . $extension;
        $file->move($directory, $filename);

        return '/' . trim(UploadPath::relative('settings') . '/' . $filename, '/');
    }

    private function deleteSettingImage(?string $imageUrl): void
    {
        if (empty($imageUrl) || str_starts_with($imageUrl, 'http://') || str_starts_with($imageUrl, 'https://') || str_starts_with($imageUrl, 'data:')) {
            return;
        }

        $normalizedPath = '/' . ltrim($imageUrl, '/');
        $settingsPrefix = '/' . trim(UploadPath::relative('settings'), '/') . '/';
        if (!str_starts_with($normalizedPath, $settingsPrefix)) {
            return;
        }

        $fullPath = public_path(ltrim($normalizedPath, '/'));
        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }
}
