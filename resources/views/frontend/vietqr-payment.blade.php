@extends('frontend.layouts.app')

@section('title', 'Quét QR thanh toán')

@section('content')
@php
  $vietqrExpireMinutes = max(1, (int) ($frontendPaymentSettings['vietqr_expire_minutes'] ?? 30));
  $isInvoiceExpired = ($invoice->invoice_status ?? '') === 'expired';
  $isInvoiceCancelled = ($invoice->invoice_status ?? '') === 'cancelled';
@endphp
<main class="phone order-success-phone vietqr-payment-phone">
  @include('frontend.partials.topbar', [
    'headerClass' => 'topbar',
  ])

  <section class="success-page vietqr-payment-page"
    data-status-url="{{ URL::signedRoute('frontend.vietqr.payment-status', ['invoice' => $invoice->id]) }}"
    data-success-url="{{ $successUrl ?? '' }}"
    data-cancel-url="{{ URL::signedRoute('frontend.vietqr.payment-cancel', ['invoice' => $invoice->id]) }}"
    data-invoice-code="{{ $invoice->invoice_code }}"
    data-pending-minutes="{{ $vietqrExpireMinutes }}"
    data-invoice-status="{{ $invoice->invoice_status }}">

    <section class="success-card vietqr-payment-success-card{{ !empty($successUrl) ? ' is-visible' : '' }}" data-payment-success-card>
      <div class="vietqr-payment-success-icon" aria-hidden="true">
        <i class="bi bi-check-lg"></i>
      </div>
      <h1>Thanh toán thành công</h1>
      <p class="success-text">Đơn thanh toán của bạn đã được xác nhận. Hệ thống sẽ tự chuyển sang trang đặt hàng thành công sau giây lát.</p>

      <div class="vietqr-payment-success-summary">
        <div class="success-info-row">
          <span>Hóa đơn</span>
          <strong>{{ $invoice->invoice_code }}</strong>
        </div>
        <div class="success-info-row">
          <span>Số tiền</span>
          <strong>{{ number_format((float) $invoice->total_amount, 0, ',', '.') }}đ</strong>
        </div>
        <div class="success-info-row">
          <span>Phương thức</span>
          <strong>Chuyển khoản VietQR</strong>
        </div>
      </div>

      @if (!empty($successUrl))
        <a href="{{ $successUrl }}" class="success-link vietqr-payment-success-link">Xem đơn hàng <i class="bi bi-chevron-double-right"></i></a>
      @endif
    </section>

    <section class="success-card vietqr-payment-expired-card{{ $isInvoiceExpired ? ' is-visible' : '' }}" data-payment-expired-card>
      <div class="vietqr-payment-expired-icon" aria-hidden="true">
        <i class="bi bi-clock-history"></i>
      </div>
      <h1>Hóa đơn đã hết hạn</h1>
      <p class="success-text">Phiên thanh toán VietQR này đã đóng. Vui lòng quay lại giỏ hàng hoặc tạo hóa đơn mới để thanh toán tiếp.</p>
      <div class="vietqr-payment-success-summary">
        <div class="success-info-row">
          <span>Hóa đơn</span>
          <strong>{{ $invoice->invoice_code }}</strong>
        </div>
        <div class="success-info-row">
          <span>Số tiền</span>
          <strong>{{ number_format((float) $invoice->total_amount, 0, ',', '.') }}đ</strong>
        </div>
      </div>
      <a href="{{ route('frontend.cart') }}" class="success-link vietqr-payment-success-link">
        Quay lại giỏ hàng
        <svg class="heroicon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
        </svg>
      </a>
    </section>

    <section class="success-card vietqr-payment-cancelled-card{{ $isInvoiceCancelled ? ' is-visible' : '' }}" data-payment-cancelled-card>
      <div class="vietqr-payment-expired-icon" aria-hidden="true">
        <svg class="heroicon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
      </div>
      <h1>Hóa đơn đã hủy</h1>
      <p class="success-text">Phiên thanh toán VietQR này đã được hủy. Bạn có thể quay lại để tạo đơn mới.</p>
      <div class="vietqr-payment-success-summary">
        <div class="success-info-row">
          <span>Hóa đơn</span>
          <strong>{{ $invoice->invoice_code }}</strong>
        </div>
        <div class="success-info-row">
          <span>Số tiền</span>
          <strong>{{ number_format((float) $invoice->total_amount, 0, ',', '.') }}đ</strong>
        </div>
      </div>

      <div class="vietqr-cancelled-products">
        <h2>Chi tiết sản phẩm</h2>

        @forelse ($invoiceItems as $item)
          <article class="success-product-item">
            <div class="success-product-thumb">
              @if (!empty($item->product_url))
                <a href="{{ $item->product_url }}" class="vietqr-product-link" aria-label="Xem chi tiết {{ $item->product_name_snapshot }}">
                  <img src="{{ $item->image_url }}" alt="{{ $item->product_name_snapshot }}" loading="lazy" decoding="async" fetchpriority="low" />
                </a>
              @else
                <img src="{{ $item->image_url }}" alt="{{ $item->product_name_snapshot }}" loading="lazy" decoding="async" fetchpriority="low" />
              @endif
            </div>

            <div class="success-product-info">
              <h3>
                @if (!empty($item->product_url))
                  <a href="{{ $item->product_url }}" class="vietqr-product-name-link">{{ $item->product_name_snapshot }}</a>
                @else
                  {{ $item->product_name_snapshot }}
                @endif
              </h3>
              <p>{{ $item->variant_name_snapshot ?: '-' }}</p>
              <div class="success-product-bottom">
                <span>Số lượng: {{ (int) $item->qty }}</span>
                <strong>{{ number_format((float) $item->unit_price, 0, ',', '.') }}đ</strong>
              </div>
            </div>
          </article>
        @empty
          <p class="vietqr-cancelled-products__empty">Không có dữ liệu sản phẩm trong hóa đơn này.</p>
        @endforelse
      </div>

      <div class="vietqr-payment-success-links">
        <a href="{{ route('frontend.cart') }}" class="success-link vietqr-payment-success-link">
          <span class="heroicon-button-mark" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-7">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
            </svg>
          </span>
          <span>Về giỏ hàng</span>
        </a>
        <a href="{{ route('frontend.home') }}" class="success-link vietqr-payment-success-link">
          <span class="heroicon-button-mark" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-7">
              <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
            </svg>
          </span>
          <span>Về trang chủ</span>
        </a>
      </div>
    </section>

    <div class="vietqr-payment-main{{ !empty($successUrl) || $isInvoiceExpired || $isInvoiceCancelled ? ' is-hidden' : '' }}" data-payment-pending-block>
      <section class="success-hero-panel">
        <h1>Quét QR để thanh toán</h1>
        <p class="success-text">Vui lòng quét mã QR để chuyển khoản đúng nội dung.</p>
        <p class="success-order-code">Hóa đơn #{{ $invoice->invoice_code }}</p>
        <p class="success-order-code vietqr-payment-status">
          Trạng thái thanh toán:
          @if ($invoice->payment_status === 'paid')
            <span>Đã thanh toán</span>
          @else
            <span class="vietqr-payment-status-waiting">
              <span class="vietqr-payment-spinner" aria-hidden="true"></span>
              <span data-payment-status-label>Chờ thanh toán</span>
            </span>
          @endif
        </p>
      </section>

      @if (!empty($vietqrPayment))
        <section class="success-card vietqr-payment-card">
          <h2>Thông tin chuyển khoản</h2>

          <div class="success-product-item vietqr-payment-layout">
            <div class="success-product-thumb vietqr-payment-thumb">
              <img src="{{ $vietqrPayment['qr_url'] }}" alt="Mã VietQR cho hóa đơn {{ $invoice->invoice_code }}" loading="eager" decoding="async" fetchpriority="high" />
            </div>

            <div class="success-product-info vietqr-payment-info">
              <h3>{{ $vietqrPayment['bank_name'] }}</h3>
              <p>Chủ tài khoản: {{ $vietqrPayment['account_name'] }}</p>
              <p class="vietqr-copy-row">
                <span>Số tài khoản: {{ $vietqrPayment['account_no'] }}</span>
                <button
                  type="button"
                  class="vietqr-copy-button"
                  data-copy-text="{{ $vietqrPayment['account_no'] }}"
                  aria-label="Sao chép số tài khoản"
                  title="Sao chép số tài khoản"
                >
                  <i class="bi bi-copy" aria-hidden="true"></i>
                </button>
              </p>
              <p class="vietqr-copy-row">
                <span>Số tiền: {{ number_format((float) $vietqrPayment['amount'], 0, ',', '.') }}đ</span>
                <button
                  type="button"
                  class="vietqr-copy-button"
                  data-copy-text="{{ number_format((float) $vietqrPayment['amount'], 0, '', '') }}"
                  aria-label="Sao chép số tiền"
                  title="Sao chép số tiền"
                >
                  <i class="bi bi-copy" aria-hidden="true"></i>
                </button>
              </p>
              <p class="vietqr-copy-row">
                <span>Nội dung: {{ $vietqrPayment['transfer_content'] }}</span>
                <button
                  type="button"
                  class="vietqr-copy-button"
                  data-copy-text="{{ $vietqrPayment['transfer_content'] }}"
                  aria-label="Sao chép nội dung chuyển khoản"
                  title="Sao chép nội dung chuyển khoản"
                >
                  <i class="bi bi-copy" aria-hidden="true"></i>
                </button>
              </p>
              <div class="vietqr-payment-actions">
                <button
                  type="button"
                  class="vietqr-payment-download"
                  data-payment-screenshot
                >
                 <i class="bi bi-camera"></i>
                  <span>Chụp màn hình</span>
                </button>
                <button type="button" class="vietqr-payment-cancel" data-payment-cancel>
                  <span class="heroicon-button-mark" aria-hidden="true">
                    <svg class="heroicon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                  </span>
                  <span>Hủy hóa đơn</span>
                </button>
              </div>
            </div>
          </div>
        </section>
      @endif

      <section class="success-card">
        <h2>Thông tin người nhận</h2>

        <div class="success-info-row">
          <span>Người nhận</span>
          <strong>{{ $invoice->customer_name }}</strong>
        </div>

        <div class="success-info-row">
          <span>Số điện thoại</span>
          <strong>{{ $invoice->customer_phone }}</strong>
        </div>

        <div class="success-info-row">
          <span>{{ $invoice->delivery_type === 'pickup' ? 'Cửa hàng nhận' : 'Địa chỉ' }}</span>
          <strong>
            @if ($invoice->delivery_type === 'pickup')
              {{ $selectedStore?->name ?: 'Nhận tại cửa hàng' }}
              @if ($selectedStore)
                - {{ $selectedStore->address_line }}, {{ $selectedStore->district }}, {{ $selectedStore->province }}
              @endif
            @else
              {{ $invoice->shipping_address_text ?: '-' }}
            @endif
          </strong>
        </div>

        <div class="success-info-row total">
          <span>Tổng tiền</span>
          <strong>{{ number_format((float) $invoice->total_amount, 0, ',', '.') }}đ</strong>
        </div>
      </section>

      <section class="success-card">
        <h2>Thông tin sản phẩm</h2>

        @foreach ($invoiceItems as $item)
          <article class="success-product-item">
            <div class="success-product-thumb">
              @if (!empty($item->product_url))
                <a href="{{ $item->product_url }}" class="vietqr-product-link" aria-label="Xem chi tiết {{ $item->product_name_snapshot }}">
                  <img src="{{ $item->image_url }}" alt="{{ $item->product_name_snapshot }}" loading="lazy" decoding="async" fetchpriority="low" />
                </a>
              @else
                <img src="{{ $item->image_url }}" alt="{{ $item->product_name_snapshot }}" loading="lazy" decoding="async" fetchpriority="low" />
              @endif
            </div>

            <div class="success-product-info">
              <h3>
                @if (!empty($item->product_url))
                  <a href="{{ $item->product_url }}" class="vietqr-product-name-link">{{ $item->product_name_snapshot }}</a>
                @else
                  {{ $item->product_name_snapshot }}
                @endif
              </h3>
              <p>{{ $item->variant_name_snapshot ?: '-' }}</p>
              <div class="success-product-bottom">
                <span>Số lượng: {{ (int) $item->qty }}</span>
                <strong>{{ number_format((float) $item->unit_price, 0, ',', '.') }}đ</strong>
              </div>
            </div>
          </article>
        @endforeach
      </section>
    </div>
  </section>

  <div class="vietqr-popup" data-vietqr-popup hidden>
    <div class="vietqr-popup__backdrop" data-vietqr-popup-dismiss></div>
    <div class="vietqr-popup__dialog" role="dialog" aria-modal="true" aria-labelledby="vietqr-popup-title">
      <div class="vietqr-popup__icon" aria-hidden="true" data-vietqr-popup-icon>
        <i class="bi bi-info-circle"></i>
      </div>
      <h2 id="vietqr-popup-title" data-vietqr-popup-title>Thông báo</h2>
      <p data-vietqr-popup-message></p>
      <div class="vietqr-popup__actions">
        <button type="button" class="vietqr-popup__button vietqr-popup__button--ghost" data-vietqr-popup-cancel hidden>Đóng</button>
        <button type="button" class="vietqr-popup__button" data-vietqr-popup-confirm>Đã hiểu</button>
      </div>
    </div>
  </div>
