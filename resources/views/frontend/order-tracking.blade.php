@extends('frontend.layouts.app')

@section('title', 'Tra cứu đơn hàng')

@section('content')
@php
  $shopName = trim((string) ($trackingSettings['site_name'] ?? $frontendSiteName));
  $shopLogo = trim((string) ($trackingSettings['site_logo_url'] ?? $frontendLogoUrl ?? ''));
  $shopPhone = trim((string) ($trackingSettings['contact_phone'] ?? $trackingSettings['hotline'] ?? ''));
  $shopAddress = trim((string) ($trackingSettings['contact_address'] ?? ''));
  $statusLabel = $order->order_status_label;
  $paymentMethodValue = strtolower((string) ($order->payment_method ?? 'cod'));
  $paymentStatusValue = strtolower((string) ($order->payment_status ?? 'unpaid'));
  $trackingPaymentMethodLabel = match ($paymentMethodValue) {
      'vietqr' => 'VietQR',
      'cod' => 'Thanh toán khi nhận hàng',
      default => strtoupper($paymentMethodValue),
  };
  $trackingPaymentStatusLabel = $paymentMethodValue === 'cod' && in_array($paymentStatusValue, ['unpaid', 'pending'], true)
      ? 'Thanh toán khi nhận hàng'
      : match ($paymentStatusValue) {
          'paid' => 'Đã thanh toán',
          'unpaid' => 'Chưa thanh toán',
          'pending' => 'Đang chờ thanh toán',
          'failed' => 'Thanh toán thất bại',
          'refunded' => 'Đã hoàn tiền',
          default => $paymentStatusValue,
      };
  $itemsSummary = $orderItems->map(function ($item, $index) {
      $variant = trim((string) ($item->variant_name_snapshot ?? ''));
      $parts = array_filter([$item->product_name_snapshot, $variant !== '' ? $variant : null], fn ($value) => filled($value));

      return ($index + 1) . '. ' . implode(' - ', $parts) . ', SL: ' . (int) $item->qty;
  })->implode("\n");
  $recipientAddress = $order->delivery_type === 'pickup'
      ? trim(implode(', ', array_filter([
          $selectedStore?->name,
          $selectedStore?->address_line,
          $selectedStore?->district,
          $selectedStore?->province,
      ])))
      : trim((string) ($order->shipping_address_text ?? ''));
  $createdAt = optional($order->created_at)->format('d-m-Y H:i');
