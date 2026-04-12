@extends('frontend.layouts.app')

@section('title', 'Xếp hạng khách hàng')
@section('meta_title', 'Xếp hạng khách hàng theo doanh thu')
@section('meta_description', 'Danh sách khách hàng nổi bật của shop được sắp xếp theo tổng doanh thu tích lũy.')

@php
  $tierLabels = [
      'new' => 'Khách hàng mới',
      'friendly' => 'Khách hàng thân thiện',
      'loyal' => 'Khách hàng trung thành',
      'vip' => 'Khách hàng VIP',
      'diamond' => 'Khách hàng Kim cương',
  ];

  $tierBadgeClass = fn (string $tier): string => match ($tier) {
      'friendly' => 'is-friendly',
      'loyal' => 'is-loyal',
      'vip' => 'is-vip',
      'diamond' => 'is-diamond',
      default => 'is-new',
  };

  $initials = function (?string $name): string {
      $parts = preg_split('/\s+/u', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
      if ($parts === []) {
          return 'KH';
      }

      if (count($parts) === 1) {
          return mb_strtoupper(mb_substr($parts[0], 0, 2));
      }

      return mb_strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[count($parts) - 1], 0, 1));
  };

@endphp

@push('head')
<style>
  .customer-ranking-phone {
    background: #f6f3ee;
  }

  .customer-ranking-shell {
    max-width: 540px;
    margin: 0 auto;
    padding: 12px 8px 28px;
  }

  .ranking-hero {
    margin-bottom: 12px;
    padding: 14px;
    border-radius: 14px;
    border: 1px solid #ddd4c8;
    background: #fff;
  }

  .ranking-hero-copy strong {
    display: block;
    color: #24211d;
    font-size: 17px;
    font-weight: 800;
    line-height: 1.2;
  }

  .ranking-hero-copy span {
    display: block;
    margin-top: 4px;
    color: #6f675d;
    font-size: 12px;
  }

  .ranking-search-wrap {
    margin: 0 0 12px;
    padding: 12px;
    border-radius: 14px;
    border: 1px solid #ddd4c8;
    background: #fff;
  }

  .ranking-notice {
    margin: 0 0 12px;
    padding: 12px 13px;
    border-radius: 14px;
    border: 1px solid #ddd4c8;
    background: #fff;
  }

  .ranking-notice strong {
    display: block;
    color: #3f3932;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.5;
  }

  .ranking-notice p {
    margin: 6px 0 0;
    color: #73695d;
    font-size: 12px;
    line-height: 1.6;
  }

  .ranking-search-label {
    display: block;
    margin-bottom: 8px;
    color: #4d463f;
    font-size: 12px;
    font-weight: 700;
  }

  .ranking-search-field {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0 0 12px;
    margin-bottom: 8px;
    padding: 10px 12px;
    border-radius: 12px;
    border: 1px solid #e2d8cc;
    background: #faf8f5;
  }

  .ranking-search-field i {
    color: #8b8175;
    font-size: 14px;
  }

  .ranking-search-input {
    width: 100%;
    border: 0;
    outline: none;
    background: transparent;
    padding: 0;
    font-size: 13px;
    color: #2f2c28;
  }

  .ranking-search-clear {
    border: 0;
    background: transparent;
    color: #8b8175;
    font-size: 14px;
    width: 24px;
    height: 24px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    cursor: pointer;
  }

  .ranking-search-clear[hidden] {
    display: none;
  }

  .ranking-search-meta {
    color: #7a7064;
    font-size: 11px;
  }

  .ranking-summary-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 8px;
    margin-bottom: 14px;
  }

  .ranking-summary-card {
    background: #fff;
    border: 1px solid #ddd4c8;
    border-radius: 14px;
    padding: 12px 8px;
    text-align: center;
  }

  .ranking-summary-card strong {
    display: block;
    color: #1d1d1b;
    font-size: 26px;
    font-weight: 800;
    line-height: 1;
  }

  .ranking-summary-card span {
    display: block;
    margin-top: 4px;
    color: #403a34;
    font-size: 12px;
    font-weight: 600;
    line-height: 1.2;
  }

  .ranking-summary-card small {
    display: block;
    margin-top: 3px;
    color: #8c7b65;
    font-size: 11px;
    font-weight: 600;
  }

  .ranking-section-title {
    margin: 0 0 10px;
    color: #5d5448;
    font-size: 13px;
    font-weight: 800;
    letter-spacing: .04em;
    text-transform: uppercase;
  }

  .ranking-list {
    display: grid;
    gap: 10px;
  }

  .ranking-list-card {
    display: grid;
    grid-template-columns: 34px 42px minmax(0, 1fr) auto;
    align-items: center;
    gap: 10px;
    padding: 12px;
    border-radius: 14px;
    background: #fff;
    border: 1px solid #ddd4c8;
  }

  .ranking-list-card[hidden] {
    display: none !important;
  }

  .ranking-list-card.is-filter-hidden {
    display: none !important;
  }

  .ranking-list-rank {
    color: #2d2a27;
    font-size: 18px;
    font-weight: 700;
    text-align: center;
  }

  .ranking-list-avatar {
    width: 34px;
    height: 34px;
    border-radius: 999px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 800;
    background: #f3d7df;
    color: #b14973;
  }

  .ranking-list-avatar.is-friendly {
    background: #eadcf7;
    color: #7a43b6;
  }

  .ranking-list-avatar.is-loyal {
    background: #dff1e6;
    color: #347a4c;
  }

  .ranking-list-avatar.is-vip {
    background: #fff0d1;
    color: #a76800;
  }

  .ranking-list-avatar.is-diamond {
    background: #def0ff;
    color: #2c6bb5;
  }

  .ranking-list-info {
    min-width: 0;
  }

  .ranking-list-info strong {
    display: block;
    color: #24211d;
    font-size: 15px;
    font-weight: 700;
    line-height: 1.25;
  }

  .ranking-list-info span {
    display: block;
    margin-top: 3px;
    color: #645c52;
    font-size: 12px;
  }

  .ranking-list-meta {
    text-align: right;
    min-width: 64px;
  }

  .ranking-list-value {
    color: #24211d;
    font-size: 16px;
    font-weight: 800;
    line-height: 1.1;
  }

  .ranking-list-tier {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 52px;
    margin-top: 4px;
    padding: 3px 8px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    background: #ededed;
    color: #69645d;
  }

  .ranking-list-tier.is-vip {
    background: #ffe7bd;
    color: #b16b00;
  }

  .ranking-list-tier.is-diamond {
    background: #e2eeff;
    color: #285aa1;
  }

  .ranking-list-tier.is-loyal {
    background: #efefef;
    color: #6a6a6a;
  }

  .ranking-list-tier.is-friendly {
    background: #efe7fb;
    color: #7c48b8;
  }

  .customer-ranking-empty {
    padding: 20px 14px;
    border-radius: 16px;
    text-align: center;
    color: #6b6258;
    font-size: 13px;
    background: rgba(255, 255, 255, 0.7);
    border: 1px dashed #d2c3b0;
  }

  @media (max-width: 390px) {
    .ranking-summary-grid {
      grid-template-columns: 1fr;
    }

    .ranking-list-card {
      grid-template-columns: 30px 38px minmax(0, 1fr);
    }

    .ranking-list-meta {
      grid-column: 2 / 4;
      margin-left: auto;
    }
  }
