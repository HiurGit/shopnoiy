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

    .order-history-tabs {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 6px;
      margin: 0 0 14px;
      padding: 4px;
      border-radius: 14px;
      background: #f1f5f3;
      border: 1px solid #e0e8e5;
    }

    .order-history-tab {
      min-width: 0;
      min-height: 38px;
      padding: 7px 6px;
      border-radius: 11px;
      color: #65736f;
      text-decoration: none;
      display: inline-flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 4px;
      font-size: 0.68rem;
      font-weight: 800;
      line-height: 1.15;
      text-align: center;
      white-space: nowrap;
    }

    .order-history-tab.is-active {
      background: #ffffff;
      color: #17201f;
      box-shadow: 0 8px 18px rgba(24, 39, 36, 0.08);
    }

    .order-history-tab__icon-wrap {
      position: relative;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 38px;
      height: 32px;
    }

    .order-history-tab__icon {
      width: 30px;
      height: 30px;
      color: #6f7e79;
      display: block;
    }

    .order-history-tab.is-active .order-history-tab__icon {
      color: #00624f;
    }

    .order-history-tab__label {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 0;
    }

    .order-history-tab__count {
      position: absolute;
      top: -3px;
      right: -4px;
      min-width: 16px;
      height: 16px;
      padding: 0 4px;
      border-radius: 999px;
      background: #dfe8e4;
      color: #51615c;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 0.62rem;
      line-height: 1;
    }

    .order-history-tab.is-active .order-history-tab__count {
      background: #e8f5ef;
      color: #00624f;
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

    .order-item__status-badge {
      display: inline-flex;
      align-items: center;
      min-height: 24px;
      padding: 4px 10px;
      border-radius: 999px;
      background: #e8f5ef;
      color: #00624f;
      border: 1px solid #cde8dd;
      font-size: 0.78rem;
      font-weight: 800;
      line-height: 1.2;
      white-space: nowrap;
    }

    .order-item__status-badge.is-pending {
      background: #fff5dd;
      color: #946400;
      border-color: #f3dfaa;
    }

    .order-item__status-badge.is-cancelled,
    .order-item__status-badge.is-expired {
      background: #f4ece9;
      color: #9f3f26;
      border-color: #ead0c7;
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

    .order-item__payment-text {
      color: #697772;
      font-weight: 600;
      white-space: nowrap;
    }

    .order-item__payment-text.is-unpaid {
      color: #697772;
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

        @php
          $orderHistoryTabs = $orderHistoryTabs ?? [
              ['key' => 'verified', 'label' => 'Đã xác minh', 'count' => 0],
              ['key' => 'pending', 'label' => 'Chờ xác minh', 'count' => 0],
              ['key' => 'cancelled', 'label' => 'Đã hủy', 'count' => 0],
          ];
          $activeOrderHistoryTab = $activeOrderHistoryTab ?? 'verified';
          $emptyText = match ($activeOrderHistoryTab) {
              'pending' => 'Bạn chưa có đơn hàng chờ xác minh.',
              'cancelled' => 'Bạn chưa có đơn hàng đã hủy.',
              default => 'Bạn chưa có đơn hàng đã xác minh.',
          };
        @endphp

        <nav class="order-history-tabs" aria-label="Lọc lịch sử đơn hàng">
          @foreach ($orderHistoryTabs as $tab)
            @php
              $tabKey = (string) $tab['key'];
              $tabUrl = route('frontend.profile.orders', array_merge(request()->except(['page', 'tab']), ['tab' => $tabKey]));
            @endphp
            <a
              href="{{ $tabUrl }}"
              class="order-history-tab {{ $activeOrderHistoryTab === $tabKey ? 'is-active' : '' }}"
              @if ($activeOrderHistoryTab === $tabKey) aria-current="page" @endif
            >
              @if ($tabKey === 'verified')
                <span class="order-history-tab__icon-wrap">
                  <svg class="order-history-tab__icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21a3.745 3.745 0 0 1-3.068-1.593 3.745 3.745 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.745 3.745 0 0 1 3.296-1.043A3.745 3.745 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.745 3.745 0 0 1 3.296 1.043 3.745 3.745 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                  </svg>
                  <span class="order-history-tab__count">{{ (int) ($tab['count'] ?? 0) }}</span>
                </span>
              @elseif ($tabKey === 'pending')
                <span class="order-history-tab__icon-wrap">
                  <svg class="order-history-tab__icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                  </svg>
                  <span class="order-history-tab__count">{{ (int) ($tab['count'] ?? 0) }}</span>
                </span>
              @else
                <span class="order-history-tab__icon-wrap">
                  <svg class="order-history-tab__icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                  </svg>
                  <span class="order-history-tab__count">{{ (int) ($tab['count'] ?? 0) }}</span>
                </span>
              @endif
              <span class="order-history-tab__label">
                <span>{{ $tab['label'] }}</span>
              </span>
            </a>
          @endforeach
        </nav>

        @if ($customerOrders->isEmpty())
          <p class="order-empty">{{ $emptyText }}</p>
        @else
          <div class="order-list">
            @foreach ($customerOrders as $order)
              @php
                $historyType = (string) ($order->history_type ?? 'order');
                $statusValue = strtolower((string) ($order->order_status ?? $order->invoice_status ?? ''));
                $paymentStatusValue = strtolower((string) ($order->payment_status ?? ''));
                $statusBadgeClass = match (true) {
                    in_array($statusValue, ['cancelled', 'expired'], true) => 'is-cancelled',
                    in_array($statusValue, ['pending_verification', 'pending_payment'], true) => 'is-pending',
                    $statusValue === 'verified' => 'is-verified',
                    default => '',
                };
                $paymentTextClass = match (true) {
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
                  <span class="order-item__status-badge {{ $statusBadgeClass }}">{{ $order->history_status_label }}</span>
                </div>
                <div class="order-item__submeta">
                  <span>{{ $historyType === 'invoice' ? 'Hóa đơn VietQR' : 'Đơn hàng' }}</span>
                  <span class="order-item__payment-text {{ $paymentTextClass }}">{{ $order->history_payment_status_label }}</span>
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
