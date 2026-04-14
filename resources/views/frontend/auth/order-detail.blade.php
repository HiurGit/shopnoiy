@extends('frontend.layouts.app')

@section('title', 'Chi tiết đơn hàng')
@section('meta_title', 'Chi tiết đơn hàng khách hàng')
@section('meta_description', 'Trang chi tiết đơn hàng của khách hàng tại Shop Nội Y Buôn Hồ.')
@section('meta_robots', 'noindex,nofollow')

@push('head')
  <style>
    .order-detail-page {
      min-height: 100vh;
      background: #ffffff;
      color: #1b2322;
      font-family: 'Be Vietnam Pro', sans-serif;
      padding: 60px 0 36px;
    }

    .order-detail-shell {
      max-width: 430px;
      margin: 0 auto;
    }

    .order-detail-card {
      padding: 20px 16px 24px;
    }

    .order-detail-block {
      background: #f7f9f8;
      border: 1px solid #e3ece8;
      border-radius: 16px;
      padding: 14px;
      margin-bottom: 12px;
    }

    .order-detail-block h2 {
      margin: 0 0 10px;
      font-size: 0.98rem;
      color: #17201f;
    }

    .order-detail-row {
      display: flex;
      justify-content: space-between;
      gap: 10px;
      font-size: 0.9rem;
      color: #42514d;
      padding: 6px 0;
      border-bottom: 1px dashed #d6dfdb;
    }

    .order-detail-row:last-child {
      border-bottom: 0;
    }

    .order-detail-row strong {
      color: #152120;
      font-weight: 700;
      text-align: right;
    }

    .order-detail-item {
      background: #ffffff;
      /* border: 1px solid #e6ecea; */
      border-radius: 14px;
      padding: 12px;
      margin-bottom: 10px;
      display: grid;
      grid-template-columns: 62px minmax(0, 1fr);
      column-gap: 10px;
      row-gap: 2px;
      align-items: start;
    }

    .order-detail-item:last-child {
      margin-bottom: 0;
    }

    .order-detail-item__top {
      display: flex;
      align-items: flex-start;
      gap: 10px;
    }

    .order-detail-item__thumb {
      width: 62px;
      height: 62px;
      margin-bottom: 0;
      border-radius: 10px;
      border: 1px solid #e6ecea;
      object-fit: cover;
      flex-shrink: 0;
      display: block;
      background: #f5f8f7;
      grid-column: 1;
      grid-row: 1 / 3;
    }

    .order-detail-item__content {
      min-width: 0;
      flex: 1;
    }

    .order-detail-item__name {
      margin: 0;
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 8px;
      color: #17201f;
      font-size: 0.92rem;
      font-weight: 700;
      line-height: 1.45;
      grid-column: 2;
    }

    .order-detail-item__title-row {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 8px;
    }

    .order-detail-item__price {
      color: #005147;
      font-size: 0.9rem;
      font-weight: 700;
      white-space: nowrap;
    }

    .order-detail-item__variant {
      margin: 4px 0 0;
      color: #60706c;
      font-size: 0.85rem;
      grid-column: 2;
    }

    .order-detail-item__meta {
      display: flex;
      justify-content: flex-end;
      align-items: center;
      margin-top: 8px;
      font-size: 0.86rem;
      color: #42514d;
      grid-column: 1 / -1;
    }

    .order-detail-item__line-total {
      color: #005147;
      font-weight: 700;
    }

    .order-empty {
      margin: 0;
      padding: 12px;
      border-radius: 12px;
      background: #ffffff;
      border: 1px solid #e6ecea;
      color: #677571;
      font-size: 0.9rem;
      text-align: center;
    }

    .order-detail-back {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 100%;
      margin-top: 6px;
      padding: 12px 14px;
      border-radius: 999px;
      border: 1px solid #d7dfdc;
      color: #17201f;
      text-decoration: none;
      font-size: 0.92rem;
    }
  </style>
@endpush

