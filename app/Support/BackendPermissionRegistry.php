<?php

namespace App\Support;

class BackendPermissionRegistry
{
    /**
     * @return array<string, array{label: string, route: string, prefixes: array<int, string>}>
     */
    public static function definitions(): array
    {
        return [
            'dashboard' => [
                'label' => 'Bảng điều khiển',
                'route' => 'backend.index',
                'prefixes' => ['backend.index'],
            ],
            'banners' => [
                'label' => 'Banner',
                'route' => 'backend.banners',
                'prefixes' => ['backend.banners'],
            ],
            'home_management' => [
                'label' => 'Quản lý trang chủ',
                'route' => 'backend.home-management',
                'prefixes' => ['backend.home-management'],
            ],
            'categories' => [
                'label' => 'Danh mục',
                'route' => 'backend.categories',
                'prefixes' => ['backend.categories'],
            ],
            'products' => [
                'label' => 'Danh sách sản phẩm',
                'route' => 'backend.products',
                'prefixes' => ['backend.products'],
            ],
            'product_colors' => [
                'label' => 'Màu sắc sản phẩm',
                'route' => 'backend.product-colors',
                'prefixes' => ['backend.product-colors'],
            ],
            'product_sizes' => [
                'label' => 'Kích thước sản phẩm',
                'route' => 'backend.product-sizes',
                'prefixes' => ['backend.product-sizes'],
            ],
            'product_tags' => [
                'label' => 'Tag sản phẩm',
                'route' => 'backend.product-tags',
                'prefixes' => ['backend.product-tags'],
            ],
            'product_targets' => [
                'label' => 'Đối tượng danh mục',
                'route' => 'backend.product-targets',
                'prefixes' => ['backend.product-targets'],
            ],
            'promotions' => [
                'label' => 'Khuyến mãi',
                'route' => 'backend.promotions',
                'prefixes' => ['backend.promotions'],
            ],
            'promo_tickers' => [
                'label' => 'Promo Ticker',
                'route' => 'backend.promo-tickers',
                'prefixes' => ['backend.promo-tickers'],
            ],
            'footer_links' => [
                'label' => 'Link chân trang',
                'route' => 'backend.footer-links',
                'prefixes' => ['backend.footer-links'],
            ],
            'orders' => [
                'label' => 'Đơn hàng',
                'route' => 'backend.orders',
                'prefixes' => ['backend.orders'],
            ],
            'payment_invoices' => [
                'label' => 'Hóa đơn',
                'route' => 'backend.payment-invoices',
                'prefixes' => ['backend.payment-invoices'],
            ],
            'sepay_webhook_logs' => [
                'label' => 'Nhật ký SePay',
                'route' => 'backend.sepay-webhook-logs',
                'prefixes' => ['backend.sepay-webhook-logs'],
            ],
            'customers_ranking' => [
                'label' => 'Xếp hạng mua hàng',
                'route' => 'backend.customers.ranking',
                'prefixes' => ['backend.customers.ranking'],
            ],
            'customers_config' => [
                'label' => 'Cấu hình rank',
                'route' => 'backend.customers.config',
                'prefixes' => ['backend.customers.config'],
            ],
            'customers' => [
                'label' => 'Danh sách khách hàng',
                'route' => 'backend.customers',
                'prefixes' => ['backend.customers'],
            ],
            'stores' => [
                'label' => 'Cửa hàng & nội dung',
                'route' => 'backend.stores',
                'prefixes' => ['backend.stores'],
            ],
            'settings' => [
                'label' => 'Cấu hình website',
                'route' => 'backend.settings',
                'prefixes' => ['backend.settings'],
            ],
            'activity_logs' => [
                'label' => 'Nhật ký hoạt động',
                'route' => 'backend.activity-logs',
                'prefixes' => ['backend.activity-logs'],
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function defaultStaffPermissions(): array
    {
        return ['products'];
    }

    public static function permissionForRoute(?string $routeName): ?string
    {
        if (!$routeName || $routeName === 'backend.logout') {
            return null;
        }

        foreach (self::definitions() as $key => $definition) {
            foreach ($definition['prefixes'] as $prefix) {
                if (str_starts_with($routeName, $prefix)) {
                    return $key;
                }
            }
        }

        return null;
    }

    public static function routeForPermission(string $permission): ?string
    {
        return self::definitions()[$permission]['route'] ?? null;
    }
}

