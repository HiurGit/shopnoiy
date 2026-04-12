# ShopNoiY

Website bán hàng Laravel cho Shop Nội Y, gồm giao diện người dùng, trang quản trị backend, phân quyền theo role và nhật ký hoạt động.

## Công nghệ sử dụng

- PHP `^8.2`
- Laravel `^12.0`
- Livewire `^4.2`
- Vite
- MySQL hoặc MariaDB

## Tính năng chính

- Trang chủ, danh mục, danh mục con, chi tiết sản phẩm, giỏ hàng, thanh toán
- Tìm kiếm và gợi ý tìm kiếm
- Quản lý banner, danh mục, sản phẩm, màu sắc, kích thước
- Quản lý khuyến mãi, promo ticker, footer links, cấu hình website
- Quản lý khách hàng, xếp hạng khách hàng và cấu hình rank
- Phân quyền backend theo `role`
- Nhật ký hoạt động backend: đăng nhập, đăng xuất, thêm, sửa, xóa, cập nhật

## Cấu trúc truy cập

- Frontend: `/`
- Đăng nhập backend: `/thaodepzai/login`
- Khu vực quản trị: `/admin`

## Lưu ý quan trọng

Project này đang dùng thư mục gốc làm web root thay vì thư mục `public` mặc định của Laravel.

Trong [`bootstrap/app.php`](./bootstrap/app.php) có dòng:

```php
$app->usePublicPath(dirname(__DIR__));
```

Khi deploy lên hosting, cần cấu hình domain hoặc document root trỏ đúng theo cách này.

## Cài đặt local

1. Cài package PHP:

```bash
composer install
```

2. Tạo file môi trường:

```bash
copy .env.example .env
```

3. Sinh app key:

```bash
php artisan key:generate
```

4. Cấu hình database trong `.env`

5. Chạy migration:

```bash
php artisan migrate
```

6. Cài package frontend:

```bash
npm install
```

7. Chạy môi trường phát triển:

```bash
composer run dev
```

Hoặc chạy riêng:

```bash
php artisan serve
npm run dev
```

## Build production

```bash
npm run build
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Phân quyền backend

- `admin`: toàn quyền backend
- `staff`: quyền được cấp theo role
- `customer`: là khách hàng, không quản lý trong màn role backend

Danh sách quyền backend được khai báo tại:

- [`app/Support/BackendPermissionRegistry.php`](./app/Support/BackendPermissionRegistry.php)

Màn quản lý role:

- [`app/Http/Controllers/Backend/RoleController.php`](./app/Http/Controllers/Backend/RoleController.php)
- [`resources/views/backend/roles/index.blade.php`](./resources/views/backend/roles/index.blade.php)

## Nhật ký hoạt động

Hệ thống có lưu log hoạt động backend để theo dõi ai đã thao tác gì.

Thành phần chính:

- [`app/Models/ActivityLog.php`](./app/Models/ActivityLog.php)
- [`app/Support/ActivityLogger.php`](./app/Support/ActivityLogger.php)
- [`app/Http/Middleware/LogBackendActivity.php`](./app/Http/Middleware/LogBackendActivity.php)
- [`resources/views/backend/activity-logs/index.blade.php`](./resources/views/backend/activity-logs/index.blade.php)

## Route chính

Khai báo route tại:

- [`routes/web.php`](./routes/web.php)

Bao gồm:

- Frontend storefront
- Backend authentication
- Backend management modules
- Role management
- Activity logs

## Upload và dữ liệu tĩnh

- Ảnh upload lưu trong thư mục `uploads/`
- CSS frontend đang dùng: `frontend/style.css`
- File địa chỉ Việt Nam dùng ở checkout: `frontend/vn-addresses.json`

## Gợi ý khi nén up hosting

- Có thể bỏ `node_modules/`
- Có thể bỏ `tests/` nếu không dùng trên hosting
- Chỉ bỏ `vendor/` khi hosting có thể chạy `composer install`
- Giữ nguyên `uploads/`, `build/`, `.env` phù hợp môi trường thật

## Ghi chú

README này mô tả theo trạng thái hiện tại của project trong workspace. Nếu sau này thêm module hoặc đổi route/backend login, nên cập nhật lại để dễ bàn giao và deploy.
