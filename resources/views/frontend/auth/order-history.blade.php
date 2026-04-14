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
              <a
                href="{{ route('frontend.profile.orders.detail', ['order' => $order->id]) }}"
                class="order-item"
                aria-label="Xem chi tiết đơn {{ $order->order_code ?: ('#' . $order->id) }}"
              >
                <div class="order-item__head">
                  <span class="order-item__code">
              <i class="bi bi-box-seam"></i>
                    {{ $order->order_code ?: ('#' . $order->id) }}
                  </span>
                  <span class="order-item__amount">{{ number_format((float) $order->total_amount, 0, ',', '.') }}đ</span>
                </div>
                <div class="order-item__meta">
                  <span>{{ optional($order->created_at)->format('d/m/Y H:i') ?: '-' }}</span>
                  <span>{{ $order->order_status_label ?? ucfirst((string) $order->order_status) }}</span>
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
