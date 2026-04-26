@extends('frontend.layouts.app')

@section('title', 'Lịch sử mua hàng')
@section('meta_title', 'Lịch sử mua hàng khách hàng')
@section('meta_description', 'Trang lịch sử mua hàng của khách hàng tại Shop Nội Y Buôn Hồ.')
@section('meta_robots', 'noindex,nofollow')

@push('head')
  <style>
    .order-history-page {
      min-height: 100vh;
      background: #ffffff;
      color: #1b2322;
      font-family: 'Be Vietnam Pro', sans-serif;
      padding: 60px 0 36px;
    }

    .order-history-shell {
      max-width: 430px;
      margin: 0 auto;
    }

    .order-history-card {
      padding: 20px 16px 24px;
    }

    .order-history-card h2 {
      margin: 0 0 14px;
      font-size: 1.2rem;
      color: #17201f;
    }

    .order-list {
      display: grid;
      gap: 12px;
    }

    .order-item {
      display: block;
      padding: 14px;
      border-radius: 16px;
      background: #f6f8f7;
      border: 1px solid #e6ecea;
      color: inherit;
      text-decoration: none;
    }

    .order-item:hover {
      border-color: #c9d7d3;
      background: #f2f7f5;
    }

    .order-item__head {
      display: flex;
      justify-content: space-between;
      gap: 10px;
      margin-bottom: 6px;
      font-size: 0.92rem;
      color: #17201f;
    }

    .order-item__code {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-weight: 700;
    }

    .order-item__code i {
      font-size: 1rem;
      color: #5f6d69;
    }

    .order-item__amount {
      font-weight: 700;
      color: #005147;
    }

    .order-item__meta {
      display: flex;
      justify-content: space-between;
      gap: 10px;
      color: #5c6a66;
      font-size: 0.88rem;
    }

    .order-item__meta span:last-child {
      text-align: right;
    }

    .order-item__submeta {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      margin-top: 8px;
      color: #697772;
      font-size: 0.8rem;
    }

    .order-item__badge {
      display: inline-flex;
      align-items: center;
      min-height: 24px;
      padding: 4px 9px;
      border-radius: 999px;
      background: #e9f4f0;
      color: #005147;
      font-weight: 700;
      white-space: nowrap;
    }

    .order-item__badge.is-cancelled,
    .order-item__badge.is-expired {
      background: #f4ece9;
      color: #9f3f26;
    }

    .order-item__badge.is-unpaid {
      background: #fff5dd;
      color: #946400;
    }

    .order-empty {
      margin: 0;
      padding: 16px;
      border-radius: 14px;
      background: #f8faf9;
      color: #677571;
      font-size: 0.92rem;
      text-align: center;
    }

    .order-pagination {
      margin-top: 16px;
    }

    .order-back {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 100%;
      margin-top: 14px;
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
  <main class="phone order-history-page">
    @include('frontend.partials.topbar', [
      'headerClass' => 'topbar',
    ])

    <section class="cart-subhead">
      <a href="{{ route('frontend.profile') }}" class="cart-subhead-back" aria-label="Quay lại trang tài khoản">
        <i class="bi bi-arrow-left"></i>
      </a>
      <h1>Lịch sử mua hàng</h1>
      <span class="cart-subhead-spacer"></span>
    </section>

    <div class="order-history-shell">
      <section class="order-history-card">
        <h2>Đơn hàng của bạn</h2>

        @if ($customerOrders->isEmpty())
          <p class="order-empty">Bạn chưa có đơn hàng nào.</p>
        @else
          <div class="order-list">
            @foreach ($customerOrders as $order)
              @php
                $historyType = (string) ($order->history_type ?? 'order');
                $statusValue = strtolower((string) ($order->order_status ?? $order->invoice_status ?? ''));
                $paymentStatusValue = strtolower((string) ($order->payment_status ?? ''));
                $badgeClass = match (true) {
                    in_array($statusValue, ['cancelled', 'expired'], true) => 'is-cancelled',
                    in_array($paymentStatusValue, ['unpaid', 'pending', 'pending_payment'], true) => 'is-unpaid',
                    default => '',
                };
              @endphp
              <a
                href="{{ $order->history_url }}"
                class="order-item"
                aria-label="Xem chi tiết {{ $historyType === 'invoice' ? 'hóa đơn' : 'đơn' }} {{ $order->history_code }}"
              >
                <div class="order-item__head">
                  <span class="order-item__code">
                    <i class="bi {{ $historyType === 'invoice' ? 'bi-qr-code' : 'bi-box-seam' }}"></i>
                    {{ $order->history_code }}
                  </span>
                  <span class="order-item__amount">{{ number_format((float) $order->history_amount, 0, ',', '.') }}đ</span>
                </div>
                <div class="order-item__meta">
                  <span>{{ optional($order->history_created_at)->format('d/m/Y H:i') ?: '-' }}</span>
                  <span>{{ $order->history_status_label }}</span>
                </div>
                <div class="order-item__submeta">
                  <span>{{ $historyType === 'invoice' ? 'Hóa đơn VietQR' : 'Đơn hàng' }}</span>
                  <span class="order-item__badge {{ $badgeClass }}">{{ $order->history_payment_status_label }}</span>
                </div>
              </a>
            @endforeach
          </div>

          <div class="order-pagination">
            {{ $customerOrders->onEachSide(1)->links() }}
          </div>
        @endif

        <a href="{{ route('frontend.profile') }}" class="order-back">Quay lại tài khoản</a>
      </section>
    </div>
  </main>
@endsection
