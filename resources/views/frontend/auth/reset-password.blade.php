@extends('frontend.layouts.app')

@section('title', 'Đặt lại mật khẩu')
@section('meta_title', 'Đặt lại mật khẩu khách hàng')
@section('meta_description', 'Đặt lại mật khẩu tài khoản khách hàng tại Shop Nội Y Buôn Hồ.')
@section('meta_robots', 'noindex,nofollow')

@push('head')
  <style>
    body {
      color: #303330;
    }

    .phone.reset-page,
    .reset-page {
      min-height: 100vh;
      padding: 72px 16px 48px;
      background: #ffffff;
      font-family: 'Be Vietnam Pro', sans-serif;
    }

    .reset-shell {
      width: min(100%, 460px);
      margin: 0 auto;
    }

    .reset-page .topbar {
      margin-left: auto;
      margin-right: auto;
      margin-bottom: 12px;
    }

    .reset-card {
      background: rgba(255, 255, 255, 0.88);
    }

    .reset-brand-hero {
      width: 100%;
      min-height: 140px;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 30px 24px 4px;
    }

    .reset-brand-hero img {
      max-width: min(100%, 240px);
      max-height: 120px;
      object-fit: contain;
      display: block;
    }

    .reset-brand-fallback {
      text-align: center;
      color: #303330;
    }

    .reset-brand-fallback strong {
      display: block;
      font-size: clamp(1.8rem, 5vw, 2.4rem);
      letter-spacing: -0.03em;
    }

    .reset-brand-fallback span {
      display: block;
      margin-top: 8px;
      font-size: 0.95rem;
      color: #6d6f6b;
    }

    .reset-display {
      margin-bottom: 22px;
      text-align: center;
    }

    .reset-display h1 {
      margin: 0 0 8px;
      font-size: clamp(1.8rem, 5vw, 2.2rem);
      color: #533823;
      letter-spacing: -0.03em;
    }

    .reset-display p {
      margin: 0;
      color: #6d6f6b;
      font-size: 0.95rem;
      line-height: 1.5;
    }

    .reset-alert {
      margin-bottom: 16px;
      padding: 14px 16px;
      border-radius: 18px;
      font-size: 0.92rem;
    }

    .reset-alert-error {
      color: #991b1b;
      background: #fee2e2;
      border: 1px solid #fecaca;
    }

    .reset-field {
      margin-bottom: 16px;
    }

    .reset-label {
      display: block;
      margin-bottom: 8px;
      font-size: 0.78rem;
      letter-spacing: 0.12em;
      color: #5b4632;
      text-transform: uppercase;
    }

    .reset-input-wrap {
      position: relative;
    }

    .reset-input {
      width: 100%;
      border: 1px solid #252c2c;
      border-radius: 999px;
      padding: 15px 52px 15px 18px;
      background: #ffffff;
      color: #2f241a;
      font-size: 0.96rem;
      transition: box-shadow 0.2s ease, transform 0.2s ease;
    }

    .reset-input:focus {
      outline: none;
      box-shadow: 0 0 0 3px rgba(37, 44, 44, 0.12);
      transform: translateY(-1px);
    }

    .reset-input.is-invalid {
      border-color: #dc2626;
      box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.12);
    }

    .reset-field-error {
      display: block;
      margin-top: 8px;
      padding-left: 6px;
      color: #b91c1c;
      font-size: 0.82rem;
      line-height: 1.4;
    }

    .reset-input-icon {
      position: absolute;
      top: 50%;
      right: 18px;
      transform: translateY(-50%);
      color: #8c8174;
      font-size: 1.15rem;
      pointer-events: none;
    }

    .reset-input-toggle {
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

    .reset-submit {
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

    .reset-submit:hover {
      background: #1d2323;
    }

    .reset-footer {
      margin-top: 22px;
      text-align: center;
      color: #6d6f6b;
      font-size: 0.92rem;
    }

    .reset-footer strong {
      display: block;
      margin-bottom: 6px;
      color: #2f241a;
    }

    .reset-login-link {
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

    .reset-login-link:hover {
      color: #252c2c;
      background: rgba(37, 44, 44, 0.04);
    }

    @media (max-width: 480px) {
      .reset-card {
        padding: 16px;
        border-radius: 24px;
      }
    }
  </style>
@endpush

@section('content')
  <main class="phone reset-page">
    <div class="reset-shell">
      @include('frontend.partials.topbar', [
        'headerClass' => 'topbar',
      ])

      <section class="reset-card" aria-labelledby="customer-reset-title">
        <div class="reset-brand-hero">
          @if (!empty($frontendLogoUrl))
            <img
              src="{{ $frontendLogoUrl }}"
              alt="{{ $frontendSiteName }}"
              loading="eager"
              decoding="async"
            >
          @else
            <div class="reset-brand-fallback">
              <strong>{{ $frontendSiteName }}</strong>
              <span>Đặt lại mật khẩu để tiếp tục mua sắm cùng shop</span>
            </div>
          @endif
        </div>

        <div class="reset-display">
          <h1 id="customer-reset-title">Đặt lại mật khẩu</h1>
          <p>{{ $email }}</p>
        </div>

        @if ($errors->any())
          <div class="reset-alert reset-alert-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('frontend.password.reset.submit') }}">
          @csrf
          <input type="hidden" name="token" value="{{ $token }}">
          <input type="hidden" name="email" value="{{ old('email', $email) }}">

          <div class="reset-field">
            <label for="password" class="reset-label">Mật khẩu mới</label>
            <div class="reset-input-wrap">
              <input
                id="password"
                name="password"
                type="password"
                class="reset-input @error('password') is-invalid @enderror"
                placeholder="Nhập mật khẩu mới"
                autocomplete="new-password"
                required
              >
              <button
                type="button"
                class="reset-input-toggle"
                data-password-toggle
                data-target="#password"
                aria-label="Hiện mật khẩu"
                aria-pressed="false"
              >
                <i class="bi bi-eye"></i>
              </button>
            </div>
            @error('password')
              <span class="reset-field-error">{{ $message }}</span>
            @enderror
          </div>

          <div class="reset-field">
            <label for="password_confirmation" class="reset-label">Xác nhận mật khẩu</label>
            <div class="reset-input-wrap">
              <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                class="reset-input @error('password') is-invalid @enderror"
                placeholder="Nhập lại mật khẩu mới"
                autocomplete="new-password"
                required
              >
              <button
                type="button"
                class="reset-input-toggle"
                data-password-toggle
                data-target="#password_confirmation"
                aria-label="Hiện mật khẩu"
                aria-pressed="false"
              >
                <i class="bi bi-eye"></i>
              </button>
            </div>
            @error('password')
              <span class="reset-field-error">{{ $message }}</span>
            @enderror
          </div>

          <button type="submit" class="reset-submit">Đặt lại mật khẩu</button>
        </form>

       
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