</style>
@endpush

@section('content')
<main class="phone cat-phone customer-ranking-phone">
  <header class="cat-topbar">
    <div class="topbar-row">
      @include('frontend.partials.logo')
      <a href="{{ route('frontend.search') }}" class="search-form topbar-search search-entry-link" aria-label="Mở tìm kiếm">
        <i class="bi bi-search search-icon"></i>
        <span class="search-entry-text">Tìm sản phẩm...</span>
        <span class="search-entry-btn">Tìm</span>
      </a>
      <div class="cat-actions">
        <a href="{{ route('frontend.cart') }}" class="cat-bell-wrap" aria-label="Mở giỏ hàng"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"></path></svg></a>
        <i class="bi bi-person-circle"></i>
      </div>
    </div>
  </header>

  <section class="cart-subhead">
    <a href="{{ route('frontend.home') }}" class="cart-subhead-back" aria-label="Quay lại" data-history-back="true">
      <i class="bi bi-arrow-left"></i>
    </a>
    <h1>Xếp hạng khách hàng</h1>
    <span class="cart-subhead-spacer"></span>
  </section>

  <section class="customer-ranking-shell">
    <div class="ranking-notice">
      <strong>Khách hàng nằm trong TOP xếp hạng của shop cuối năm sẽ nhận được quà tặng đặc biệt</strong>
      <p>Cảm ơn bạn đã luôn đồng hành cùng shop</p>
    </div>

    <div class="ranking-search-wrap">
      <label class="ranking-search-label" for="ranking-search-input">Tìm khách hàng theo số điện thoại</label>
      <div class="ranking-search-field">
        <i class="bi bi-search"></i>
        <input type="text" id="ranking-search-input" class="ranking-search-input" placeholder="Ví dụ: 09 hoặc 123" data-ranking-search>
        <button type="button" class="ranking-search-clear" aria-label="Xóa từ khóa" data-ranking-clear hidden>
          <i class="bi bi-x-lg"></i>
        </button>
      </div>
      <div class="ranking-search-meta" data-ranking-meta>Hiển thị {{ number_format($customers->count()) }} khách hàng</div>
    </div>

    @if ($customers->isEmpty())
      <div class="customer-ranking-empty">
        Chưa có dữ liệu khách hàng đủ điều kiện để xếp hạng.
      </div>
    @else
        <div class="ranking-list" data-ranking-list>
          @foreach ($customers as $customer)
          <article
            class="ranking-list-card"
            data-ranking-item
            data-ranking-phone="{{ preg_replace('/\s+/', '', mb_strtolower($customer->masked_phone ?: '')) }}"
          >
            <div class="ranking-list-rank">{{ $customer->rank }}</div>
            <div class="ranking-list-avatar {{ $tierBadgeClass((string) $customer->tier) }}">{{ $initials($customer->display_name) }}</div>

            <div class="ranking-list-info">
              <strong>{{ $customer->masked_phone ?: 'Chưa có số điện thoại' }}</strong>
              <span>{{ number_format((int) $customer->total_orders) }} đơn hàng</span>
            </div>

            <div class="ranking-list-meta">
              <div class="ranking-list-value">{{ number_format((float) $customer->total_spent, 0, ',', '.') }}đ</div>
              <div class="ranking-list-tier {{ $tierBadgeClass((string) $customer->tier) }}">
                {{ $tierLabels[$customer->tier] ?? ucfirst((string) $customer->tier) }}
              </div>
            </div>
          </article>
        @endforeach
      </div>
    @endif

    <div class="customer-ranking-empty" data-ranking-empty hidden>
      Không tìm thấy khách hàng phù hợp với từ khóa.
    </div>
  </section>

  <button class="scroll-top-btn" type="button" aria-label="Cuộn lên đầu trang">
    <i class="bi bi-arrow-up"></i>
  </button>
