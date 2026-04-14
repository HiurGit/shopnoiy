@extends('frontend.layouts.app')

@section('title', 'Đặt hàng thành công')

@section('content')
@php
  $paymentMethodLabel = match ($order->payment_method) {
    'vietqr' => 'VietQR',
    'cod' => 'Thanh toán khi nhận hàng (COD)',
    default => strtoupper((string) $order->payment_method),
  };
  $isPaid = $order->payment_status === 'paid';
  $isPendingVietqr = $order->payment_method === 'vietqr' && !$isPaid;
  $pageTitle = $isPendingVietqr ? 'Đơn hàng đã tạo' : 'Đặt hàng thành công';
  $pageText = $isPendingVietqr
    ? 'Đơn hàng của bạn đã được ghi nhận. Vui lòng quét mã VietQR bên dưới để chuyển khoản.'
    : 'Cảm ơn bạn đã mua sắm tại Shop Nội Y';
  $paymentStatusLabel = $isPaid ? 'Đã thanh toán' : 'Chưa thanh toán';
  $paymentStatusClass = $isPaid ? 'success-paid' : 'success-pending';
  $orderStatusLabel = $order->order_status_label ?? 'Đã tiếp nhận';
  $subtotalAmount = (float) ($order->subtotal ?? $orderItems->sum(fn ($item) => ((float) $item->unit_price * (int) $item->qty)));
  $discountAmount = (float) ($order->discount_amount ?? 0);
  $shippingFee = (float) ($order->shipping_fee ?? 0);
  $recipientHeading = $order->delivery_type === 'pickup' ? 'Điểm nhận hàng' : 'Địa chỉ nhận hàng';
  $recipientAddress = $order->delivery_type === 'pickup'
    ? trim(($selectedStore?->name ?: 'Nhận tại cửa hàng') . ($selectedStore ? ' - ' . $selectedStore->address_line . ', ' . $selectedStore->district . ', ' . $selectedStore->province : ''))
    : ($order->shipping_address_text ?: '-');
@endphp
<main class="phone order-success-phone">
  @include('frontend.partials.topbar', [
    'headerClass' => 'topbar',
  ])

  <section class="success-page">
    <section class="success-hero-panel">
      <div class="success-icon">
        <i class="bi {{ $isPendingVietqr ? 'bi-clock-history' : 'bi-check' }}"></i>
      </div>

      <h1>{{ $pageTitle }}</h1>
      <p class="success-text">{{ $pageText }}</p>
      @if (session('payment_notice'))
        <p class="success-subtext {{ $paymentStatusClass }}">{{ session('payment_notice') }}</p>
      @endif
    </section>

    <section class="success-card success-detail-card">
      <div class="success-order-banner">
        <div>
          <p class="success-detail-label">Đơn hàng</p>
          <h2 class="success-order-banner-title">{{ $order->order_code }}</h2>
        </div>
        <span class="success-status-badge">{{ $orderStatusLabel }}</span>
      </div>

      <div class="success-detail-section">
        <p class="success-detail-label">Hình thức thanh toán</p>
        <div class="success-detail-value">{{ $paymentMethodLabel }}</div>
        <p class="success-detail-note">Trạng thái thanh toán: {{ $paymentStatusLabel }}</p>
      </div>

      <div class="success-detail-section">
        <p class="success-detail-label">{{ $recipientHeading }}</p>
        <div class="success-detail-contact">
          <strong>{{ $order->customer_name }}</strong>
          <span>{{ $order->customer_phone }}</span>
        </div>
        <div class="success-detail-address">{{ $recipientAddress }}</div>
      </div>

      <div class="success-detail-section">
        <p class="success-detail-label">Thông tin sản phẩm</p>

        @foreach ($orderItems as $item)
          <article class="success-product-item success-product-item--receipt">
            <div class="success-product-thumb">
              <img src="{{ $item->image_url }}" alt="{{ $item->product_name_snapshot }}" loading="lazy" decoding="async" fetchpriority="low" />
            </div>

            <div class="success-product-info">
              <div class="success-product-head">
                <h3>{{ $item->product_name_snapshot }}</h3>
                <strong class="success-product-price">{{ number_format((float) $item->unit_price, 0, ',', '.') }}đ</strong>
              </div>
              <p>{{ $item->variant_name_snapshot ?: '-' }}</p>
              <div class="success-product-bottom">
                <span class="success-qty-badge">{{ (int) $item->qty }}</span>
                <span>Số lượng: {{ (int) $item->qty }}</span>
              </div>
            </div>
          </article>
        @endforeach

        <div class="success-total-list">
          <div class="success-total-row">
            <span>Tạm tính</span>
            <strong>{{ number_format($subtotalAmount, 0, ',', '.') }}đ</strong>
          </div>
          <div class="success-total-row">
            <span>Giảm giá</span>
            <strong>{{ number_format($discountAmount, 0, ',', '.') }}đ</strong>
          </div>
          <div class="success-total-row">
            <span>Phí vận chuyển</span>
            <strong>{{ number_format($shippingFee, 0, ',', '.') }}đ</strong>
          </div>
          <div class="success-total-row success-total-row--grand">
            <span>Tổng thanh toán</span>
            <strong>{{ number_format((float) $order->total_amount, 0, ',', '.') }}đ</strong>
          </div>
        </div>
      </div>
    </section>

    @if (!empty($vietqrPayment) && $order->payment_status !== 'paid')
      <section class="success-card">
        <h2>Thanh toán bằng VietQR</h2>

        <div class="success-product-item">
          <div class="success-product-thumb">
            <img src="{{ $vietqrPayment['qr_url'] }}" alt="Mã VietQR cho đơn {{ $order->order_code }}" loading="eager" decoding="async" fetchpriority="high" />
          </div>

          <div class="success-product-info">
            <h3>{{ $vietqrPayment['bank_name'] }}</h3>
            <p>Chủ tài khoản: {{ $vietqrPayment['account_name'] }}</p>
            <p>Số tài khoản: {{ $vietqrPayment['account_no'] }}</p>
            <p>Số tiền: {{ number_format((float) $vietqrPayment['amount'], 0, ',', '.') }}đ</p>
            <p>Nội dung: {{ $vietqrPayment['transfer_content'] }}</p>
          </div>
        </div>
      </section>
    @endif

    <a href="{{ route('frontend.home') }}" class="success-link success-link-bottom">
   <i class="bi bi-shop"></i>
      <span>Tiếp tục mua sắm</span>
    </a>
  </section>
</main>

@if (
  $order->payment_method === 'cod'
  || ($order->payment_method === 'vietqr' && $order->payment_status === 'paid')
)
  @push('scripts')
    <script>
      if (window.ShopNoiyCart) {
        window.ShopNoiyCart.clear();
      }
    </script>
  @endpush
@endif
@endsection
