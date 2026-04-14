@php
  $headerClass = $headerClass ?? 'topbar';
  $rowClass = $rowClass ?? 'topbar-row';
  $actionsClass = $actionsClass ?? 'actions';
  $showLogo = $showLogo ?? true;
  $showAccount = $showAccount ?? true;
  $showCart = $showCart ?? true;
  $searchMode = $searchMode ?? 'full';
  $searchHref = $searchHref ?? route('frontend.search');
  $searchText = $searchText ?? 'Tìm sản phẩm...';
  $searchEntryClass = $searchEntryClass ?? '';
  $searchClass = $searchClass ?? 'search-icon-wrap';
  $accountClass = $accountClass ?? 'user-wrap';
  $accountLabel = $accountLabel ?? 'Tài khoản khách hàng';
  $cartClass = $cartClass ?? 'bell-wrap';
  $cartLabel = $cartLabel ?? 'Mở giỏ hàng';
  $accountHref = $accountHref ?? (auth()->check() && auth()->user()?->role === 'customer'
      ? route('frontend.profile')
      : route('frontend.login'));
@endphp

<header class="{{ $headerClass }}">
  @if ($rowClass)
    <div class="{{ $rowClass }}">
  @endif

  @if ($showLogo)
    @include('frontend.partials.logo')
  @endif

  @if ($searchMode === 'full')
    <a href="{{ $searchHref }}" class="search-form topbar-search search-entry-link {{ $searchEntryClass }}" aria-label="Mở tìm kiếm">
      <i class="bi bi-search search-icon"></i>
      <span class="search-entry-text">{{ $searchText }}</span>
      <span class="search-entry-btn">Tìm</span>
    </a>
  @endif

  <div class="{{ $actionsClass }}">
    @if ($searchMode === 'icon')
      <i class="bi bi-search"></i>
    @elseif ($searchMode === 'icon-link')
      <a href="{{ $searchHref }}" class="{{ $searchClass }}" aria-label="Mở tìm kiếm">
        <i class="bi bi-search"></i>
      </a>
    @endif

    @if ($showAccount)
      <a href="{{ $accountHref }}" class="{{ $accountClass }}" aria-label="{{ $accountLabel }}">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"></path>
        </svg>
      </a>
    @endif

    @if ($showCart)
      <a href="{{ route('frontend.cart') }}" class="{{ $cartClass }}" aria-label="{{ $cartLabel }}">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon" width="20" height="20">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"></path>
        </svg>
      </a>
    @endif
  </div>

  @if ($rowClass)
    </div>
  @endif
</header>