@endphp
<style>
  .tracking-shell {
    min-height: 100vh;
    padding: 12px 12px 36px;
    background:
      radial-gradient(circle at top right, rgba(249, 115, 22, 0.18), transparent 28%),
      linear-gradient(180deg, #f6f3ee 0%, #ece8df 100%);
    color: #111827;
  }
  .tracking-wrap {
    max-width: 420px;
    margin: 0 auto;
  }
  .tracking-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 12px;
  }
  .tracking-home {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.86);
    color: #111827;
    text-decoration: none;
    box-shadow: 0 10px 26px rgba(15, 23, 42, 0.08);
    font-size: 13px;
    font-weight: 700;
  }
  .tracking-note {
    max-width: 160px;
    text-align: right;
    font-size: 11px;
    color: #475569;
  }
  .mobile-label {
    background: #fff;
    border: 2px solid #2f2f2f;
    border-radius: 22px;
    overflow: hidden;
    box-shadow: 0 18px 44px rgba(15, 23, 42, 0.14);
  }
  .mobile-head {
    padding: 14px 14px 12px;
    border-bottom: 2px dashed #6b7280;
  }
  .label-brand {
    display: flex;
    align-items: center;
    gap: 12px;
  }
  .brand-badge {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    /* background: linear-gradient(135deg, #f97316, #fb923c); */
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    overflow: hidden;
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.16);
  }
  .brand-badge img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }
  .brand-badge-fallback {
    color: #fff;
    font-size: 22px;
    font-weight: 800;
  }
  .brand-title {
    display: block;
    font-size: 22px;
    line-height: 1;
    font-weight: 800;
    color: #f97316;
    letter-spacing: -0.02em;
  }
  .brand-subtitle {
    display: block;
    margin-top: 4px;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .18em;
    color: #0f172a;
  }
  .head-meta {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 118px;
    align-items: start;
    gap: 10px;
    margin-top: 14px;
  }
  .meta-list {
    display: grid;
    gap: 6px;
    font-size: 12px;
  }
  .head-qr {
    display: flex;
    justify-content: flex-end;
  }
  .meta-item strong {
    display: block;
    margin-bottom: 2px;
    font-size: 10px;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: #64748b;
  }
  .mobile-section {
    padding: 14px;
    border-bottom: 2px dashed #6b7280;
  }
  .mobile-section:last-of-type {
    border-bottom: 0;
  }
  .section-title {
    margin-bottom: 10px;
    font-size: 12px;
    letter-spacing: .14em;
    font-weight: 800;
    text-transform: uppercase;
    color: #64748b;
  }
  .address-stack {
    display: grid;
    gap: 14px;
  }
  .address-card {
    display: grid;
    gap: 6px;
  }
  .address-label {
    font-size: 13px;
    font-weight: 800;
    color: #0f172a;
    text-transform: uppercase;
  }
  .address-name {
    font-size: 18px;
    font-weight: 800;
    color: #0f172a;
  }
  .address-phone {
    font-size: 13px;
    color: #f97316;
    font-weight: 700;
  }
  .address-text {
    white-space: pre-line;
    line-height: 1.45;
    font-size: 13px;
  }
  .code-main {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    gap: 8px;
    padding: 4px 0 0;
  }
  .code-main-label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .12em;
    color: #64748b;
  }
  .code-main-value {
    font-size: 42px;
    line-height: 1;
    letter-spacing: .05em;
    font-weight: 900;
    color: #111827;
    text-align: center;
    word-break: break-all;
  }
  .code-status {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    border-radius: 999px;
    background: #ecfeff;
    color: #0f766e;
    font-weight: 700;
    font-size: 12px;
  }
  .code-extra {
    margin-top: 12px;
  }
  .qr-grid {
    width: 112px;
    height: 112px;
    padding: 6px;
    background: #fff;
    border: 2px solid #111827;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .qr-grid svg {
    width: 100%;
    height: 100%;
    display: block;
  }
  .qr-caption {
    font-size: 11px;
    color: #64748b;
    line-height: 1.45;
    text-align: center;
  }
  .mobile-grid {
    display: grid;
    gap: 14px;
  }
  .details-title,
  .summary-title {
    margin-bottom: 10px;
    font-size: 13px;
    font-weight: 800;
    text-transform: uppercase;
  }
  .item-lines {
    white-space: pre-line;
    line-height: 1.55;
    font-size: 13px;
  }
  .summary-box {
    padding: 12px;
    border: 1px dashed #cbd5e1;
    background: #f8fafc;
  }
  .summary-value {
    font-size: 24px;
    font-weight: 900;
    color: #111827;
  }
  .summary-sub {
    margin-top: 6px;
    font-size: 12px;
    color: #475569;
    line-height: 1.5;
  }
  .contact-box {
    padding: 12px;
    border: 1px dashed #94a3b8;
    background: #f8fafc;
    font-size: 12px;
    line-height: 1.55;
  }
  .tracking-link-box {
    padding: 14px;
    background: #fafaf9;
    font-size: 12px;
  }
  .tracking-link-box a {
    color: #1d4ed8;
    word-break: break-all;
    font-weight: 700;
  }
  @media (min-width: 768px) {
    .tracking-wrap {
      max-width: 420px;
    }
  }
  @media (max-width: 360px) {
    .head-meta {
      grid-template-columns: 1fr;
    }
    .head-qr {
      justify-content: flex-start;
    }
  }
</style>

