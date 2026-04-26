@extends('frontend.layouts.app')

@section('title', 'Hỗ trợ khách hàng')
@section('meta_title', 'Hỗ trợ khách hàng')
@section('meta_description', 'Liên hệ hỗ trợ khách hàng, tra cứu chính sách và nhận tư vấn từ Shop Nội Y Buôn Hồ.')
@section('meta_robots', 'noindex,follow')

@php
  $supportLinks = collect($frontendContactLinks ?? [])
      ->filter(fn ($link) => !empty($link['href']) && in_array((string) ($link['theme'] ?? ''), ['phone', 'zalo', 'group'], true))
      ->values();

  $supportCopyByTheme = [
      'phone' => [
          'label' => 'Gọi điện',
          'description' => 'Gọi trực tiếp để được hỗ trợ nhanh.',
      ],
      'zalo' => [
          'label' => 'Chat Zalo',
          'description' => 'Nhắn tin Zalo với shop.',
      ],
      'group' => [
          'label' => 'Nhóm Zalo',
          'description' => 'Tham gia nhóm Zalo của shop.',
      ],
  ];
@endphp

@push('head')
  <style>
    .customer-support-page {
      background: #ffffff;
      padding-top: 60px;
      color: #17201f;
    }

    .customer-support-shell {
      padding: 12px 10px 30px;
    }

    .customer-support-hero {
      padding: 20px 18px;
      border-radius: 22px;
      background: linear-gradient(180deg, #f5faf8 0%, #eef6f3 100%);
      border: 1px solid rgba(0, 81, 71, 0.08);
      box-shadow: 0 14px 28px rgba(15, 23, 42, 0.05);
    }

    .customer-support-hero__icon {
      width: 46px;
      height: 46px;
      border-radius: 999px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: #005147;
      color: #fff;
      font-size: 20px;
      margin-bottom: 12px;
    }

    .customer-support-hero h2 {
      margin: 0;
      font-size: 1.28rem;
      line-height: 1.3;
      color: #12211f;
    }

    .customer-support-hero p {
      margin: 8px 0 0;
      color: #5f6d69;
      font-size: 0.9rem;
      line-height: 1.6;
    }

    .customer-support-section {
      margin-top: 14px;
    }

    .customer-support-section h3 {
      margin: 0 0 10px;
      font-size: 1rem;
      color: #17201f;
    }

    .customer-support-grid {
      display: grid;
      gap: 10px;
    }

    .customer-support-card {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      min-height: 64px;
      padding: 13px 14px;
      border: 1px solid #dce6e2;
      border-radius: 16px;
      background: linear-gradient(180deg, #ffffff 0%, #f7faf9 100%);
      color: #17201f;
      text-decoration: none;
      box-shadow: 0 8px 18px rgba(15, 23, 42, 0.04);
    }

    .customer-support-card:hover {
      color: #17201f;
      border-color: rgba(0, 81, 71, 0.24);
      background: #f8fbfa;
    }

    .customer-support-card__lead {
      display: flex;
      align-items: center;
      gap: 12px;
      min-width: 0;
    }

    .customer-support-card__icon {
      width: 38px;
      height: 38px;
      border-radius: 999px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      flex: 0 0 38px;
      background: #e6f4ef;
      color: #005147;
      font-size: 1rem;
    }

    .customer-support-card__copy {
      display: grid;
      gap: 3px;
      min-width: 0;
    }

    .customer-support-card__copy strong {
      font-size: 0.92rem;
      color: #17201f;
    }

    .customer-support-card__copy span {
      color: #667571;
      font-size: 0.8rem;
      line-height: 1.35;
    }

    .customer-support-order {
      padding: 16px;
      border-radius: 18px;
      background: #17201f;
      color: #fff;
    }

    .customer-support-order h3 {
      color: #fff;
      margin-bottom: 6px;
    }

    .customer-support-order p {
      margin: 0;
      color: rgba(255, 255, 255, 0.78);
      font-size: 0.86rem;
      line-height: 1.55;
    }

    .customer-support-order a {
      margin-top: 12px;
      min-height: 42px;
      padding: 0 14px;
      border-radius: 999px;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: #fff;
      color: #17201f;
      text-decoration: none;
      font-size: 0.88rem;
      font-weight: 700;
    }

    .customer-support-empty {
      margin: 0;
      padding: 14px;
      border-radius: 16px;
      background: #f7f9f8;
      color: #61706c;
      font-size: 0.88rem;
      line-height: 1.55;
    }
  </style>
@endpush

@section('content')
  <main class="phone customer-support-page">
    @include('frontend.partials.topbar', [
      'headerClass' => 'topbar',
    ])

    <section class="cart-subhead">
      <a href="{{ route('frontend.home') }}" class="cart-subhead-back" aria-label="Quay lại" data-history-back="true">
        <i class="bi bi-arrow-left"></i>
      </a>
      <h1>Hỗ trợ khách hàng</h1>
      <span class="cart-subhead-spacer"></span>
    </section>

    <section class="customer-support-shell">
      <section class="customer-support-hero">
        <span class="customer-support-hero__icon" aria-hidden="true">
          <i class="bi bi-headset"></i>
        </span>
        <h2>Shop luôn sẵn sàng hỗ trợ bạn</h2>
        <p>Cần kiểm tra đơn hàng, đổi thông tin nhận hàng hoặc hỏi thêm về sản phẩm, bạn có thể chọn kênh liên hệ phù hợp bên dưới.</p>
      </section>

      <section class="customer-support-section">
        <h3>Liên hệ nhanh</h3>
        <div class="customer-support-grid">
          @forelse ($supportLinks as $supportLink)
            @php
              $supportHref = (string) ($supportLink['href'] ?? '');
              $supportTheme = (string) ($supportLink['theme'] ?? '');
              $supportCopy = $supportCopyByTheme[$supportTheme] ?? [
                  'label' => (string) ($supportLink['label'] ?? 'Hỗ trợ'),
                  'description' => 'Liên hệ shop để được hỗ trợ.',
              ];
              $isExternalSupportLink = str_starts_with($supportHref, 'http://') || str_starts_with($supportHref, 'https://');
            @endphp
            <a
              href="{{ $supportHref }}"
              class="customer-support-card"
              @if ($isExternalSupportLink) target="_blank" rel="noopener noreferrer" @endif
            >
              <span class="customer-support-card__lead">
                <span class="customer-support-card__icon">
                  <i class="bi {{ $supportLink['icon'] ?? 'bi-headset' }}"></i>
                </span>
                <span class="customer-support-card__copy">
                  <strong>{{ $supportCopy['label'] }}</strong>
                  <span>{{ $supportCopy['description'] }}</span>
                </span>
              </span>
              <i class="bi bi-chevron-right"></i>
            </a>
          @empty
            <p class="customer-support-empty">Thông tin hỗ trợ đang được cập nhật. Vui lòng quay lại sau hoặc xem thông tin liên hệ ở trang chủ.</p>
          @endforelse
        </div>
      </section>

      <section class="customer-support-section customer-support-order">
        <h3>Đang cần hỗ trợ đơn hàng?</h3>
        <p>Bạn có thể mở lịch sử mua hàng để xem mã đơn, trạng thái thanh toán và thông tin giao nhận trước khi liên hệ shop.</p>
        <a href="{{ auth()->check() && auth()->user()?->role === 'customer' ? route('frontend.profile.orders') : route('frontend.login') }}">
          <i class="bi bi-receipt"></i>
          <span>Xem đơn hàng</span>
        </a>
      </section>

      <section class="customer-support-section">
        <h3>Thông tin cần biết</h3>
        <div class="customer-support-grid">
          @foreach ($guideLinks as $guideLink)
            <a href="{{ $guideLink['url'] }}" class="customer-support-card">
              <span class="customer-support-card__lead">
                <span class="customer-support-card__icon">
                  <i class="bi {{ $guideLink['icon'] }}"></i>
                </span>
                <span class="customer-support-card__copy">
                  <strong>{{ $guideLink['title'] }}</strong>
                  <span>{{ $guideLink['description'] }}</span>
                </span>
              </span>
              <i class="bi bi-chevron-right"></i>
            </a>
          @endforeach
        </div>
      </section>
    </section>
  </main>
@endsection