@section('content')
  <main class="phone order-detail-page">
    @include('frontend.partials.topbar', [
      'headerClass' => 'topbar',
    ])

    <section class="cart-subhead">
      <a href="{{ route('frontend.profile.orders') }}" class="cart-subhead-back" aria-label="Quay lại lịch sử mua hàng">
        <i class="bi bi-arrow-left"></i>
      </a>
      <h1>Chi tiết đơn hàng</h1>
      <span class="cart-subhead-spacer"></span>
    </section>

    <div class="order-detail-shell">
      <section class="order-detail-card">
        @php
          $paymentMethod = strtolower((string) ($customerOrder->payment_method ?? 'cod'));
          $paymentStatus = strtolower((string) ($customerOrder->payment_status ?? 'unpaid'));

          $paymentMethodLabels = [
            'cod' => 'Thanh toán khi nhận hàng',
            'cash' => 'Tiền mặt',
            'bank_transfer' => 'Chuyển khoản ngân hàng',
            'bank' => 'Chuyển khoản ngân hàng',
            'vnpay' => 'VNPay',
            'momo' => 'MoMo',
            'zalopay' => 'ZaloPay',
            'stripe' => 'Thẻ quốc tế',
            'paypal' => 'PayPal',
          ];

          $paymentStatusLabels = [
            'unpaid' => 'Chưa thanh toán',
            'pending' => 'Chờ thanh toán',
            'paid' => 'Đã thanh toán',
            'failed' => 'Thanh toán thất bại',
            'cancelled' => 'Đã hủy',
            'refunded' => 'Đã hoàn tiền',
            'partially_refunded' => 'Hoàn tiền một phần',
          ];
        @endphp

        <div class="order-detail-block">
          <h2>Thông tin đơn hàng</h2>
          <div class="order-detail-row">
            <span>Mã đơn</span>
            <strong>{{ $customerOrder->order_code ?: ('#' . $customerOrder->id) }}</strong>
          </div>
          <div class="order-detail-row">
            <span>Trạng thái</span>
            <strong>{{ $customerOrder->order_status_label ?? ucfirst((string) $customerOrder->order_status) }}</strong>
          </div>
          <div class="order-detail-row">
            <span>Ngày đặt</span>
            <strong>{{ optional($customerOrder->created_at)->format('d/m/Y H:i') ?: '-' }}</strong>
          </div>
          <div class="order-detail-row">
            <span>Tổng tiền</span>
            <strong>{{ number_format((float) $customerOrder->total_amount, 0, ',', '.') }}đ</strong>
          </div>
        </div>

        <div class="order-detail-block">
          <h2>Giao nhận & thanh toán</h2>
          <div class="order-detail-row">
            <span>Hình thức nhận</span>
            <strong>{{ (string) $customerOrder->delivery_type === 'pickup' ? 'Nhận tại cửa hàng' : 'Giao tận nơi' }}</strong>
          </div>
          @if ((string) $customerOrder->delivery_type === 'pickup')
            <div class="order-detail-row">
              <span>Cửa hàng nhận</span>
              <strong>{{ $customerOrderPickupStore?->name ?: '-' }}</strong>
            </div>
          @endif
          @if ((string) $customerOrder->delivery_type !== 'pickup')
            <div class="order-detail-row">
              <span>Địa chỉ nhận</span>
              <strong>{{ $customerOrder->shipping_address_text ?: '-' }}</strong>
            </div>
          @endif
          <div class="order-detail-row">
            <span>Thanh toán</span>
            <strong>{{ $paymentMethodLabels[$paymentMethod] ?? ucfirst((string) ($customerOrder->payment_method ?? 'cod')) }}</strong>
          </div>
          <div class="order-detail-row">
            <span>Trạng thái thanh toán</span>
            <strong>{{ $paymentStatusLabels[$paymentStatus] ?? ucfirst((string) ($customerOrder->payment_status ?? 'unpaid')) }}</strong>
          </div>
          @if (!empty($customerOrderPayment?->transaction_code))
            <div class="order-detail-row">
              <span>Mã giao dịch</span>
              <strong>{{ (string) $customerOrderPayment->transaction_code }}</strong>
            </div>
          @endif
        </div>

        <div class="order-detail-block">
          <h2>Sản phẩm</h2>
          @forelse ($customerOrderItems as $item)
            <article class="order-detail-item">
              <img
                src="{{ (string) ($item->image_url ?? '') }}"
                alt="{{ (string) ($item->product_name_snapshot ?? 'Sản phẩm') }}"
                class="order-detail-item__thumb"
                loading="lazy"
              >
              <p class="order-detail-item__name"><span>{{ (string) ($item->product_name_snapshot ?? 'Sản phẩm') }}</span><span class="order-detail-item__price">{{ number_format((float) ($item->unit_price ?? 0), 0, ',', '.') }}đ</span></p>
              <p class="order-detail-item__variant">{{ (string) ($item->variant_name_snapshot ?? '-') }} | SL: {{ (int) ($item->qty ?? 0) }}</p>
            </article>
          @empty
            <p class="order-empty">Không có sản phẩm trong đơn hàng này.</p>
          @endforelse
        </div>

        <a href="{{ route('frontend.profile.orders') }}" class="order-detail-back">Quay lại lịch sử mua hàng</a>
      </section>
    </div>
  </main>
@endsection
