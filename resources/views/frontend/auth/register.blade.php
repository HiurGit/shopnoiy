@extends('frontend.layouts.app')

@section('title', 'Tạo tài khoản')
@section('meta_title', 'Tạo tài khoản khách hàng')
@section('meta_description', 'Tạo tài khoản khách hàng để mua sắm nhanh hơn tại Shop Nội Y Buôn Hồ.')
@section('meta_robots', 'noindex,nofollow')

@push('head')
  <style>
    body {
      color: #303330;
    }

    .phone.register-page,
    .register-page {
      min-height: 100vh;
      padding: 72px 16px 48px;
      background: #ffffff;
      font-family: 'Be Vietnam Pro', sans-serif;
    }

    .register-shell {
      width: min(100%, 460px);
      margin: 0 auto;
    }

    .register-page .topbar {
      margin-left: auto;
      margin-right: auto;
      margin-bottom: 12px;
    }

    .register-user-button {
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

    .register-page .topbar .register-user-icon {
      width: 22px;
      height: 22px;
      display: inline-block;
      color: #252c2c;
    }

    .register-card {
      background: rgba(255, 255, 255, 0.88);
    }

    .register-brand-hero {
      width: 100%;
      min-height: 140px;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 30px 24px 4px;
      /* margin-bottom: 24px; */
    }

    .register-brand-hero img {
      max-width: min(100%, 240px);
      max-height: 120px;
      object-fit: contain;
      display: block;
    }

    .register-brand-fallback {
      text-align: center;
      color: #303330;
    }

    .register-brand-fallback strong {
      display: block;
      font-size: clamp(1.8rem, 5vw, 2.4rem);
      letter-spacing: -0.03em;
    }

    .register-brand-fallback span {
      display: block;
      margin-top: 8px;
      font-size: 0.95rem;
      color: #6d6f6b;
    }

    .register-display {
      margin-bottom: 22px;
      text-align: center;
    }

    .register-display h1 {
      margin: 0 0 8px;
      font-size: clamp(2rem, 6vw, 2.45rem);
      color: #533823;
      letter-spacing: -0.03em;
    }

    .register-display p {
      margin: 0;
      color: #6d6f6b;
      font-size: 0.95rem;
    }

    .register-alert {
      margin-bottom: 16px;
      padding: 14px 16px;
      border-radius: 18px;
      font-size: 0.92rem;
    }

    .register-alert-success {
      color: #14532d;
      background: #dcfce7;
      border: 1px solid #bbf7d0;
    }

    .register-alert-error {
      color: #991b1b;
      background: #fee2e2;
      border: 1px solid #fecaca;
    }

    .register-field {
      margin-bottom: 16px;
    }

    .register-label {
      display: block;
      margin-bottom: 8px;
      font-size: 0.78rem;
      letter-spacing: 0.12em;
      color: #5b4632;
      text-transform: uppercase;
    }

    .register-input-wrap {
      position: relative;
    }

    .register-input {
      width: 100%;
      border: 1px solid #252c2c;
      border-radius: 999px;
      padding: 15px 52px 15px 18px;
      background: #ffffff;
      color: #2f241a;
      font-size: 0.96rem;
      transition: box-shadow 0.2s ease, transform 0.2s ease;
    }

    .register-input:focus {
      outline: none;
      box-shadow: 0 0 0 3px rgba(125, 87, 49, 0.18);
      transform: translateY(-1px);
    }

    .register-input.is-invalid {
      border-color: #dc2626;
      box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.12);
    }

    .register-field-error {
      display: block;
      margin-top: 8px;
      padding-left: 6px;
      color: #b91c1c;
      font-size: 0.82rem;
      line-height: 1.4;
    }

    .register-input-icon {
      position: absolute;
      top: 50%;
      right: 18px;
      transform: translateY(-50%);
      color: #8c8174;
      font-size: 1.15rem;
      pointer-events: none;
    }

    .register-input-toggle {
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

    .register-submit {
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

    .register-submit:hover {
      background: #1d2323;
    }

    .register-footer {
      margin-top: 22px;
      text-align: center;
      color: #6d6f6b;
      font-size: 0.92rem;
    }

    .register-footer strong {
      display: block;
      margin-bottom: 6px;
      color: #2f241a;
    }

    .register-login-link {
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

    .register-login-link:hover {
      color: #252c2c;
      background: rgba(37, 44, 44, 0.04);
    }

    @media (max-width: 480px) {
      .register-card {
        padding: 16px;
        border-radius: 24px;
      }
    }
  </style>
@endpush

@section('content')
  <main class="phone register-page">
    <div class="register-shell">
      @include('frontend.partials.topbar', [
        'headerClass' => 'topbar',
      ])

      <section class="register-card" aria-labelledby="customer-register-title">
        <div class="register-brand-hero">
          @if (!empty($frontendLogoUrl))
            <img
              src="{{ $frontendLogoUrl }}"
              alt="{{ $frontendSiteName }}"
              loading="eager"
              decoding="async"
            >
          @else
            <div class="register-brand-fallback">
              <strong>{{ $frontendSiteName }}</strong>
              <span>Tạo tài khoản để mua sắm nhanh hơn cùng shop</span>
            </div>
          @endif
        </div>

        <div class="register-display">
          <h1 id="customer-register-title">Tạo tài khoản</h1>
       
        </div>

        @if (session('success'))
          <div class="register-alert register-alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('frontend.register.submit') }}">
          @csrf

          <div class="register-field">
            <label for="phone" class="register-label">Số điện thoại</label>
            <div class="register-input-wrap">
              <input
                id="phone"
                name="phone"
                type="tel"
                class="register-input @error('phone') is-invalid @enderror"
                placeholder="Nhập số điện thoại"
                value="{{ old('phone') }}"
                inputmode="tel"
                autocomplete="tel"
                required
              >
              <span class="register-input-icon">
                <i class="bi bi-telephone"></i>
              </span>
            </div>
            @error('phone')
              <span class="register-field-error">{{ $message }}</span>
            @enderror
          </div>

          <div class="register-field">
            <label for="email" class="register-label">Email</label>
            <div class="register-input-wrap">
              <input
                id="email"
                name="email"
                type="email"
                class="register-input @error('email') is-invalid @enderror"
                placeholder="Nhập email"
                value="{{ old('email') }}"
                autocomplete="email"
                required
              >
              <span class="register-input-icon">
                <i class="bi bi-envelope"></i>
              </span>
            </div>
            @error('email')
              <span class="register-field-error">{{ $message }}</span>
            @enderror
          </div>

          <div class="register-field">
            <label for="password" class="register-label">Mật khẩu</label>
            <div class="register-input-wrap">
              <input
                id="password"
                name="password"
                type="password"
                class="register-input @error('password') is-invalid @enderror"
                placeholder="Nhập mật khẩu"
                autocomplete="new-password"
                required
              >
              <button
                type="button"
                class="register-input-toggle"
                data-password-toggle
                data-target="#password"
                aria-label="Hiện mật khẩu"
                aria-pressed="false"
              >
                <i class="bi bi-eye"></i>
              </button>
            </div>
            @error('password')
              <span class="register-field-error">{{ $message }}</span>
            @enderror
          </div>

          <div class="register-field">
            <label for="password_confirmation" class="register-label">Xác nhận mật khẩu</label>
            <div class="register-input-wrap">
              <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                class="register-input @error('password') is-invalid @enderror"
                placeholder="Nhập lại mật khẩu"
                autocomplete="new-password"
                required
              >
              <button
                type="button"
                class="register-input-toggle"
                data-password-toggle
                data-target="#password_confirmation"
                aria-label="Hiện mật khẩu"
                aria-pressed="false"
              >
                <i class="bi bi-eye"></i>
              </button>
            </div>
            @error('password')
              <span class="register-field-error">{{ $message }}</span>
            @enderror
          </div>

          <button type="submit" class="register-submit">Tạo tài khoản</button>
        </form>

        <div class="register-footer">
          <strong>Đã có tài khoản?</strong>
          <a href="{{ route('frontend.login') }}" class="register-login-link">Đăng nhập</a>
        </div>
      </section>
    </div>
  </main>
@endsection

@push('scripts')
  <script>
    (() => {
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