</main>

@push('head')
  <style>
    @if ($isInvoiceExpired || $isInvoiceCancelled)
      [data-vietqr-resume] {
        display: none !important;
      }
    @endif

    .vietqr-payment-success-card {
      display: none;
      text-align: center;
      padding-top: 28px;
      padding-bottom: 28px;
    }

    .vietqr-payment-success-card.is-visible {
      display: block;
    }

    .vietqr-payment-expired-card {
      display: none;
      text-align: center;
      padding-top: 28px;
      padding-bottom: 28px;
    }

    .vietqr-payment-expired-card.is-visible {
      display: block;
    }

    .vietqr-payment-cancelled-card {
      display: none;
      text-align: center;
      padding-top: 28px;
      padding-bottom: 28px;
    }

    .vietqr-payment-cancelled-card.is-visible {
      display: block;
    }

    .vietqr-payment-main.is-hidden {
      display: none;
    }

    .heroicon {
      width: 1.25em;
      height: 1.25em;
      display: inline-block;
      flex: 0 0 auto;
      vertical-align: -0.18em;
    }

    .size-6 {
      width: 1.5rem;
      height: 1.5rem;
      display: inline-block;
      flex: 0 0 auto;
    }

    .size-7 {
      width: 1.75rem;
      height: 1.75rem;
      display: inline-block;
      flex: 0 0 auto;
    }

    .heroicon-button-mark {
      width: 26px;
      height: 26px;
      border-radius: 999px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: rgba(22, 51, 88, 0.08);
      flex: 0 0 26px;
    }

    .heroicon-button-mark .heroicon {
      width: 16px;
      height: 16px;
    }

    .heroicon-button-mark .size-6 {
      width: 19px;
      height: 19px;
    }

    .heroicon-button-mark svg,
    .heroicon-button-mark .size-7 {
      width: 19px;
      height: 19px;
      display: block;
    }

    .vietqr-cancelled-products {
      margin-top: 18px;
      text-align: left;
    }

    .vietqr-cancelled-products h2 {
      margin: 0 0 12px;
      font-size: 0.98rem;
      line-height: 1.3;
      color: #17201f;
    }

    .vietqr-cancelled-products .success-product-item {
      margin-bottom: 10px;
      align-items: flex-start;
    }

    .vietqr-cancelled-products .success-product-item:last-child {
      margin-bottom: 0;
    }

    .vietqr-cancelled-products .success-product-thumb {
      width: 76px;
      height: 76px;
      flex: 0 0 76px;
    }

    .vietqr-cancelled-products .success-product-info {
      min-width: 0;
    }

    .vietqr-cancelled-products .success-product-info h3 {
      margin: 0 0 4px;
      font-size: 0.84rem;
      line-height: 1.35;
      font-weight: 700;
      color: #17201f;
    }

    .vietqr-cancelled-products .success-product-info p {
      margin: 0;
      font-size: 0.74rem;
      line-height: 1.35;
      color: #66736f;
    }

    .vietqr-cancelled-products .success-product-bottom {
      margin-top: 7px;
      font-size: 0.76rem;
      line-height: 1.35;
    }

    .vietqr-cancelled-products .success-product-bottom strong {
      font-size: 0.8rem;
    }

    .vietqr-cancelled-products__empty {
      margin: 0;
      color: #66736f;
      font-size: 0.88rem;
      line-height: 1.45;
    }

    .vietqr-product-link {
      display: block;
      width: 100%;
      height: 100%;
      color: inherit;
      text-decoration: none;
    }

    .vietqr-product-name-link {
      color: inherit;
      text-decoration: none;
    }

    .vietqr-product-link:hover,
    .vietqr-product-name-link:hover {
      color: #005147;
    }

    .vietqr-payment-success-icon {
      width: 84px;
      height: 84px;
      margin: 0 auto 18px;
      border-radius: 999px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #dff8e8;
      color: #14a44d;
      font-size: 40px;
    }

    .vietqr-payment-success-card h1 {
      margin-bottom: 12px;
      color: #14a44d;
    }

    .vietqr-payment-expired-icon {
      width: 84px;
      height: 84px;
      margin: 0 auto 18px;
      border-radius: 999px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #fff4dc;
      color: #d28b00;
      font-size: 38px;
    }

    .vietqr-payment-expired-icon .heroicon {
      width: 42px;
      height: 42px;
    }

    .vietqr-payment-expired-card h1 {
      margin-bottom: 12px;
      color: #b7791f;
    }

    .vietqr-payment-success-summary {
      margin-top: 20px;
      padding: 18px 18px 8px;
      border-radius: 20px;
      background: #f4f6fb;
    }

    .vietqr-payment-success-link {
      margin-top: 16px;
      justify-content: center;
    }

    .vietqr-payment-success-links {
      margin-top: 16px;
      display: flex;
      flex-direction: row;
      gap: 10px;
      justify-content: center;
    }

    .vietqr-payment-success-links .vietqr-payment-success-link {
      margin-top: 0;
      flex: 1 1 0;
      min-width: 0;
      min-height: 46px;
      padding: 10px 16px 10px 12px;
      border: 1px solid rgba(22, 51, 88, 0.12);
      border-radius: 16px;
      background: linear-gradient(180deg, #ffffff 0%, #f6f9fd 100%);
      color: #163358;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      box-shadow: 0 12px 24px rgba(22, 51, 88, 0.08);
      transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease, background 0.2s ease;
    }

    .vietqr-payment-success-links .vietqr-payment-success-link:hover {
      color: #163358;
      border-color: rgba(22, 51, 88, 0.22);
      background: linear-gradient(180deg, #ffffff 0%, #eef4fb 100%);
      transform: translateY(-1px);
      box-shadow: 0 16px 28px rgba(22, 51, 88, 0.12);
    }

    .vietqr-payment-layout {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 18px;
      text-align: center;
    }

    .vietqr-payment-thumb {
      width: min(88vw, 360px);
      height: min(88vw, 360px);
      margin: 0 auto;
      border-radius: 24px;
      background: #fff;
      padding: 14px;
      box-shadow: 0 16px 36px rgba(21, 34, 56, 0.08);
    }

    .vietqr-payment-thumb img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      border-radius: 16px;
      display: block;
    }

    .vietqr-payment-info {
      width: 100%;
      text-align: center;
    }

    .vietqr-payment-info h3 {
      margin-top: 0;
      margin-bottom: 10px;
    }

    .vietqr-payment-info p {
      margin: 2px 0;
    }

    .vietqr-copy-row {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      flex-wrap: nowrap;
      width: 100%;
      margin-left: 0;
      margin-right: 0;
    }

    .vietqr-copy-button {
      width: 28px;
      height: 28px;
      border: 1px solid #dbe5f2;
      background: #f6f9ff;
      color: #163358;
      border-radius: 999px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      flex: 0 0 28px;
      transition: all .2s ease;
    }

    .vietqr-copy-button i {
      font-size: 14px;
      line-height: 1;
    }

    .vietqr-copy-button:hover {
      background: #e8f0ff;
      border-color: #c9d9f3;
    }

    .vietqr-copy-button.is-copied {
      background: #1b9f5a;
      border-color: #1b9f5a;
      color: #fff;
    }

    .vietqr-payment-actions {
      margin-top: 16px;
      display: flex;
      justify-content: center;
      gap: 10px;
      flex-wrap: wrap;
    }

    .vietqr-payment-download {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      min-height: 46px;
      padding: 10px 18px 10px 12px;
      border: 1px solid rgba(22, 51, 88, 0.12);
      border-radius: 16px;
      background: linear-gradient(180deg, #ffffff 0%, #f6f9fd 100%);
      color: #163358;
      text-decoration: none;
      font-size: 14px;
      font-weight: 700;
      box-shadow: 0 12px 24px rgba(22, 51, 88, 0.08);
      transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease, background 0.2s ease;
    }

    .vietqr-payment-download i {
      width: 26px;
      height: 26px;
      border-radius: 999px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: rgba(22, 51, 88, 0.08);
      font-size: 14px;
    }

    .vietqr-payment-download:hover {
      color: #163358;
      border-color: rgba(22, 51, 88, 0.22);
      background: linear-gradient(180deg, #ffffff 0%, #eef4fb 100%);
      transform: translateY(-1px);
      box-shadow: 0 16px 28px rgba(22, 51, 88, 0.12);
    }

    .vietqr-payment-cancel {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      min-height: 46px;
      padding: 10px 18px 10px 12px;
      border: 1px solid rgba(22, 51, 88, 0.12);
      border-radius: 16px;
      background: linear-gradient(180deg, #ffffff 0%, #f6f9fd 100%);
      color: #163358;
      font-size: 14px;
      font-weight: 700;
      box-shadow: 0 12px 24px rgba(22, 51, 88, 0.08);
      transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease, background 0.2s ease;
      cursor: pointer;
    }

    .vietqr-payment-cancel:hover {
      border-color: rgba(22, 51, 88, 0.22);
      background: linear-gradient(180deg, #ffffff 0%, #eef4fb 100%);
      transform: translateY(-1px);
      box-shadow: 0 16px 28px rgba(22, 51, 88, 0.12);
    }

    .vietqr-popup {
      position: fixed;
      inset: 0;
      z-index: 1450;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .vietqr-popup[hidden] {
      display: none !important;
    }

    .vietqr-popup__backdrop {
      position: absolute;
      inset: 0;
      background: rgba(12, 24, 44, 0.48);
      backdrop-filter: blur(2px);
    }

    .vietqr-popup__dialog {
      position: relative;
      width: min(100%, 360px);
      padding: 24px 20px 18px;
      border-radius: 24px;
      background: #fff;
      text-align: center;
      box-shadow: 0 24px 50px rgba(10, 31, 58, 0.18);
    }

    .vietqr-popup__icon {
      width: 56px;
      height: 56px;
      margin: 0 auto 14px;
      border-radius: 999px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #eaf2ff;
      color: #1f5d8d;
      font-size: 24px;
    }

    .vietqr-popup__icon.is-danger {
      background: #fff1eb;
      color: #c2410c;
    }

    .vietqr-popup__dialog h2 {
      margin: 0 0 10px;
      color: #163358;
      font-size: 20px;
    }

    .vietqr-popup__dialog p {
      margin: 0;
      color: #4b5563;
      font-size: 14px;
      line-height: 1.6;
    }

    .vietqr-popup__actions {
      margin-top: 18px;
      display: flex;
      justify-content: center;
      gap: 10px;
    }

    .vietqr-popup__button {
      min-width: 118px;
      min-height: 44px;
      border: 0;
      border-radius: 999px;
      background: #163358;
      color: #fff;
      font-size: 14px;
      font-weight: 700;
      cursor: pointer;
    }

    .vietqr-popup__button--ghost {
      background: #eef2f7;
      color: #163358;
    }

    .vietqr-payment-status-waiting {
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .vietqr-payment-auto-note {
      margin-top: 8px;
      color: #6b7280;
      font-size: 14px;
    }

    .vietqr-payment-spinner {
      width: 14px;
      height: 14px;
      border-radius: 999px;
      border: 2px solid rgba(22, 51, 88, 0.18);
      border-top-color: #163358;
      animation: vietqr-spin 0.9s linear infinite;
    }

    @keyframes vietqr-spin {
      from {
        transform: rotate(0deg);
      }

      to {
        transform: rotate(360deg);
      }
    }
  </style>
@endpush

@push('scripts')
  <script>
    (function () {
      const vietqrResumeStorageKey = 'shopnoiy:vietqr-pending:v1';
      const page = document.querySelector('.vietqr-payment-page');
      if (!page) {
        return;
      }

      const statusUrl = page.dataset.statusUrl;
      const cancelUrl = page.dataset.cancelUrl || '';
      const invoiceCode = page.dataset.invoiceCode || '';
      const invoiceStatus = page.dataset.invoiceStatus || '';
      const pendingMinutes = Number(page.dataset.pendingMinutes || 30) || 30;
      const pendingBlock = page.querySelector('[data-payment-pending-block]');
      const successCard = page.querySelector('[data-payment-success-card]');
      const expiredCard = page.querySelector('[data-payment-expired-card]');
      const cancelledCard = page.querySelector('[data-payment-cancelled-card]');
      const cancelButton = page.querySelector('[data-payment-cancel]');
      const popup = document.querySelector('[data-vietqr-popup]');
      const popupIcon = document.querySelector('[data-vietqr-popup-icon]');
      const popupTitle = document.querySelector('[data-vietqr-popup-title]');
      const popupMessage = document.querySelector('[data-vietqr-popup-message]');
      const popupConfirm = document.querySelector('[data-vietqr-popup-confirm]');
      const popupCancel = document.querySelector('[data-vietqr-popup-cancel]');
      const statusLabel = page.querySelector('[data-payment-status-label]');
      const autoNote = page.querySelector('[data-payment-auto-note]');
      const resumeWidget = document.querySelector('[data-vietqr-resume]');
      let redirected = false;
      const copyTimers = new WeakMap();

      const fallbackCopyText = function (text) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.setAttribute('readonly', '');
        textArea.style.position = 'fixed';
        textArea.style.top = '-9999px';
        document.body.appendChild(textArea);
        textArea.select();
        const isCopied = document.execCommand('copy');
        document.body.removeChild(textArea);
        return isCopied;
      };

      const copyText = function (text) {
        if (!text) {
          return Promise.resolve(false);
        }

        if (navigator.clipboard && window.isSecureContext) {
          return navigator.clipboard.writeText(text)
            .then(function () {
              return true;
            })
            .catch(function () {
              return fallbackCopyText(text);
            });
        }

        return Promise.resolve(fallbackCopyText(text));
      };

      const markCopyResult = function (button, isSuccess) {
        if (!button) {
          return;
        }

        const icon = button.querySelector('i');
        if (!icon) {
          return;
        }

        if (copyTimers.has(button)) {
          window.clearTimeout(copyTimers.get(button));
        }

        if (isSuccess) {
          button.classList.add('is-copied');
          icon.className = 'bi bi-check2';
          copyTimers.set(button, window.setTimeout(function () {
            button.classList.remove('is-copied');
            icon.className = 'bi bi-copy';
          }, 1400));
          return;
        }

        button.classList.remove('is-copied');
        icon.className = 'bi bi-x';
        copyTimers.set(button, window.setTimeout(function () {
          icon.className = 'bi bi-copy';
        }, 1000));
      };

      const hidePopup = function () {
        if (!popup) {
          return;
        }

        popup.hidden = true;
        document.body.style.overflow = '';
      };

      const showPopup = function (options) {
        const settings = options || {};

        return new Promise(function (resolve) {
          if (!popup || !popupTitle || !popupMessage || !popupConfirm || !popupCancel || !popupIcon) {
            resolve(false);
            return;
          }

          popupTitle.textContent = settings.title || 'Thông báo';
          popupMessage.textContent = settings.message || '';
          popupConfirm.textContent = settings.confirmText || 'Đã hiểu';
          popupCancel.textContent = settings.cancelText || 'Đóng';
          popupCancel.hidden = settings.type !== 'confirm';
          popupIcon.classList.toggle('is-danger', settings.tone === 'danger');
          popup.hidden = false;
          document.body.style.overflow = 'hidden';

          const finalize = function (value) {
            popupConfirm.removeEventListener('click', handleConfirm);
            popupCancel.removeEventListener('click', handleCancel);
            popup.querySelectorAll('[data-vietqr-popup-dismiss]').forEach(function (element) {
              element.removeEventListener('click', handleCancel);
            });
            hidePopup();
            resolve(value);
          };

          const handleConfirm = function () {
            finalize(true);
          };

          const handleCancel = function () {
            finalize(false);
          };

          popupConfirm.addEventListener('click', handleConfirm);
          popupCancel.addEventListener('click', handleCancel);
          popup.querySelectorAll('[data-vietqr-popup-dismiss]').forEach(function (element) {
            element.addEventListener('click', handleCancel);
          });
        });
      };

      const savePendingPayment = function () {
        if (!statusUrl || page.dataset.successUrl) {
          return;
        }

        let existingPayment = null;
        try {
          const payload = JSON.parse(window.localStorage.getItem(vietqrResumeStorageKey) || 'null');
          existingPayment = payload && typeof payload === 'object' ? payload : null;
        } catch (error) {
          existingPayment = null;
        }

        const now = Date.now();
        const existingExpiresAt = Number(existingPayment?.expires_at || 0);
        const shouldReuseExistingTimer = existingPayment
          && existingExpiresAt > now
          && String(existingPayment.invoice_code || '') === String(invoiceCode)
          && String(existingPayment.status_url || '') === String(statusUrl);

        window.localStorage.setItem(vietqrResumeStorageKey, JSON.stringify({
          url: window.location.href,
          status_url: statusUrl,
          invoice_code: invoiceCode,
          expires_at: shouldReuseExistingTimer
            ? existingExpiresAt
            : now + (pendingMinutes * 60 * 1000)
        }));
      };

      const clearPendingPayment = function () {
        window.localStorage.removeItem(vietqrResumeStorageKey);
      };

      const showSuccess = function (successUrl) {
        clearPendingPayment();

        if (pendingBlock) {
          pendingBlock.classList.add('is-hidden');
        }

        if (successCard) {
          successCard.classList.add('is-visible');
        }

        if (!successUrl || redirected) {
          return;
        }

        redirected = true;
        window.setTimeout(function () {
          window.location.href = successUrl;
        }, 1800);
      };

      const showExpired = function () {
        clearPendingPayment();

        if (resumeWidget) {
          resumeWidget.hidden = true;
        }

        if (pendingBlock) {
          pendingBlock.classList.add('is-hidden');
        }

        if (successCard) {
          successCard.classList.remove('is-visible');
        }

        if (expiredCard) {
          expiredCard.classList.add('is-visible');
        }
      };

      const showCancelled = function () {
        clearPendingPayment();

        if (resumeWidget) {
          resumeWidget.hidden = true;
        }

        if (pendingBlock) {
          pendingBlock.classList.add('is-hidden');
        }

        if (successCard) {
          successCard.classList.remove('is-visible');
        }

        if (expiredCard) {
          expiredCard.classList.remove('is-visible');
        }

        if (cancelledCard) {
          cancelledCard.classList.add('is-visible');
        }
      };

      if (page.dataset.successUrl) {
        showSuccess(page.dataset.successUrl);
        return;
      }

      if (invoiceStatus === 'expired') {
        showExpired();
        return;
      }

      if (invoiceStatus === 'cancelled') {
        showCancelled();
        return;
      }

      if (!statusUrl) {
        return;
      }

      savePendingPayment();

      const pollStatus = function () {
        window.fetch(statusUrl, {
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          credentials: 'same-origin'
        })
          .then(function (response) {
            if (!response.ok) {
              throw new Error('Failed to fetch payment status.');
            }

            return response.json();
          })
          .then(function (data) {
            if (!data || data.success !== true) {
              return;
            }

            if (data.is_expired || data.invoice_status === 'expired') {
              showExpired();
              return;
            }

            if (data.is_cancelled || data.invoice_status === 'cancelled') {
              showCancelled();
              return;
            }

            if (data.payment_status === 'paid' && data.success_url) {
              showSuccess(data.success_url);
              return;
            }

            if (statusLabel && data.payment_status !== 'paid') {
              statusLabel.textContent = 'Chờ thanh toán';
            }
          })
          .catch(function () {
            if (autoNote) {
              autoNote.textContent = 'Đang kiểm tra trạng thái thanh toán...';
            }
          });
      };

      pollStatus();
      window.setInterval(pollStatus, 4000);

      document.addEventListener('click', function (event) {
        const copyButton = event.target.closest('[data-copy-text]');

        if (copyButton) {
          event.preventDefault();
          copyText((copyButton.dataset.copyText || '').trim())
            .then(function (isSuccess) {
              markCopyResult(copyButton, isSuccess);
            })
            .catch(function () {
              markCopyResult(copyButton, false);
            });
          return;
        }

        const screenshotButton = event.target.closest('[data-payment-screenshot]');

        if (screenshotButton) {
          event.preventDefault();

          showPopup({
            title: 'Hướng dẫn thanh toán',
            message: 'Hãy chụp ảnh màn hình mã QR, sau đó mở ứng dụng ngân hàng và chọn quét mã từ thư viện ảnh để thanh toán.'
          });
          return;
        }

        const button = event.target.closest('[data-payment-cancel]');

        if (!button || !cancelUrl) {
          return;
        }

        event.preventDefault();

        showPopup({
          type: 'confirm',
          tone: 'danger',
          title: 'Hủy hóa đơn',
          message: 'Bạn có chắc muốn hủy hóa đơn VietQR này không?',
          confirmText: 'Hủy hóa đơn',
          cancelText: 'Quay lại'
        }).then(function (confirmed) {
          if (!confirmed) {
            return;
          }

          button.disabled = true;

          window.fetch(cancelUrl, {
            method: 'POST',
            headers: {
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-TOKEN': @json(csrf_token()),
            },
            credentials: 'same-origin'
          })
            .then(function (response) {
              return response.json().then(function (data) {
                return {
                  ok: response.ok,
                  data: data
                };
              });
            })
            .then(function (result) {
              const errorMessage = result.data && result.data.message
                ? result.data.message
                : 'Không thể hủy hóa đơn lúc này.';

              if (!result.ok || !result.data || result.data.success !== true) {
                showPopup({
                  title: 'Không thể hủy',
                  message: errorMessage
                });
                button.disabled = false;
                return;
              }

              showCancelled();
            })
            .catch(function () {
              showPopup({
                title: 'Không thể hủy',
                message: 'Không thể hủy hóa đơn lúc này.'
              });
              button.disabled = false;
            });
        });
      });
    })();
  </script>
@endpush
@endsection