</main>
@endsection

@push('scripts')
<script>
  const scrollTopBtn = document.querySelector('.scroll-top-btn');
  const syncScrollTopButton = () => {
    if (scrollTopBtn) {
      scrollTopBtn.classList.toggle('is-visible', window.scrollY > 320);
    }
  };

  syncScrollTopButton();
  window.addEventListener('scroll', syncScrollTopButton, { passive: true });

  if (scrollTopBtn) {
    scrollTopBtn.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  const rankingSearchInput = document.querySelector('[data-ranking-search]');
  const rankingSearchClear = document.querySelector('[data-ranking-clear]');
  const rankingItems = [...document.querySelectorAll('[data-ranking-item]')];
  const rankingEmpty = document.querySelector('[data-ranking-empty]');
  const rankingMeta = document.querySelector('[data-ranking-meta]');

  if (rankingSearchInput && rankingItems.length) {
    const updateRankingSearch = () => {
      const keyword = String(rankingSearchInput.value || '').trim().toLowerCase();
      let visibleCount = 0;

      rankingItems.forEach((item) => {
        const phone = item.dataset.rankingPhone || '';
        const normalizedKeyword = keyword.replace(/\s+/g, '');
        const matched = normalizedKeyword === '' || phone.includes(normalizedKeyword);
        item.hidden = !matched;
        item.classList.toggle('is-filter-hidden', !matched);
        item.style.display = matched ? '' : 'none';
        if (matched) visibleCount += 1;
      });

      if (rankingSearchClear) {
        rankingSearchClear.hidden = keyword === '';
      }

      if (rankingMeta) {
        rankingMeta.textContent = keyword === ''
          ? `Hiển thị ${visibleCount} khách hàng`
          : `Tìm thấy ${visibleCount} khách hàng`;
      }

      if (rankingEmpty) {
        rankingEmpty.hidden = !(keyword !== '' && visibleCount === 0);
      }
    };

    rankingSearchInput.addEventListener('input', updateRankingSearch);

    if (rankingSearchClear) {
      rankingSearchClear.addEventListener('click', () => {
        rankingSearchInput.value = '';
        updateRankingSearch();
        rankingSearchInput.focus();
      });
    }

    updateRankingSearch();
  }
</script>
@endpush
