@extends('frontend.layouts.app')

@section('title', 'Đăng nhập')
@section('meta_title', 'Đăng nhập khách hàng')
@section('meta_description', 'Đăng nhập tài khoản khách hàng để tiếp tục mua sắm tại Shop Nội Y Buôn Hồ.')
@section('meta_robots', 'noindex,nofollow')

@push('head')
  <style>
    body {
      /* background: #ffffff; */
      color: #303330;
    }

    .phone.login-page,
    .login-page {
      min-height: 100vh;
      padding: 72px 16px 48px;
      background: #ffffff;
      font-family: 'Be Vietnam Pro', sans-serif;
    }

    .login-shell {
      width: min(100%, 460px);
      margin: 0 auto;
    }

    .login-page .topbar {
      /* max-width: 460px; */
      margin-left: auto;
      margin-right: auto;
    }

    .login-page .topbar {
      margin-bottom: 12px;
    }

    .login-user-button {
      width: 36px;
      height: 36px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 14px;
      border: 1px solid rgba(148, 163, 184, 0.28);
      background: linear-gradient(180deg, #ffffff 0%, #f7fafd 100%);
      box-shadow: 0 10px 20px rgba(30, 41, 59, 0.12);
      color: #252c2c;
      text-decoration: none;
      flex-shrink: 0;
    }

    .login-user-button:hover,
    .login-user-button:focus-visible {
      color: #252c2c;
      transform: translateY(-1px);
    }

    .login-page .topbar .login-user-icon {
      width: 22px;
      height: 22px;
      display: inline-block;
      color: #252c2c;
    }

    .login-card {
      /* padding: 20px; */
      /* border-radius: 28px; */
      background: rgba(255, 255, 255, 0.88);
      /* box-shadow: 0 24px 60px rgba(95, 70, 49, 0.12); */
      /* backdrop-filter: blur(10px); */
    }

    .login-brand-hero {
      width: 100%;
      min-height: 140px;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 30px 24px 4px;
      /* margin-bottom: 24px; */
    }

    .login-brand-hero img {
      max-width: min(100%, 240px);
      max-height: 120px;
      object-fit: contain;
      display: block;
    }

    .login-brand-fallback {
      text-align: center;
      color: #303330;
    }

    .login-brand-fallback strong {
      display: block;
      font-size: clamp(1.8rem, 5vw, 2.4rem);
      letter-spacing: -0.03em;
    }

    .login-brand-fallback span {
      display: block;
      margin-top: 8px;
      font-size: 0.95rem;
      color: #6d6f6b;
    }

    .login-display {
      margin-bottom: 22px;
      text-align: center;
    }

    .login-display h1 {
      margin: 0 0 8px;
      font-size: clamp(2rem, 6vw, 2.45rem);
      color: #533823;
      letter-spacing: -0.03em;
    }

    .login-display p {
      margin: 0;
      color: #6d6f6b;
      font-size: 0.95rem;
    }

    .login-alert {
      margin-bottom: 16px;
      padding: 14px 16px;
      border-radius: 18px;
      font-size: 0.92rem;
    }

    .login-alert-success {
      color: #14532d;
      background: #dcfce7;
      border: 1px solid #bbf7d0;
    }

    .login-alert-error {
      color: #991b1b;
      background: #fee2e2;
      border: 1px solid #fecaca;
    }

    .login-field {
      margin-bottom: 16px;
    }

    .login-label {
      display: block;
      margin-bottom: 8px;
      font-size: 0.78rem;
      letter-spacing: 0.12em;
      color: #5b4632;
      text-transform: uppercase;
    }

    .login-input-wrap {
      position: relative;
    }

    .login-input {
      width: 100%;
      border: 1px solid #252c2c;
      border-radius: 999px;
      padding: 15px 52px 15px 18px;
      background: #ffffff;
      color: #2f241a;
      font-size: 0.96rem;
      transition: box-shadow 0.2s ease, transform 0.2s ease;
    }

    .login-input:focus {
      outline: none;
      box-shadow: 0 0 0 3px rgba(125, 87, 49, 0.18);
      transform: translateY(-1px);
    }

    .login-input.is-invalid {
      border-color: #dc2626;
      box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.12);
    }

    .login-field-error {
      display: block;
      margin-top: 8px;
      padding-left: 6px;
      color: #b91c1c;
      font-size: 0.82rem;
      line-height: 1.4;
    }

    .login-input-icon {
      position: absolute;
      top: 50%;
      right: 18px;
      transform: translateY(-50%);
      color: #8c8174;
      font-size: 1.15rem;
      pointer-events: none;
    }

    .login-input-toggle {
      position: absolute;
      top: 50%;
      right: 12px;
      transform: translateY(-50%);
      width: 38px;
      height: 38px;
      border: 0;
      border-radius: 999px;
      background: transparent;
      color: #6d6f6b;
      font-size: 1.18rem;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
    }

    .login-meta {
      display: flex;
      justify-content: flex-end;
      margin: 8px 0 18px;
      font-size: 0.9rem;
    }

    .login-link {
      color: #7d5731;
      text-decoration: none;
    }

    .login-link:hover {
      color: #5d3e21;
    }

    .login-submit {
      width: 100%;
      border: 0;
      border-radius: 999px;
      padding: 15px 20px;
      background: #252c2c;
      color: #fff;
      font-size: 0.92rem;
      letter-spacing: 0.2em;
      text-transform: uppercase;
    }

    .login-submit:hover {
      background: #1d2323;
    }

    .login-register {
      display: inline-flex;
      width: 100%;
      align-items: center;
      justify-content: center;
      margin-top: 12px;
      padding: 15px 20px;
      border: 1px solid #252c2c;
      border-radius: 999px;
      color: #252c2c;
      font-size: 0.92rem;
      text-decoration: none;
      background: transparent;
    }

    .login-register:hover {
      color: #252c2c;
      background: rgba(37, 44, 44, 0.04);
    }

    .login-footer {
      margin-top: 22px;
      text-align: center;
      color: #6d6f6b;
      font-size: 0.92rem;
    }

    .login-footer strong {
      display: block;
      margin-bottom: 6px;
      color: #2f241a;
    }

    @media (max-width: 480px) {
      .login-card {
        padding: 16px;
        border-radius: 24px;
      }
    }
  </style>
