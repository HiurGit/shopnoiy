<!DOCTYPE html>
<html lang="vi">
<head>
  @php
    $metaTitle = trim($__env->yieldContent('meta_title')) ?: trim($__env->yieldContent('title')) ?: $frontendMetaTitle;
    $metaDescription = trim($__env->yieldContent('meta_description')) ?: $frontendMetaDescription;
    $metaImage = trim($__env->yieldContent('og_image')) ?: ($frontendMetaOgImage ?? '');
    $metaType = trim($__env->yieldContent('og_type')) ?: 'website';
    $metaUrl = trim($__env->yieldContent('canonical_url')) ?: url()->current();
    $metaRobots = trim($__env->yieldContent('meta_robots')) ?: ($frontendMetaRobots ?? 'index,follow');
  @endphp
  @php
    $showBottomNav = !request()->routeIs([
      'frontend.product-detail',
      'frontend.cart',
      'frontend.checkout',
      'frontend.vietqr.payment',
    ]);
  @endphp
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <meta name="google-site-verification" content="UAlZvk2gzciJ8fOM6Eu0GnqQ2Joeckf5XTow3DzjPR4" />
  <title>{{ $metaTitle === $frontendMetaTitle ? $frontendMetaTitle : $metaTitle . ' | ' . $frontendMetaTitle }}</title>
  @php
    $visitorPageType = match (true) {
      request()->routeIs('frontend.product-detail') => 'product_detail',
      request()->routeIs('frontend.category') => 'category',
      request()->routeIs('frontend.subcategories') => 'subcategory',
      request()->routeIs('frontend.childcategories') => 'childcategory',
      request()->routeIs('frontend.cart') => 'cart',
      request()->routeIs('frontend.checkout') => 'checkout',
      request()->routeIs('frontend.vietqr.payment') => 'vietqr_payment',
      request()->routeIs('frontend.order-success') => 'order_success',
      request()->routeIs('frontend.order-tracking') => 'order_tracking',
      request()->routeIs('frontend.search') => 'search',
      default => 'page',
    };
    $visitorActivityLabel = match ($visitorPageType) {
      'product_detail' => 'Đang xem chi tiết sản phẩm',
      'category', 'subcategory', 'childcategory' => 'Đang xem danh mục sản phẩm',
      'cart' => 'Đang xem giỏ hàng',
      'checkout' => 'Đang ở trang thanh toán',
      'vietqr_payment' => 'Đang chờ thanh toán VietQR',
      'order_success' => 'Vừa đặt hàng thành công',
      'order_tracking' => 'Đang theo dõi đơn hàng',
      'search' => 'Đang tìm kiếm sản phẩm',
      default => 'Đang xem website',
    };
    $visitorMeta = [];
    if (isset($product)) {
      $visitorMeta['product_name'] = $product->name ?? null;
      $visitorMeta['product_slug'] = $product->slug ?? null;
    }
    if (request()->routeIs('frontend.search')) {
      $visitorMeta['search_query'] = trim((string) request('q', '')) ?: null;
    }
  @endphp
  <meta name="description" content="{{ $metaDescription }}" />
  <meta name="robots" content="{{ $metaRobots }}" />
  <link rel="canonical" href="{{ $metaUrl }}" />
  <meta property="og:locale" content="vi_VN" />
  <meta property="og:type" content="{{ $metaType }}" />
  <meta property="og:site_name" content="{{ $frontendSiteName }}" />
  <meta property="og:title" content="{{ $metaTitle }}" />
  <meta property="og:description" content="{{ $metaDescription }}" />
  <meta property="og:url" content="{{ $metaUrl }}" />
  @if ($metaImage !== '')
    <meta property="og:image" content="{{ $metaImage }}" />
    <meta name="twitter:image" content="{{ $metaImage }}" />
  @endif
  <meta name="twitter:card" content="{{ $metaImage !== '' ? 'summary_large_image' : 'summary' }}" />
  <meta name="twitter:title" content="{{ $metaTitle }}" />
  <meta name="twitter:description" content="{{ $metaDescription }}" />
  @if ($frontendFaviconUrl)
    <link rel="icon" href="{{ $frontendFaviconUrl }}{{ $frontendFaviconVersion ? '?v=' . $frontendFaviconVersion : '' }}" />
    <link rel="shortcut icon" href="{{ $frontendFaviconUrl }}{{ $frontendFaviconVersion ? '?v=' . $frontendFaviconVersion : '' }}" />
    <link rel="apple-touch-icon" href="{{ $frontendFaviconUrl }}{{ $frontendFaviconVersion ? '?v=' . $frontendFaviconVersion : '' }}" />
  @endif
  <script type="application/ld+json">
    {!! json_encode([
      '@context' => 'https://schema.org',
      '@type' => 'WebSite',
      'name' => $frontendSiteName,
      'alternateName' => array_values(array_filter([
        $frontendLogoPrimary . ($frontendLogoAccent ? ' ' . $frontendLogoAccent : ''),
        'Cô Thu Nội Y Buôn Hồ',
        'THU Nội Y',
        'Nội Y Buôn Hồ',
      ], fn ($value) => is_string($value) && trim($value) !== '')),
      'url' => url('/'),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
  </script>
  @if (!empty($breadcrumbSchema))
    <script type="application/ld+json">
      {!! json_encode($breadcrumbSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
  @endif
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin />
  <link
    rel="preload"
    as="style"
    href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap"
    onload="this.onload=null;this.rel='stylesheet'"
  />
  <noscript>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  </noscript>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <link rel="stylesheet" href="{{ asset('frontend/style.css') }}?v={{ filemtime(base_path('frontend/style.css')) }}" />
  @livewireStyles
  @stack('vendor_styles')
  @stack('head')
</head>
<body class="{{ trim((trim($__env->yieldContent('body_class')) ?: 'promo-hidden') . ($showBottomNav ? ' has-bottom-nav' : '')) }}">
  @yield('content')
  @if ($showBottomNav)
    <nav class="home-bottom-nav" aria-label="Điều hướng nhanh">
      <a href="{{ route('frontend.home') }}" class="home-bottom-nav__link {{ request()->routeIs('frontend.home') ? 'is-active' : '' }}" @if (request()->routeIs('frontend.home')) aria-current="page" @endif>
        <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8">
          <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955a1.125 1.125 0 0 1 1.592 0L21.75 12M4.5 9.75V19.5A2.25 2.25 0 0 0 6.75 21.75h10.5a2.25 2.25 0 0 0 2.25-2.25V9.75M9.75 21.75v-6a2.25 2.25 0 0 1 4.5 0v6" />
        </svg>
        <span>Home</span>
      </a>
      <a href="{{ route('frontend.search') }}" class="home-bottom-nav__link {{ request()->routeIs('frontend.search') ? 'is-active' : '' }}" @if (request()->routeIs('frontend.search')) aria-current="page" @endif>
        <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8">
          <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 6.04 6.04a7.5 7.5 0 0 0 10.61 10.61Z" />
        </svg>
        <span>Search</span>
      </a>
      <a href="{{ route('frontend.category') }}" class="home-bottom-nav__link home-bottom-nav__link--center {{ request()->routeIs('frontend.category', 'frontend.subcategories', 'frontend.childcategories') ? 'is-active' : '' }}" @if (request()->routeIs('frontend.category', 'frontend.subcategories', 'frontend.childcategories')) aria-current="page" @endif>
        <span class="home-bottom-nav__icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75h6.75v6.75H3.75V3.75Zm9.75 0h6.75v6.75H13.5V3.75ZM3.75 13.5h6.75v6.75H3.75V13.5Zm9.75 0h6.75v6.75H13.5V13.5Z" />
          </svg>
        </span>
        <span>Danh mục</span>
      </a>
      <a href="{{ route('frontend.cart') }}" class="home-bottom-nav__link home-bottom-nav__link--cart {{ request()->routeIs('frontend.cart') ? 'is-active' : '' }}" aria-label="Mở giỏ hàng" @if (request()->routeIs('frontend.cart')) aria-current="page" @endif>
        <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
        </svg>
        <span>Cart</span>
      </a>
      <a
        href="{{ auth()->check() && auth()->user()?->role === 'customer' ? route('frontend.profile') : route('frontend.login') }}"
        class="home-bottom-nav__link {{ request()->routeIs('frontend.profile*', 'frontend.login', 'frontend.register', 'frontend.password.*') ? 'is-active' : '' }}"
        @if (request()->routeIs('frontend.profile*', 'frontend.login', 'frontend.register', 'frontend.password.*')) aria-current="page" @endif
      >
        <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.964 0a9 9 0 1 0-11.964 0m11.964 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
        </svg>
        <span>Profile</span>
      </a>
    </nav>
  @endif
  @unless (request()->routeIs('frontend.search'))
    <livewire:frontend.search-page mode="overlay" />
  @endunless
  <div class="cart-toast" data-cart-toast hidden>
    <div class="cart-toast-head">
      <strong>Đã thêm vào giỏ hàng</strong>
      <button class="cart-toast-close" type="button" aria-label="Đóng thông báo" data-cart-toast-close>
        <i class="bi bi-x-lg"></i>
      </button>
    </div>
    <div class="cart-toast-body">
      <div class="cart-toast-thumb">
        <img src="" alt="" data-cart-toast-image />
      </div>
      <div class="cart-toast-info">
         <h3 data-cart-toast-name></h3>
        <p data-cart-toast-variant></p>
        <div class="cart-toast-price-row">
          <strong data-cart-toast-price></strong>
        </div>
      </div>
    </div>
    <a href="{{ route('frontend.cart') }}" class="cart-toast-link">Xem giỏ hàng</a>
  </div>
  <a href="#" class="vietqr-resume-widget" data-vietqr-resume hidden aria-label="Tiếp tục thanh toán VietQR">
    <span class="vietqr-resume-widget__icon" aria-hidden="true">
      <i class="bi bi-qr-code-scan"></i>
      <span class="vietqr-resume-widget__pulse"></span>
    </span>
    <span class="vietqr-resume-widget__content">
      <span data-vietqr-resume-timer>{{ sprintf('%02d:00', max(1, (int) ($frontendPaymentSettings['vietqr_expire_minutes'] ?? 30))) }}</span>
    </span>
  </a>
  @livewireScripts
  <style>
    .vietqr-resume-widget {
      position: fixed;
      right: max(12px, calc((100vw - 430px) / 2 + 12px));
      bottom: 156px;
      z-index: 1200;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 6px;
      width: 64px;
      padding: 0;
      border-radius: 999px;
      background: transparent;
      color: #fff;
      text-decoration: none;
      box-shadow: none;
      transition: transform 0.2s ease;
    }

    .vietqr-resume-widget[hidden] {
      display: none !important;
    }

    .vietqr-resume-widget:hover {
      transform: translateY(-2px);
      color: #fff;
    }

    .vietqr-resume-widget__icon {
      position: relative;
      width: 56px;
      height: 56px;
      flex: 0 0 56px;
      border-radius: 999px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #163358 0%, #1f5d8d 100%);
      box-shadow: 0 16px 30px rgba(10, 31, 58, 0.26);
      font-size: 24px;
    }

    .vietqr-resume-widget__pulse {
      position: absolute;
      top: 5px;
      right: 5px;
      width: 10px;
      height: 10px;
      border-radius: 999px;
      background: #22c55e;
      box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.5);
      animation: vietqr-resume-pulse 1.8s infinite;
    }

    .vietqr-resume-widget__content {
      min-width: 0;
      padding: 4px 8px;
      border-radius: 999px;
      background: rgba(22, 51, 88, 0.92);
      box-shadow: 0 10px 18px rgba(10, 31, 58, 0.18);
    }

    .vietqr-resume-widget__content span {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .vietqr-resume-widget__content span {
      display: block;
      font-size: 11px;
      line-height: 1.1;
      font-weight: 700;
      color: #fff;
      letter-spacing: 0.04em;
    }

    @keyframes vietqr-resume-pulse {
      0% {
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.55);
      }

      70% {
        box-shadow: 0 0 0 8px rgba(34, 197, 94, 0);
      }

      100% {
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
      }
    }

    @media (max-width: 640px) {
      .vietqr-resume-widget {
        right: 12px;
        bottom: 160px;
      }
    }

    .vietqr-payment-page[data-invoice-status="expired"] ~ .vietqr-resume-widget {
      display: none !important;
    }

  </style>
  <script>
    (() => {
      const cartStorageKey = 'shopnoiy:cart:v1';
      const vietqrResumeStorageKey = 'shopnoiy:vietqr-pending:v1';
      const savedLoginFromSuccess = @json(session('frontend_saved_login'));
      const cartToastElement = document.querySelector('[data-cart-toast]');
      const cartToastClose = document.querySelector('[data-cart-toast-close]');
      const cartToastImage = document.querySelector('[data-cart-toast-image]');
      const cartToastName = document.querySelector('[data-cart-toast-name]');
      const cartToastVariant = document.querySelector('[data-cart-toast-variant]');
      const cartToastPrice = document.querySelector('[data-cart-toast-price]');
      const vietqrResumeWidget = document.querySelector('[data-vietqr-resume]');
      const vietqrResumeTimer = document.querySelector('[data-vietqr-resume-timer]');
      let cartToastTimer = null;
      let vietqrResumeTimerId = null;
      let vietqrStatusCheckTimerId = null;

      if (typeof savedLoginFromSuccess === 'string' && savedLoginFromSuccess.trim() !== '') {
        try {
          localStorage.setItem('shopnoiy_saved_login', savedLoginFromSuccess.trim());
          localStorage.removeItem('shopnoiy_saved_password');
        } catch (error) {}
      }

      const parseCartItems = () => {
        try {
          const payload = JSON.parse(localStorage.getItem(cartStorageKey) || '[]');
          return Array.isArray(payload) ? payload : [];
        } catch (error) {
          return [];
        }
      };

      const saveCartItems = (items) => {
        localStorage.setItem(cartStorageKey, JSON.stringify(items));
        window.dispatchEvent(new CustomEvent('shopnoiy-cart-updated', {
          detail: {
            items,
            count: items.reduce((total, item) => total + (Number(item.qty) || 0), 0)
          }
        }));
      };

      const readPendingVietqrPayment = () => {
        try {
          const payload = JSON.parse(localStorage.getItem(vietqrResumeStorageKey) || 'null');
          return payload && typeof payload === 'object' ? payload : null;
        } catch (error) {
          return null;
        }
      };

      const clearPendingVietqrPayment = () => {
        localStorage.removeItem(vietqrResumeStorageKey);
      };

      const hasUsablePendingVietqrPayment = (payload) => {
        if (!payload || typeof payload !== 'object') {
          return false;
        }

        const url = String(payload.url || '').trim();
        const statusUrl = String(payload.status_url || '').trim();
        const invoiceCode = String(payload.invoice_code || '').trim();
        const expiresAt = Number(payload.expires_at || 0);

        return url !== '' && statusUrl !== '' && invoiceCode !== '' && expiresAt > 0;
      };

      const syncExpiredPendingVietqr = (payload) => {
        if (!payload || !payload.status_url) {
          return;
        }

        window.fetch(payload.status_url, {
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          credentials: 'same-origin'
        }).catch(() => {});
      };

      const formatCountdown = (remainingMs) => {
        const totalSeconds = Math.max(0, Math.ceil(remainingMs / 1000));
        const minutes = Math.floor(totalSeconds / 60);
        const seconds = totalSeconds % 60;

        return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
      };

      const hideVietqrResumeWidget = () => {
        if (vietqrResumeTimerId) {
          window.clearInterval(vietqrResumeTimerId);
          vietqrResumeTimerId = null;
        }

        if (vietqrStatusCheckTimerId) {
          window.clearInterval(vietqrStatusCheckTimerId);
          vietqrStatusCheckTimerId = null;
        }

        if (vietqrResumeWidget) {
          vietqrResumeWidget.hidden = true;
          vietqrResumeWidget.style.display = 'none';
          vietqrResumeWidget.style.opacity = '0';
          vietqrResumeWidget.style.visibility = 'hidden';
        }
      };

      const schedulePendingVietqrStatusCheck = (payload) => {
        if (vietqrStatusCheckTimerId) {
          window.clearInterval(vietqrStatusCheckTimerId);
          vietqrStatusCheckTimerId = null;
        }

        if (!payload || !payload.status_url || window.location.href === payload.url) {
          return;
        }

        const checkStatus = () => {
          const activePayment = readPendingVietqrPayment();
          if (!activePayment || activePayment.status_url !== payload.status_url) {
            hideVietqrResumeWidget();
            return;
          }

          window.fetch(activePayment.status_url, {
            headers: {
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
          })
            .then((response) => response.ok ? response.json() : null)
            .then((data) => {
              if (!data || data.success !== true) {
                clearPendingVietqrPayment();
                hideVietqrResumeWidget();
                return;
              }

              if (
                data.payment_status !== 'unpaid'
                || data.invoice_status !== 'pending_payment'
                || data.invoice_status === 'expired'
                || data.is_expired === true
              ) {
                clearPendingVietqrPayment();
                hideVietqrResumeWidget();
              }
            })
            .catch(() => {});
        };

        checkStatus();
        vietqrStatusCheckTimerId = window.setInterval(checkStatus, 15000);
      };

      const syncPendingVietqrWidget = () => {
        const payload = readPendingVietqrPayment();
        const expiredPaymentPage = document.querySelector('.vietqr-payment-page[data-invoice-status="expired"]');

        if (expiredPaymentPage) {
          clearPendingVietqrPayment();
          if (vietqrResumeWidget) {
            vietqrResumeWidget.remove();
          }
          hideVietqrResumeWidget();
          return;
        }

        if (!vietqrResumeWidget || !hasUsablePendingVietqrPayment(payload)) {
          clearPendingVietqrPayment();
          hideVietqrResumeWidget();
          return;
        }

        const expiresAt = Number(payload.expires_at || 0);
        if (!expiresAt || expiresAt <= Date.now()) {
          syncExpiredPendingVietqr(payload);
          clearPendingVietqrPayment();
          hideVietqrResumeWidget();
          return;
        }

        const renderCountdown = () => {
          const activePayment = readPendingVietqrPayment();
          const activeExpiresAt = Number(activePayment?.expires_at || 0);

          if (!activePayment || !activeExpiresAt || activeExpiresAt <= Date.now()) {
            syncExpiredPendingVietqr(activePayment);
            clearPendingVietqrPayment();
            hideVietqrResumeWidget();
            return;
          }

          if (vietqrResumeTimer) {
            vietqrResumeTimer.textContent = formatCountdown(activeExpiresAt - Date.now());
          }
        };

        const activateWidget = () => {
          vietqrResumeWidget.href = payload.url || '#';
          vietqrResumeWidget.hidden = false;
          vietqrResumeWidget.style.display = 'flex';
          vietqrResumeWidget.style.opacity = '';
          vietqrResumeWidget.style.visibility = '';

          renderCountdown();

          if (vietqrResumeTimerId) {
            window.clearInterval(vietqrResumeTimerId);
          }

          vietqrResumeTimerId = window.setInterval(renderCountdown, 1000);
          schedulePendingVietqrStatusCheck(payload);
        };

        hideVietqrResumeWidget();

        window.fetch(payload.status_url, {
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          credentials: 'same-origin'
        })
          .then((response) => response.ok ? response.json() : null)
          .then((data) => {
            if (!data || data.success !== true) {
              clearPendingVietqrPayment();
              hideVietqrResumeWidget();
              return;
            }

            if (
              data.payment_status !== 'unpaid'
              || data.invoice_status !== 'pending_payment'
              || data.invoice_status === 'expired'
              || data.is_expired === true
            ) {
              clearPendingVietqrPayment();
              hideVietqrResumeWidget();
              return;
            }

            activateWidget();
          })
          .catch(() => {
            hideVietqrResumeWidget();
          });
      };
      const syncCartBadges = () => {
        const count = parseCartItems().reduce((total, item) => total + (Number(item.qty) || 0), 0);

        document.querySelectorAll('.bell-wrap, .cat-bell-wrap, .home-bottom-nav__link--cart').forEach((link) => {
          let badge = link.querySelector('.badge');

          if (count <= 0) {
            if (badge) {
              badge.remove();
            }
            return;
          }

          if (!badge) {
            badge = document.createElement('span');
            badge.className = 'badge';
            link.appendChild(badge);
          }

          badge.textContent = count > 99 ? '99+' : String(count);
        });
      };

      window.ShopNoiyCart = {
        key: cartStorageKey,
        getItems() {
          return parseCartItems();
        },
        setItems(items) {
          saveCartItems(Array.isArray(items) ? items : []);
        },
        clear() {
          saveCartItems([]);
        },
        count() {
          return parseCartItems().reduce((total, item) => total + (Number(item.qty) || 0), 0);
        },
        subtotal() {
          return parseCartItems().reduce((total, item) => total + ((Number(item.price) || 0) * (Number(item.qty) || 0)), 0);
        },
        addItem(item) {
          const items = parseCartItems();
          const qty = Math.max(1, Number(item.qty) || 1);
          const existingIndex = items.findIndex((entry) =>
            String(entry.product_id) === String(item.product_id)
            && String(entry.color || '') === String(item.color || '')
            && String(entry.size || '') === String(item.size || '')
          );

          if (existingIndex >= 0) {
            items[existingIndex].qty = Math.min(99, (Number(items[existingIndex].qty) || 0) + qty);
          } else {
            items.push({
              product_id: item.product_id,
              slug: item.slug || '',
              name: item.name || '',
              price: Number(item.price) || 0,
              qty,
              color: item.color || '',
              size: item.size || '',
              image_url: item.image_url || '',
            });
          }

          saveCartItems(items);
          return items;
        },
        updateItem(index, payload) {
          const items = parseCartItems();
          if (!items[index]) {
            return items;
          }

          const currentItem = items[index];
          const nextItem = {
            ...currentItem,
            ...payload,
          };

          const mergedIndex = items.findIndex((entry, entryIndex) =>
            entryIndex !== index
            && String(entry.product_id) === String(nextItem.product_id)
            && String(entry.color || '') === String(nextItem.color || '')
            && String(entry.size || '') === String(nextItem.size || '')
          );

          if (mergedIndex >= 0) {
            items[mergedIndex].qty = Math.min(99, (Number(items[mergedIndex].qty) || 0) + (Number(nextItem.qty) || 1));
            items.splice(index, 1);
          } else {
            items[index] = nextItem;
          }

          saveCartItems(items);
          return items;
        },
        updateQuantity(index, qty) {
          const items = parseCartItems();
          if (!items[index]) return items;

          const nextQty = Math.max(1, Math.min(99, Number(qty) || 1));
          items[index].qty = nextQty;
          saveCartItems(items);
          return items;
        },
        removeItem(index) {
          const items = parseCartItems();
          if (!items[index]) return items;

          items.splice(index, 1);
          saveCartItems(items);
          return items;
        },
        showToast(item) {
          if (!cartToastElement || !item) {
            return;
          }

          const formatMoney = (value) => `${new Intl.NumberFormat('vi-VN').format(Number(value) || 0)}đ`;
          const variantText = [item.color, item.size].filter(Boolean).join(' / ');

          if (cartToastImage) {
            cartToastImage.src = item.image_url || '';
            cartToastImage.alt = item.name || 'Sản phẩm vừa thêm vào giỏ';
          }

          if (cartToastName) {
            cartToastName.textContent = item.name || '';
          }

          if (cartToastVariant) {
            const quantityText = `Số lượng: ${Number(item.qty) || 1}`;
            cartToastVariant.textContent = variantText ? `${quantityText}  ${variantText}` : quantityText;
          }

          if (cartToastPrice) {
            cartToastPrice.textContent = formatMoney(item.price);
          }

          cartToastElement.hidden = false;
          cartToastElement.classList.add('is-visible');

          window.clearTimeout(cartToastTimer);
          cartToastTimer = window.setTimeout(() => {
            cartToastElement.classList.remove('is-visible');
            window.setTimeout(() => {
              cartToastElement.hidden = true;
            }, 220);
          }, 2600);
        }
      };
      syncCartBadges();
      window.addEventListener('shopnoiy-cart-updated', syncCartBadges);

      if (cartToastClose && cartToastElement) {
        cartToastClose.addEventListener('click', () => {
          window.clearTimeout(cartToastTimer);
          cartToastElement.classList.remove('is-visible');
          window.setTimeout(() => {
            cartToastElement.hidden = true;
          }, 220);
        });
      }

      const scrollKey = `shopnoiy:scroll:${window.location.href}`;
      const restoreUrlKey = 'shopnoiy:restore-url';
      const searchOriginKey = 'shopnoiy:search-origin-url';
      const searchDepthKey = 'shopnoiy:search-depth';
      const productLinkSelector = '.featured-product-link, .cat-product-link, .subcat-product-link, .flash-sale-link';
      const historyKey = 'shopnoiy:recent-searches';
      const searchPagePath = `{{ route('frontend.search', [], false) }}`;
      const navigationType = window.performance?.getEntriesByType?.('navigation')?.[0]?.type || 'navigate';

      const getSearchOverlayElement = () => document.querySelector('[data-search-overlay]');
      const getSearchOverlayInput = () => getSearchOverlayElement()?.querySelector('input[name="q"]');
      const isSearchPageUrl = (value) => {
        if (!value) {
          return false;
        }

        try {
          const url = new URL(value, window.location.origin);
          return url.origin === window.location.origin && url.pathname === searchPagePath;
        } catch (error) {
          return false;
        }
      };

      const readSearchOrigin = () => {
        const value = sessionStorage.getItem(searchOriginKey) || '';
        return isSearchPageUrl(value) ? '' : value;
      };

      const writeSearchOrigin = (value) => {
        if (!value || isSearchPageUrl(value)) {
          return;
        }

        sessionStorage.setItem(searchOriginKey, value);
      };

      const readSearchDepth = () => {
        const value = Number(sessionStorage.getItem(searchDepthKey) || 0);
        return Number.isFinite(value) && value > 0 ? Math.floor(value) : 0;
      };

      const writeSearchDepth = (value) => {
        const nextValue = Number(value);

        if (!Number.isFinite(nextValue) || nextValue <= 0) {
          sessionStorage.removeItem(searchDepthKey);
          return;
        }

        sessionStorage.setItem(searchDepthKey, String(Math.floor(nextValue)));
      };

      const syncSearchOrigin = () => {
        if (isSearchPageUrl(window.location.href)) {
          const currentOrigin = readSearchOrigin();
          if (currentOrigin) {
            return;
          }

          if (document.referrer && !isSearchPageUrl(document.referrer)) {
            writeSearchOrigin(document.referrer);
          }

          return;
        }

        writeSearchOrigin(window.location.href);
      };

      const syncSearchDepth = () => {
        if (!isSearchPageUrl(window.location.href)) {
          writeSearchDepth(0);
          return;
        }

        if (readSearchDepth() <= 0) {
          writeSearchDepth(1);
        }
      };

      const saveCurrentScroll = () => {
        sessionStorage.setItem(scrollKey, String(window.scrollY || window.pageYOffset || 0));
      };

      const markCurrentPageForRestore = () => {
        saveCurrentScroll();
        sessionStorage.setItem(restoreUrlKey, window.location.href);
      };

      const navigateTo = (targetUrl) => {
        if (window.Livewire && typeof window.Livewire.navigate === 'function') {
          window.Livewire.navigate(targetUrl);
          return;
        }

        window.location.href = targetUrl;
      };

      const readSearchHistory = () => {
        try {
          const payload = JSON.parse(localStorage.getItem(historyKey) || '[]');
          return Array.isArray(payload) ? payload : [];
        } catch (error) {
          return [];
        }
      };

      const writeSearchHistory = (items) => {
        localStorage.setItem(historyKey, JSON.stringify(items.slice(0, 8)));
      };

      const renderSearchHistory = () => {
        document.querySelectorAll('[data-search-history]').forEach((historyBox) => {
          const items = readSearchHistory();

          if (!items.length) {
            historyBox.innerHTML = '<span class="search-chip is-muted">Chưa có từ khóa nào</span>';
            return;
          }

          historyBox.innerHTML = items.map((item) => `
            <button type="button" class="search-chip" data-search-history-item="${item.replace(/"/g, '&quot;')}">
              <i class="bi bi-clock-history"></i>${item}
            </button>
          `).join('');
        });
      };

      const focusSearchOverlayInput = () => {
        const searchInput = getSearchOverlayInput();
        if (!searchInput) {
          return;
        }

        window.requestAnimationFrame(() => {
          searchInput.focus({ preventScroll: true });
          if (searchInput.value) {
            searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
          }
        });
      };

      const focusSearchPageInput = () => {
        const searchInput = document.querySelector('.search-page-form input[name="q"]');
        const searchPage = document.querySelector('.search-page');

        if (!searchInput || !searchPage || searchPage.closest('[data-search-overlay]')) {
          return;
        }

        window.requestAnimationFrame(() => {
          searchInput.focus({ preventScroll: true });

          if (searchInput.value) {
            searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
          }
        });
      };

      const openSearchOverlay = (initialValue = '') => {
        const overlayElement = getSearchOverlayElement();
        if (!overlayElement) {
          return false;
        }

        document.body.classList.add('search-overlay-open');
        overlayElement.hidden = false;
        overlayElement.classList.add('is-open');
        renderSearchHistory();
        const searchInput = getSearchOverlayInput();
        if (searchInput && initialValue.trim()) {
          searchInput.value = initialValue.trim();
          searchInput.dispatchEvent(new Event('input', { bubbles: true }));
          searchInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
        focusSearchOverlayInput();

        return true;
      };

      const closeSearchOverlay = () => {
        const overlayElement = getSearchOverlayElement();
        if (!overlayElement) {
          return;
        }

        overlayElement.classList.remove('is-open');
        overlayElement.hidden = true;
        document.body.classList.remove('search-overlay-open');
      };

      document.addEventListener('click', (event) => {
        if (event.target.closest(productLinkSelector)) {
          markCurrentPageForRestore();
        }
      });

      document.addEventListener('click', (event) => {
        const searchLink = event.target.closest('.search-entry-link');
        if (!searchLink || !searchLink.getAttribute('href')) {
          return;
        }

        if (event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
          return;
        }

        event.preventDefault();
        writeSearchOrigin(window.location.href);
        const currentSearchText = searchLink.classList.contains('has-value')
          ? (searchLink.querySelector('.search-entry-text')?.textContent || '')
          : '';

        if (!openSearchOverlay(currentSearchText)) {
          navigateTo(searchLink.getAttribute('href'));
        }
      });

      document.addEventListener('click', (event) => {
        if (event.target.closest('[data-search-overlay-close]')) {
          event.preventDefault();
          closeSearchOverlay();
        }
      });

      document.addEventListener('click', (event) => {
        const historyItem = event.target.closest('[data-search-history-item]');
        if (!historyItem) {
          return;
        }

        const searchInput = event.target.closest('[data-search-overlay], .search-page')?.querySelector('input[name="q"]')
          || document.querySelector('.search-page-form input[name="q"]');

        if (!searchInput) {
          return;
        }

        const value = historyItem.getAttribute('data-search-history-item') || '';
        searchInput.value = value;
        searchInput.dispatchEvent(new Event('input', { bubbles: true }));
        searchInput.dispatchEvent(new Event('change', { bubbles: true }));
        focusSearchOverlayInput();
      });

      document.addEventListener('click', (event) => {
        const cartLink = event.target.closest('.bell-wrap, .cat-bell-wrap, .home-bottom-nav__link--cart');
        if (!cartLink || !cartLink.getAttribute('href')) {
          return;
        }

        if (event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
          return;
        }

        event.preventDefault();
        window.location.href = cartLink.getAttribute('href');
      });

      window.addEventListener('pagehide', saveCurrentScroll);

      window.addEventListener('pageshow', () => {
        const restoreUrl = sessionStorage.getItem(restoreUrlKey);
        const savedScroll = sessionStorage.getItem(scrollKey);

        if (restoreUrl === window.location.href && savedScroll !== null) {
          window.requestAnimationFrame(() => {
            window.scrollTo(0, Number(savedScroll));
            sessionStorage.removeItem(restoreUrlKey);
            sessionStorage.removeItem(scrollKey);
          });
        }

        focusSearchPageInput();
      });

      document.querySelectorAll('[data-history-back="true"]').forEach((button) => {
        button.addEventListener('click', (event) => {
          if (window.history.length > 1) {
            event.preventDefault();
            window.history.back();
          }
        });
      });

      document.querySelectorAll('[data-search-page-back="true"]').forEach((button) => {
        const searchOrigin = readSearchOrigin();

        if (searchOrigin) {
          button.setAttribute('href', searchOrigin);
        }

        button.addEventListener('click', (event) => {
          const targetUrl = readSearchOrigin();
          const searchDepth = readSearchDepth();

          if (searchDepth > 0 && window.history.length > searchDepth) {
            event.preventDefault();
            writeSearchDepth(0);
            window.history.go(-searchDepth);

            return;
          }

          if (!targetUrl) {
            if (window.history.length > 1) {
              event.preventDefault();
              window.history.back();
            }

            return;
          }

          event.preventDefault();
          writeSearchDepth(0);
          window.location.replace(targetUrl);
        });
      });

      document.addEventListener('submit', (event) => {
        const searchForm = event.target.closest('form[data-search-history-form]');
        if (!searchForm) {
          return;
        }

        const searchInput = searchForm.querySelector('input[name="q"]');
        const value = (searchInput ? searchInput.value : '').trim();
        if (!value) {
          return;
        }

        if (isSearchPageUrl(window.location.href) && searchForm.closest('.search-page') && !searchForm.closest('[data-search-overlay]')) {
          writeSearchDepth(readSearchDepth() + 1);
        }

        writeSearchHistory([value, ...readSearchHistory().filter((item) => item !== value)]);
        renderSearchHistory();
      });

      document.addEventListener('click', (event) => {
        const clearButton = event.target.closest('.search-clear-history');
        if (!clearButton) {
          return;
        }

        localStorage.removeItem(historyKey);
        renderSearchHistory();
      });

      document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
          closeSearchOverlay();
        }
      });

      const initSearchAutocomplete = () => {
        document.querySelectorAll('.search-form[data-search-autocomplete]').forEach((form) => {
          if (form.dataset.autocompleteBound === 'true') {
            return;
          }

          form.dataset.autocompleteBound = 'true';

          const input = form.querySelector('input[name="q"]');
          const endpoint = form.dataset.searchAutocomplete;
          const searchPageUrl = form.dataset.searchPage;

          if (!input || !endpoint) {
            return;
          }

          if (searchPageUrl && !form.closest('.search-page')) {
            const openSearchPage = () => {
              const targetUrl = new URL(searchPageUrl, window.location.origin);
              const currentValue = input.value.trim();

              if (currentValue) {
                targetUrl.searchParams.set('q', currentValue);
              }

              navigateTo(targetUrl.toString());
            };

            input.addEventListener('focus', openSearchPage);
            input.addEventListener('mousedown', (event) => {
              event.preventDefault();
              openSearchPage();
            });

            return;
          }

          form.addEventListener('submit', (event) => {
            if (form.hasAttribute('data-livewire-submit') || form.hasAttribute('wire:submit')) {
              return;
            }

            if (event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
              return;
            }

            event.preventDefault();

            const targetUrl = new URL(form.getAttribute('action') || window.location.href, window.location.origin);
            const currentValue = input.value.trim();

            if (currentValue) {
              targetUrl.searchParams.set('q', currentValue);
            } else {
              targetUrl.searchParams.delete('q');
            }

            navigateTo(targetUrl.toString());
          });

          let activeIndex = -1;
          let currentItems = [];
          let debounceTimer = null;
          let abortController = null;
          let lastIssuedQuery = '';
          let isLoadingMore = false;
          let hasMoreSuggestions = false;
          let suggestionOffset = 0;
          const suggestionPageSize = 10;

          const suggestionBox = document.createElement('div');
          suggestionBox.className = 'search-suggest-box';
          form.appendChild(suggestionBox);
          input.setAttribute('autocomplete', 'off');

          const ensureSuggestionBoxAttached = () => {
            if (!form.contains(suggestionBox)) {
              form.appendChild(suggestionBox);
            }
          };

          const escapeHtml = (value) => String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');

          const closeSuggestions = () => {
            ensureSuggestionBoxAttached();
            activeIndex = -1;
            currentItems = [];
            hasMoreSuggestions = false;
            suggestionOffset = 0;
            isLoadingMore = false;
            suggestionBox.innerHTML = '';
            suggestionBox.classList.remove('is-visible');
          };

          const renderLoadMoreButton = () => {
            suggestionBox.querySelector('.search-suggest-more')?.remove();

            if (!hasMoreSuggestions) {
              return;
            }

            suggestionBox.insertAdjacentHTML('beforeend', `
              <button class="search-suggest-more" type="button">
                Xem thêm
              </button>
            `);
          };

          const renderSuggestionItems = (items, append = false) => {
            if (!append) {
              currentItems = [];
              suggestionBox.innerHTML = '';
              activeIndex = -1;
            }

            if (!items.length && !append) {
              ensureSuggestionBoxAttached();
              suggestionBox.innerHTML = '<div class="search-suggest-empty">Không thấy sản phẩm phù hợp.</div>';
              suggestionBox.classList.add('is-visible');

              return;
            }

            const startIndex = currentItems.length;
            currentItems = append ? currentItems.concat(items) : items;

            const html = items.map((item, index) => `
            <button class="search-suggest-item" type="button" data-index="${startIndex + index}" data-url="${item.url}">
              <span class="search-suggest-name"><i class="bi bi-search"></i>${escapeHtml(item.name)}</span>
            </button>
          `).join('');

            if (append) {
              suggestionBox.insertAdjacentHTML('beforeend', html);
            } else {
              suggestionBox.innerHTML = html;
            }

            ensureSuggestionBoxAttached();
            suggestionBox.classList.add('is-visible');
            renderLoadMoreButton();
          };

          const syncActiveItem = () => {
            suggestionBox.querySelectorAll('.search-suggest-item').forEach((item, index) => {
              item.classList.toggle('is-active', index === activeIndex);
            });
          };

          const requestSuggestions = async (query, offset = 0) => {
            if (abortController) {
              abortController.abort();
            }

            abortController = new AbortController();

            try {
              const url = new URL(endpoint, window.location.origin);
              url.searchParams.set('q', query);
              lastIssuedQuery = query;
              url.searchParams.set('limit', String(suggestionPageSize));
              url.searchParams.set('offset', String(offset));

              if (form.dataset.categoryId) {
                url.searchParams.set('category_id', form.dataset.categoryId);
              }

              const response = await fetch(url.toString(), {
                headers: {
                  'X-Requested-With': 'XMLHttpRequest',
                  'Accept': 'application/json'
                },
                signal: abortController.signal
              });

              if (!response.ok) {
                closeSuggestions();

                return;
              }

              const payload = await response.json();
              if (input.value.trim() !== lastIssuedQuery) {
                return null;
              }

              return payload;
            } catch (error) {
              if (error.name !== 'AbortError') {
                closeSuggestions();
              }

              return null;
            }
          };

          const fetchSuggestions = async () => {
            const query = input.value.trim();

            if (query.length < 2) {
              closeSuggestions();

              return;
            }

            const payload = await requestSuggestions(query, 0);
            if (!payload) {
              return;
            }

            hasMoreSuggestions = Boolean(payload.has_more);
            suggestionOffset = Array.isArray(payload.suggestions) ? payload.suggestions.length : 0;
            renderSuggestionItems(Array.isArray(payload.suggestions) ? payload.suggestions : []);
          };

          const loadMoreSuggestions = async () => {
            if (isLoadingMore || !hasMoreSuggestions) {
              return;
            }

            const query = input.value.trim();
            if (query.length < 2) {
              return;
            }

            isLoadingMore = true;
            const payload = await requestSuggestions(query, suggestionOffset);
            isLoadingMore = false;

            if (!payload) {
              return;
            }

            const items = Array.isArray(payload.suggestions) ? payload.suggestions : [];
            suggestionOffset += items.length;
            hasMoreSuggestions = Boolean(payload.has_more);
            renderSuggestionItems(items, true);
          };

          input.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = window.setTimeout(fetchSuggestions, 180);
          });

          input.addEventListener('focus', () => {
            if (currentItems.length) {
              ensureSuggestionBoxAttached();
              suggestionBox.classList.add('is-visible');
            }
          });

          input.addEventListener('keydown', (event) => {
            if (!suggestionBox.classList.contains('is-visible') || !currentItems.length) {
              return;
            }

            if (event.key === 'ArrowDown') {
              event.preventDefault();
              activeIndex = Math.min(activeIndex + 1, currentItems.length - 1);
              syncActiveItem();
              return;
            }

            if (event.key === 'ArrowUp') {
              event.preventDefault();
              activeIndex = Math.max(activeIndex - 1, 0);
              syncActiveItem();
              return;
            }

            if (event.key === 'Enter' && activeIndex >= 0 && currentItems[activeIndex]) {
              event.preventDefault();
              navigateTo(currentItems[activeIndex].url);
            }

            if (event.key === 'Escape') {
              closeSuggestions();
            }
          });

          suggestionBox.addEventListener('click', (event) => {
            const loadMoreButton = event.target.closest('.search-suggest-more');
            if (loadMoreButton) {
              event.preventDefault();
              loadMoreSuggestions();
              return;
            }

            const item = event.target.closest('.search-suggest-item');

            if (!item) {
              return;
            }

            event.preventDefault();
            navigateTo(item.dataset.url);
          });

          suggestionBox.addEventListener('touchmove', (event) => {
            event.stopPropagation();
          }, { passive: true });

          suggestionBox.addEventListener('scroll', () => {
            if ((suggestionBox.scrollTop + suggestionBox.clientHeight) >= (suggestionBox.scrollHeight - 24)) {
              loadMoreSuggestions();
            }
          });

          document.addEventListener('click', (event) => {
            if (!form.contains(event.target)) {
              closeSuggestions();
            }
          });
        });
      };

      initSearchAutocomplete();

      const searchAutocompleteObserver = new MutationObserver(() => {
        initSearchAutocomplete();
      });

      searchAutocompleteObserver.observe(document.body, {
        childList: true,
        subtree: true,
      });

      syncSearchOrigin();
      syncSearchDepth();
      renderSearchHistory();
      closeSearchOverlay();
      focusSearchPageInput();
      syncPendingVietqrWidget();

      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      const visitorStorageKey = 'shopnoiy:visitor:v1';
      const visitorTrackingRoute = @json(route('frontend.visitor-tracking'));
      const visitorBaseContext = {
        route_name: @json(request()->route()?->getName()),
        page_type: @json($visitorPageType),
        activity_label: @json($visitorActivityLabel),
        meta: @json($visitorMeta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
      };
      let visitorCurrentMeta = { ...(visitorBaseContext.meta || {}) };

      const createVisitorToken = () => {
        if (window.crypto && typeof window.crypto.randomUUID === 'function') {
          return `v-${window.crypto.randomUUID()}`;
        }

        return `v-${Date.now()}-${Math.random().toString(36).slice(2, 12)}`;
      };

      const readVisitorToken = () => {
        try {
          const existing = localStorage.getItem(visitorStorageKey);
          if (existing && existing.trim()) {
            return existing.trim();
          }
        } catch (error) {}

        const generated = createVisitorToken();

        try {
          localStorage.setItem(visitorStorageKey, generated);
        } catch (error) {}

        return generated;
      };

      let visitorLastTrackedAt = 0;
      let visitorTrackTimerId = null;

      const postVisitorTracking = (extra = {}, useBeacon = false) => {
        const items = parseCartItems();
        const mergedMeta = {
          ...visitorCurrentMeta,
          ...((extra && typeof extra.meta === 'object' && extra.meta !== null) ? extra.meta : {}),
        };
        visitorCurrentMeta = mergedMeta;
        const payload = {
          visitor_token: readVisitorToken(),
          route_name: visitorBaseContext.route_name,
          page_type: visitorBaseContext.page_type,
          activity_label: visitorBaseContext.activity_label,
          page_title: document.title || '',
          current_path: window.location.pathname || '',
          current_url: window.location.href || '',
          referrer_url: document.referrer || '',
          cart_count: items.reduce((total, item) => total + (Number(item.qty) || 0), 0),
          cart_value: Math.round(items.reduce((total, item) => total + ((Number(item.price) || 0) * (Number(item.qty) || 0)), 0)),
          meta: mergedMeta,
          ...extra,
        };

        if (useBeacon && navigator.sendBeacon) {
          const blob = new Blob([JSON.stringify(payload)], { type: 'application/json' });
          navigator.sendBeacon(visitorTrackingRoute, blob);
          return;
        }

        window.fetch(visitorTrackingRoute, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
          },
          credentials: 'same-origin',
          body: JSON.stringify(payload)
        }).catch(() => {});

        visitorLastTrackedAt = Date.now();
      };

      const scheduleVisitorTracking = (extra = {}, delayMs = 1200) => {
        if (visitorTrackTimerId) {
          window.clearTimeout(visitorTrackTimerId);
        }

        visitorTrackTimerId = window.setTimeout(() => {
          postVisitorTracking(extra);
        }, delayMs);
      };

      let visitorHeartbeatId = null;
      const startVisitorHeartbeat = () => {
        if (visitorHeartbeatId) {
          window.clearInterval(visitorHeartbeatId);
        }

        postVisitorTracking();

        visitorHeartbeatId = window.setInterval(() => {
          if (document.visibilityState === 'visible') {
            if ((Date.now() - visitorLastTrackedAt) >= 120000) {
              postVisitorTracking();
            }
          }
        }, 120000);
      };

      window.ShopNoiyVisitorTracking = {
        update(extra = {}) {
          scheduleVisitorTracking(extra);
        }
      };

      startVisitorHeartbeat();
      window.addEventListener('shopnoiy-cart-updated', () => scheduleVisitorTracking({}, 2500));
      window.addEventListener('pagehide', () => postVisitorTracking({}, true));

      window.addEventListener('storage', (event) => {
        if (event.key === vietqrResumeStorageKey) {
          syncPendingVietqrWidget();
        }
      });

      window.addEventListener('focus', syncPendingVietqrWidget);
      document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
          syncPendingVietqrWidget();
          if ((Date.now() - visitorLastTrackedAt) >= 30000) {
            scheduleVisitorTracking({}, 600);
          }
        }
      });

      document.addEventListener('livewire:navigated', () => {
        focusSearchPageInput();
      });
    })();
  </script>
  @stack('scripts')
</body>
</html>