<main class="tracking-shell">
  <div class="tracking-wrap">
    <div class="tracking-topbar">
      <a href="{{ route('frontend.home') }}" class="tracking-home">
        <i class="bi bi-house-door"></i>
        <span>Về trang chủ</span>
      </a>
      
    </div>

    <section class="mobile-label">
      <div class="mobile-head">
        <div class="label-brand">
          <span class="brand-badge">
            @if ($shopLogo !== '')
              <img src="{{ $shopLogo }}" alt="{{ $shopName }}" loading="eager" decoding="async" fetchpriority="low">
            @else
              <span class="brand-badge-fallback"><i class="bi bi-box-seam"></i></span>
            @endif
          </span>
          <div>
            <span class="brand-title">{{ $shopName }}</span>
            <span class="brand-subtitle">Tracking Mobile</span>
          </div>
        </div>
        <div class="head-meta">
          <div class="meta-main">
            <div class="meta-list">
              <div class="meta-item"><strong>Mã vận đơn</strong>{{ $order->customer_tracking_token }}</div>
              <div class="meta-item"><strong>Mã đơn</strong>{{ $order->order_code }}</div>
              <div class="meta-item"><strong>Trạng thái</strong>{{ $statusLabel }}</div>
            </div>
          </div>
          <div class="head-qr">
            <div class="qr-grid" aria-label="QR theo doi don hang">
              {!! $qrSvg !!}
            </div>
          </div>
        </div>
      </div>

      <div class="mobile-section">
        <div class="section-title">Thông tin giao nhận</div>
        <div class="address-stack">
          <div class="address-card">
            <div class="address-label">Từ</div>
            <div class="address-name">{{ $shopName }}</div>
            <div class="address-phone">{{ $shopPhone !== '' ? $shopPhone : 'Đang cập nhật SĐT shop' }}</div>
            <div class="address-text">{{ $shopAddress !== '' ? $shopAddress : 'Vui lòng cập nhật địa chỉ shop trong cấu hình website.' }}</div>
          </div>
          <div class="address-card">
            <div class="address-label">Đến</div>
            <div class="address-name">{{ $order->customer_name }}</div>
            <div class="address-phone">{{ $order->customer_phone }}</div>
            <div class="address-text">{{ $recipientAddress !== '' ? $recipientAddress : 'Khách nhận tại cửa hàng.' }}</div>
          </div>
        </div>
      </div>

      <div class="mobile-section">
        <div class="code-main">
          <div class="code-main-label">Mã Theo Dõi</div>
          <div class="code-main-value">{{ $order->customer_tracking_token }}</div>
          <div class="code-status"><i class="bi bi-patch-check"></i> {{ $statusLabel }}</div>
        </div>
        <div class="code-extra">
          <div class="qr-caption">Quét QR ở phía trên để mở link theo dõi đơn hàng này.</div>
        </div>
      </div>

      <div class="mobile-section">
        <div class="details-title">Nội dung hàng (Tổng SL sản phẩm: {{ (int) $orderItems->sum('qty') }})</div>
        <div class="item-lines">{{ $itemsSummary !== '' ? $itemsSummary : 'Chưa có dữ liệu sản phẩm.' }}</div>
      </div>

      <div class="mobile-section">
        <div class="mobile-grid">
          <div>
            <div class="summary-title">Ngày đặt hàng</div>
            <div class="summary-value" style="font-size: 20px;">{{ $createdAt ?: '-' }}</div>
          </div>
          <div class="summary-box">
            <div class="summary-title">Tiền thu người nhận</div>
            <div class="summary-value">{{ number_format((float) $order->total_amount, 0, ',', '.') }} VND</div>
            <div class="summary-sub">Thanh toán: {{ $trackingPaymentMethodLabel }} / {{ $trackingPaymentStatusLabel }}</div>
          </div>
          <div class="contact-box">
            Liên hệ shop:
            @if ($shopPhone !== '')
              <strong>{{ $shopPhone }}</strong>
            @else
              <strong>Đang cập nhật</strong>
            @endif
            <br>
            @if (!empty($trackingSettings['zalo_url']))
              Zalo: <a href="{{ $trackingSettings['zalo_url'] }}" target="_blank" rel="noopener">Mở Zalo hỗ trợ</a>
            @else
              Có thể liên hệ qua Zalo theo SĐT shop bên trên.
            @endif
          </div>
        </div>
      </div>

      <div class="tracking-link-box">
        Link theo dõi:
        <a href="{{ $trackingLink }}">{{ $trackingLink }}</a>
      </div>
    </section>
  </div>
</main>
@endsection