@endpush

@section('content')
  <main class="phone login-page">
    <div class="login-shell">
      @include('frontend.partials.topbar', [
        'headerClass' => 'topbar',
      ])

      <section class="login-card" aria-labelledby="customer-login-title">
        <div class="login-brand-hero">
          @if (!empty($frontendLogoUrl))
            <img
              src="{{ $frontendLogoUrl }}"
              alt="{{ $frontendSiteName }}"
              loading="eager"
              decoding="async"
            >
          @else
            <div class="login-brand-fallback">
              <strong>{{ $frontendSiteName }}</strong>
              <span>Đăng nhập để tiếp tục mua sắm cùng shop</span>
            </div>
          @endif
        </div>

        <div class="login-display">
          <h1 id="customer-login-title">Đăng nhập</h1>
        </div>

        @if (session('success'))
          <div class="login-alert login-alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('frontend.login.submit') }}">
          @csrf

          <div class="login-field">
            <label for="login" class="login-label">Số điện thoại hoặc email</label>
            <div class="login-input-wrap">
              <input
                id="login"
                name="login"
                type="text"
                class="login-input @error('login') is-invalid @enderror"
                placeholder="0901234567 hoặc email@example.com"
                value="{{ old('login') }}"
                autocomplete="username"
                required
              >
              <span class="login-input-icon">
                <i class="bi bi-person"></i>
              </span>
            </div>
            @error('login')
              <span class="login-field-error">{{ $message }}</span>
            @enderror
          </div>

          <div class="login-field">
            <label for="password" class="login-label">Mật khẩu</label>
            <div class="login-input-wrap">
              <input
                id="password"
                name="password"
                type="password"
                class="login-input @error('password') is-invalid @enderror"
                placeholder="Nhập mật khẩu"
                value="{{ old('password') }}"
                autocomplete="current-password"
                required
              >
              <button
                type="button"
                class="login-input-toggle"
                data-password-toggle
                data-target="#password"
                aria-label="Hiện mật khẩu"
                aria-pressed="false"
              >
                <i class="bi bi-eye"></i>
              </button>
            </div>
            @error('password')
              <span class="login-field-error">{{ $message }}</span>
            @enderror
          </div>

          <div class="login-meta">
            <a href="{{ route('frontend.password.forgot') }}" class="login-link">Quên mật khẩu?</a>
          </div>

          <button type="submit" class="login-submit">Đăng nhập</button>
        </form>

        <div class="login-footer">
          <strong>Chưa có tài khoản?</strong>
          <a href="{{ route('frontend.register') }}" class="login-register">Tạo tài khoản</a>
        </div>
      </section>
    </div>
  </main>
@endsection

@push('scripts')
  <script>
    (() => {
      const loginInput = document.querySelector('#login');
      const passwordInput = document.querySelector('#password');
      const loginForm = loginInput ? loginInput.closest('form') : null;
      const loginStorageKey = 'shopnoiy_saved_login';
      const passwordStorageKey = 'shopnoiy_saved_password';

      if (loginInput) {
        const serverValue = loginInput.value.trim();
        const savedRawValue = localStorage.getItem(loginStorageKey);
        const savedValue = savedRawValue ? savedRawValue.trim() : '';

        if (!serverValue && savedValue) {
          loginInput.value = savedValue;
        }

        if (serverValue) {
          localStorage.setItem(loginStorageKey, serverValue);
        }
      }

      if (passwordInput) {
        const serverPassword = passwordInput.value;
        const savedPassword = localStorage.getItem(passwordStorageKey) || '';

        if (!serverPassword && savedPassword) {
          passwordInput.value = savedPassword;
        }

        if (serverPassword) {
          localStorage.setItem(passwordStorageKey, serverPassword);
        }
      }

      if (loginForm && loginInput) {
        loginForm.addEventListener('submit', () => {
          const currentValue = loginInput.value.trim();
          const currentPassword = passwordInput ? passwordInput.value : '';

          if (currentValue) {
            localStorage.setItem(loginStorageKey, currentValue);
          } else {
            localStorage.removeItem(loginStorageKey);
          }

          if (currentPassword) {
            localStorage.setItem(passwordStorageKey, currentPassword);
          } else {
            localStorage.removeItem(passwordStorageKey);
          }
        });
      }

      document.querySelectorAll('[data-password-toggle]').forEach((toggleBtn) => {
        const targetSelector = toggleBtn.getAttribute('data-target');
        const input = targetSelector ? document.querySelector(targetSelector) : null;
        const icon = toggleBtn.querySelector('i');

        if (!input || !icon) {
          return;
        }

        toggleBtn.addEventListener('click', () => {
          const isVisible = input.type === 'text';
          input.type = isVisible ? 'password' : 'text';
          icon.className = isVisible ? 'bi bi-eye' : 'bi bi-eye-slash';
          toggleBtn.setAttribute('aria-label', isVisible ? 'Hiện mật khẩu' : 'Ẩn mật khẩu');
          toggleBtn.setAttribute('aria-pressed', isVisible ? 'false' : 'true');
        });
      });
    })();
  </script>
@endpush
