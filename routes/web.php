<?php

use App\Http\Controllers\Backend\CategoryController;
use App\Http\Controllers\Backend\BannerController;
use App\Http\Controllers\Backend\CustomerController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\FooterLinkController;
use App\Http\Controllers\Backend\HomeSectionItemController;
use App\Http\Controllers\Backend\AuthController;
use App\Http\Controllers\Backend\ActivityLogController;
use App\Http\Controllers\Backend\OrderController;
use App\Http\Controllers\Backend\PaymentInvoiceController;
use App\Http\Controllers\Backend\ProductController;
use App\Http\Controllers\Backend\ProductColorController;
use App\Http\Controllers\Backend\ProductSizeController;
use App\Http\Controllers\Backend\ProductTagController;
use App\Http\Controllers\Backend\ProductTargetController;
use App\Http\Controllers\Backend\PromotionController;
use App\Http\Controllers\Backend\PromoTickerController;
use App\Http\Controllers\Backend\RoleController;
use App\Http\Controllers\Backend\SePayWebhookLogController;
use App\Http\Controllers\Backend\SettingController;
use App\Http\Controllers\Backend\StoreController;
use App\Http\Controllers\Frontend\StorefrontController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'home'])->name('frontend.home');
Route::get('/sitemap.xml', [StorefrontController::class, 'sitemap'])->name('frontend.sitemap');
Route::get('/favicon.ico', [StorefrontController::class, 'favicon'])->name('frontend.favicon');
Route::get('/chinh-sach/doi-tra-bao-hanh', [StorefrontController::class, 'policyPage'])->defaults('slug', 'doi-tra-bao-hanh')->name('frontend.policy.return-warranty');
Route::get('/chinh-sach/bao-mat-thong-tin', [StorefrontController::class, 'policyPage'])->defaults('slug', 'bao-mat-thong-tin')->name('frontend.policy.privacy');
Route::get('/chinh-sach/van-chuyen', [StorefrontController::class, 'policyPage'])->defaults('slug', 'van-chuyen')->name('frontend.policy.shipping');
Route::get('/chinh-sach/huong-dan', [StorefrontController::class, 'policyPage'])->defaults('slug', 'huong-dan')->name('frontend.policy.guide');
Route::get('/ho-tro-khach-hang', [StorefrontController::class, 'customerSupport'])->name('frontend.customer-support');
Route::get('/xep-hang-khach-hang', [StorefrontController::class, 'customerRanking'])->name('frontend.customer-ranking');
Route::get('/tim-kiem', [StorefrontController::class, 'search'])->name('frontend.search');
Route::get('/san-pham-noi-bat', [StorefrontController::class, 'featuredProducts'])->name('frontend.featured-products');
Route::get('/google-merchant-feed.xml', [StorefrontController::class, 'merchantFeed'])->name('frontend.merchant-feed');
Route::get('/danh-muc/{slug?}', [StorefrontController::class, 'category'])->name('frontend.category');
Route::middleware('throttle:search-suggestions')->get('/tim-kiem-goi-y', [StorefrontController::class, 'searchSuggestions'])->name('frontend.search-suggestions');
Route::get('/danh-muc-con/{slug?}', [StorefrontController::class, 'subcategories'])->name('frontend.subcategories');
Route::get('/danh-muc-chau/{slug?}', [StorefrontController::class, 'childcategories'])->name('frontend.childcategories');
Route::get('/san-pham/{slug?}', [StorefrontController::class, 'productDetail'])->name('frontend.product-detail');
Route::get('/san-pham-config/{product}', [StorefrontController::class, 'productConfig'])->name('frontend.product-config');
Route::get('/gio-hang', [StorefrontController::class, 'cart'])->name('frontend.cart');
Route::get('/thanh-toan', [StorefrontController::class, 'checkout'])->name('frontend.checkout');
Route::get('/dang-nhap', [StorefrontController::class, 'showCustomerLoginForm'])->name('frontend.login');
Route::post('/dang-nhap', [StorefrontController::class, 'customerLogin'])->name('frontend.login.submit');
Route::get('/dang-ky', [StorefrontController::class, 'showCustomerRegisterForm'])->name('frontend.register');
Route::post('/dang-ky', [StorefrontController::class, 'customerRegister'])->name('frontend.register.submit');
Route::get('/quen-mat-khau', [StorefrontController::class, 'showCustomerForgotPasswordForm'])->name('frontend.password.forgot');
Route::post('/quen-mat-khau', [StorefrontController::class, 'customerForgotPassword'])->name('frontend.password.forgot.submit');
Route::get('/dat-lai-mat-khau/{token}', [StorefrontController::class, 'showCustomerResetPasswordForm'])->name('frontend.password.reset');
Route::post('/dat-lai-mat-khau', [StorefrontController::class, 'customerResetPassword'])->name('frontend.password.reset.submit');
Route::middleware('customer.auth')->group(function () {
    Route::get('/tai-khoan', [StorefrontController::class, 'customerProfile'])->name('frontend.profile');
    Route::get('/tai-khoan/lich-su-mua-hang', [StorefrontController::class, 'customerOrderHistory'])->name('frontend.profile.orders');
    Route::get('/tai-khoan/lich-su-mua-hang/{order}', [StorefrontController::class, 'customerOrderDetail'])->name('frontend.profile.orders.detail');
    Route::post('/tai-khoan/thong-tin', [StorefrontController::class, 'customerUpdateProfileInfo'])->name('frontend.profile.info.update');
    Route::post('/tai-khoan/dia-chi', [StorefrontController::class, 'customerUpdateAddress'])->name('frontend.profile.address.update');
    Route::post('/tai-khoan/avatar', [StorefrontController::class, 'customerUpdateAvatar'])->name('frontend.profile.avatar.update');
    Route::get('/doi-mat-khau', [StorefrontController::class, 'showCustomerChangePasswordForm'])->name('frontend.password.change');
    Route::post('/doi-mat-khau', [StorefrontController::class, 'customerChangePassword'])->name('frontend.password.change.submit');
    Route::post('/dang-xuat', [StorefrontController::class, 'customerLogout'])->name('frontend.logout');
});
Route::middleware('throttle:place-order')->post('/dat-hang', [StorefrontController::class, 'placeOrder'])->name('frontend.place-order');
Route::middleware('signed')->get('/thanh-toan-vietqr/{invoice}', [StorefrontController::class, 'vietqrPaymentPage'])->name('frontend.vietqr.payment');
Route::middleware('signed')->get('/thanh-toan-vietqr/{invoice}/trang-thai', [StorefrontController::class, 'vietqrPaymentStatus'])->name('frontend.vietqr.payment-status');
Route::middleware('signed')->get('/thanh-toan-vietqr/{invoice}/tai-qr', [StorefrontController::class, 'vietqrPaymentDownload'])->name('frontend.vietqr.payment-download');
Route::middleware('signed')->post('/thanh-toan-vietqr/{invoice}/huy', [StorefrontController::class, 'vietqrPaymentCancel'])->name('frontend.vietqr.payment-cancel');
Route::post('/thanh-toan/vietqr/sepay-webhook', [StorefrontController::class, 'sepayWebhook'])->name('frontend.vietqr.sepay-webhook');
Route::middleware('signed')->get('/dat-hang-thanh-cong/{order}', [StorefrontController::class, 'orderSuccess'])->name('frontend.order-success');
Route::middleware(['signed', 'throttle:20,1'])->get('/o/{token}', [StorefrontController::class, 'orderTracking'])->name('frontend.order-tracking');

Route::get('/thaodepzai/login', [AuthController::class, 'showLoginForm'])->name('backend.login');
Route::middleware('throttle:admin-login')->post('/thaodepzai/login', [AuthController::class, 'login'])->name('backend.login.submit');

Route::middleware(['admin.auth', 'backend.permission', 'backend.activity'])->prefix('admin')->name('backend.')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::get('/banners', [BannerController::class, 'index'])->name('banners');
    Route::get('/banners/create', [BannerController::class, 'create'])->name('banners.create');
    Route::post('/banners', [BannerController::class, 'store'])->name('banners.store');
    Route::get('/banners/{banner}', [BannerController::class, 'show'])->name('banners.show');
    Route::get('/banners/{banner}/edit', [BannerController::class, 'edit'])->name('banners.edit');
    Route::put('/banners/{banner}', [BannerController::class, 'update'])->name('banners.update');
    Route::delete('/banners/{banner}', [BannerController::class, 'destroy'])->name('banners.destroy');

    Route::get('/home-management', [HomeSectionItemController::class, 'index'])->name('home-management');
    Route::get('/home-management/create', [HomeSectionItemController::class, 'create'])->name('home-management.create');
    Route::post('/home-management', [HomeSectionItemController::class, 'store'])->name('home-management.store');
    Route::patch('/home-management/sections/{section}/visibility', [HomeSectionItemController::class, 'updateSectionVisibility'])->name('home-management.sections.visibility');
    Route::get('/home-management/{homeItem}', [HomeSectionItemController::class, 'show'])->name('home-management.show');
    Route::get('/home-management/{homeItem}/edit', [HomeSectionItemController::class, 'edit'])->name('home-management.edit');
    Route::put('/home-management/{homeItem}', [HomeSectionItemController::class, 'update'])->name('home-management.update');
    Route::delete('/home-management/{homeItem}', [HomeSectionItemController::class, 'destroy'])->name('home-management.destroy');

    Route::get('/categories', [CategoryController::class, 'index'])->name('categories');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::patch('/categories/{category}/quick-update-sort', [CategoryController::class, 'quickUpdateSort'])->name('categories.quick-update-sort');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('/products', [ProductController::class, 'index'])->name('products');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::patch('/products/{product}/quick-update', [ProductController::class, 'quickUpdate'])->name('products.quick-update');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::get('/product-colors', [ProductColorController::class, 'index'])->name('product-colors');
    Route::get('/product-colors/create', [ProductColorController::class, 'create'])->name('product-colors.create');
    Route::post('/product-colors', [ProductColorController::class, 'store'])->name('product-colors.store');
    Route::get('/product-colors/{productColor}', [ProductColorController::class, 'show'])->name('product-colors.show');
    Route::get('/product-colors/{productColor}/edit', [ProductColorController::class, 'edit'])->name('product-colors.edit');
    Route::put('/product-colors/{productColor}', [ProductColorController::class, 'update'])->name('product-colors.update');
    Route::delete('/product-colors/{productColor}', [ProductColorController::class, 'destroy'])->name('product-colors.destroy');

    Route::get('/product-sizes', [ProductSizeController::class, 'index'])->name('product-sizes');
    Route::get('/product-sizes/create', [ProductSizeController::class, 'create'])->name('product-sizes.create');
    Route::post('/product-sizes', [ProductSizeController::class, 'store'])->name('product-sizes.store');
    Route::get('/product-sizes/{productSize}', [ProductSizeController::class, 'show'])->name('product-sizes.show');
    Route::get('/product-sizes/{productSize}/edit', [ProductSizeController::class, 'edit'])->name('product-sizes.edit');
    Route::put('/product-sizes/{productSize}', [ProductSizeController::class, 'update'])->name('product-sizes.update');
    Route::delete('/product-sizes/{productSize}', [ProductSizeController::class, 'destroy'])->name('product-sizes.destroy');

    Route::get('/product-tags', [ProductTagController::class, 'index'])->name('product-tags');
    Route::get('/product-tags/create', [ProductTagController::class, 'create'])->name('product-tags.create');
    Route::post('/product-tags', [ProductTagController::class, 'store'])->name('product-tags.store');
    Route::get('/product-tags/{productTag}', [ProductTagController::class, 'show'])->name('product-tags.show');
    Route::get('/product-tags/{productTag}/edit', [ProductTagController::class, 'edit'])->name('product-tags.edit');
    Route::put('/product-tags/{productTag}', [ProductTagController::class, 'update'])->name('product-tags.update');
    Route::delete('/product-tags/{productTag}', [ProductTagController::class, 'destroy'])->name('product-tags.destroy');
    Route::get('/product-targets', [ProductTargetController::class, 'index'])->name('product-targets');
    Route::get('/product-targets/create', [ProductTargetController::class, 'create'])->name('product-targets.create');
    Route::post('/product-targets', [ProductTargetController::class, 'store'])->name('product-targets.store');
    Route::get('/product-targets/{productTarget}', [ProductTargetController::class, 'show'])->name('product-targets.show');
    Route::get('/product-targets/{productTarget}/edit', [ProductTargetController::class, 'edit'])->name('product-targets.edit');
    Route::put('/product-targets/{productTarget}', [ProductTargetController::class, 'update'])->name('product-targets.update');
    Route::delete('/product-targets/{productTarget}', [ProductTargetController::class, 'destroy'])->name('product-targets.destroy');

    Route::get('/promotions', [PromotionController::class, 'index'])->name('promotions');
    Route::get('/promotions/create', [PromotionController::class, 'create'])->name('promotions.create');
    Route::post('/promotions', [PromotionController::class, 'store'])->name('promotions.store');
    Route::get('/promotions/{promotion}', [PromotionController::class, 'show'])->name('promotions.show');
    Route::get('/promotions/{promotion}/edit', [PromotionController::class, 'edit'])->name('promotions.edit');
    Route::put('/promotions/{promotion}', [PromotionController::class, 'update'])->name('promotions.update');
    Route::delete('/promotions/{promotion}', [PromotionController::class, 'destroy'])->name('promotions.destroy');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders');
    Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/edit', [OrderController::class, 'edit'])->name('orders.edit');
    Route::patch('/orders/{order}/mark-verified', [OrderController::class, 'markVerified'])->name('orders.mark-verified');
    Route::put('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
    Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
    Route::get('/payment-invoices', [PaymentInvoiceController::class, 'index'])->name('payment-invoices');
    Route::get('/payment-invoices/{paymentInvoice}', [PaymentInvoiceController::class, 'show'])->name('payment-invoices.show');
    Route::get('/sepay-webhook-logs', [SePayWebhookLogController::class, 'index'])->name('sepay-webhook-logs');

    Route::get('/customers', [CustomerController::class, 'index'])->name('customers');
    Route::get('/customers/ranking', [CustomerController::class, 'ranking'])->name('customers.ranking');
    Route::get('/customers/config', [CustomerController::class, 'config'])->name('customers.config');
    Route::post('/customers/config', [CustomerController::class, 'updateConfig'])->name('customers.config.update');
    Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

    Route::get('/stores', [StoreController::class, 'index'])->name('stores');
    Route::get('/stores/create', [StoreController::class, 'create'])->name('stores.create');
    Route::post('/stores', [StoreController::class, 'store'])->name('stores.store');
    Route::get('/stores/{store}', [StoreController::class, 'show'])->name('stores.show');
    Route::get('/stores/{store}/edit', [StoreController::class, 'edit'])->name('stores.edit');
    Route::put('/stores/{store}', [StoreController::class, 'update'])->name('stores.update');
    Route::delete('/stores/{store}', [StoreController::class, 'destroy'])->name('stores.destroy');

    Route::get('/promo-tickers', [PromoTickerController::class, 'index'])->name('promo-tickers');
    Route::get('/promo-tickers/create', [PromoTickerController::class, 'create'])->name('promo-tickers.create');
    Route::post('/promo-tickers', [PromoTickerController::class, 'store'])->name('promo-tickers.store');
    Route::get('/promo-tickers/{promoTicker}', [PromoTickerController::class, 'show'])->name('promo-tickers.show');
    Route::get('/promo-tickers/{promoTicker}/edit', [PromoTickerController::class, 'edit'])->name('promo-tickers.edit');
    Route::put('/promo-tickers/{promoTicker}', [PromoTickerController::class, 'update'])->name('promo-tickers.update');
    Route::delete('/promo-tickers/{promoTicker}', [PromoTickerController::class, 'destroy'])->name('promo-tickers.destroy');

    Route::get('/footer-links', [FooterLinkController::class, 'index'])->name('footer-links');
    Route::get('/footer-links/create', [FooterLinkController::class, 'create'])->name('footer-links.create');
    Route::post('/footer-links', [FooterLinkController::class, 'store'])->name('footer-links.store');
    Route::get('/footer-links/{footerLink}', [FooterLinkController::class, 'show'])->name('footer-links.show');
    Route::get('/footer-links/{footerLink}/edit', [FooterLinkController::class, 'edit'])->name('footer-links.edit');
    Route::put('/footer-links/{footerLink}', [FooterLinkController::class, 'update'])->name('footer-links.update');
    Route::delete('/footer-links/{footerLink}', [FooterLinkController::class, 'destroy'])->name('footer-links.destroy');

    Route::get('/settings', [SettingController::class, 'index'])->name('settings');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs');

    Route::middleware('admin.only')->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles');
        Route::post('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    });
});
