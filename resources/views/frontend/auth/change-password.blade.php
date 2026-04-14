@extends('frontend.layouts.app')

@section('title', 'Đổi mật khẩu')
@section('meta_title', 'Đổi mật khẩu khách hàng')
@section('meta_description', 'Trang đổi mật khẩu tài khoản khách hàng tại Shop Nội Y Buôn Hồ.')
@section('meta_robots', 'noindex,nofollow')

@push('head')
  <style>
    body {
      color: #303330;
    }

    .change-password-page {
      min-height: 100vh;
      padding: 72px 16px 48px;
      background: #ffffff;
      font-family: 'Be Vietnam Pro', sans-serif;
    }

    .change-password-shell {
      width: min(100%, 460px);
      margin: 0 auto;
    }

    .change-password-page .topbar {
      margin-left: auto;
      margin-right: auto;
      margin-bottom: 12px;
    }

    .change-password-card {
      background: rgba(255, 255, 255, 0.92);
    }

    .change-password-display {
      margin-bottom: 22px;
      text-align: center;
      padding-top: 12px;
    }

    .change-password-display h1 {
      margin: 0 0 8px;
      font-size: clamp(1.8rem, 5vw, 2.2rem);
      color: #17201f;
      letter-spacing: -0.03em;
    }

    .change-password-display p {
      margin: 0;
      color: #667571;
      font-size: 0.95rem;
      line-height: 1.5;
    }

    .change-password-alert {
      margin-bottom: 16px;
      padding: 14px 16px;
      border-radius: 18px;
      font-size: 0.92rem;
    }

    .change-password-alert-success {
      color: #14532d;
      background: #dcfce7;
      border: 1px solid #bbf7d0;
    }

    .change-password-alert-error {
      color: #991b1b;
      background: #fee2e2;
      border: 1px solid #fecaca;
    }

    .change-password-form {
      display: grid;
      gap: 16px;
    }

    .change-password-field {
      display: grid;
      gap: 8px;
    }

    .change-password-label {
      font-size: 0.78rem;
      letter-spacing: 0.12em;
      color: #5b4632;
      text-transform: uppercase;
    }

    .change-password-input {
      width: 100%;
      border: 1px solid #d7dfdc;
      border-radius: 18px;
      padding: 15px 16px;
      background: #ffffff;
      color: #17201f;
      font-size: 0.96rem;
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .change-password-input:focus {
      outline: none;
      border-color: #005147;
      box-shadow: 0 0 0 3px rgba(0, 81, 71, 0.12);
    }

    .change-password-input-wrap {
      position: relative;
    }

    .change-password-input--password {
      padding-right: 52px;
    }

    .change-password-input-toggle {
      position: absolute;
      top: 50%;
      right: 10px;
      transform: translateY(-50%);
      width: 38px;
      height: 38px;
      border: 0;
      border-radius: 999px;
      background: transparent;
      color: #4d5d59;
      font-size: 1.18rem;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
    }

    .change-password-submit {
      width: 100%;
      border: 0;
      border-radius: 999px;
      padding: 15px 20px;
      background: linear-gradient(180deg, #005147 0%, #006b5e 100%);
      color: #fff;
      font-size: 0.92rem;
      font-weight: 700;
      letter-spacing: 0.18em;
      text-transform: uppercase;
      box-shadow: 0 16px 28px rgba(0, 81, 71, 0.2);
    }

    .change-password-submit:hover {
      filter: brightness(0.98);
    }

    .change-password-back {
      display: inline-flex;
      width: 100%;
      align-items: center;
      justify-content: center;
      margin-top: 12px;
      padding: 15px 20px;
      border: 1px solid #d7dfdc;
      border-radius: 999px;
      color: #17201f;
      text-decoration: none;
      background: #fff;
    }
  </style>
@endpush

@section('content')
  <main class="phone change-password-page">
    <div class="change-password-shell">
      @include('frontend.partials.topbar', [
        'headerClass' => 'topbar',
      ])

      <section class="change-password-card" aria-labelledby="customer-change-password-title">
        <div class="change-password-display">
          <h1 id="customer-change-password-title">Đổi mật khẩu</h1>
          <p>Cập nhật mật khẩu mới để bảo vệ tài khoản của bạn.</p>
        </div>

        @if (session('success'))
          <div class="change-password-alert change-password-alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
          <div class="change-password-alert change-password-alert-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('frontend.password.change.submit') }}" class="change-password-form">
          @csrf

          <div class="change-password-field">
            <label for="current_password" class="change-password-label">Mật khẩu hiện tại</label>
            <div class="change-password-input-wrap">
              <input
                id="current_password"
                name="current_password"
                type="password"
                class="change-password-input change-password-input--password"
                autocomplete="current-password"
                required
              >
              <button
                type="button"
                class="change-password-input-toggle"
                data-password-toggle
                data-target="#current_password"
                aria-label="Hiện mật khẩu"
                aria-pressed="false"
              >
                <i class="bi bi-eye"></i>
              </button>
            </div>
          </div>

          <div class="change-password-field">
            <label for="password" class="change-password-label">Mật khẩu mới</label>
            <div class="change-password-input-wrap">
              <input
                id="password"
                name="password"
                type="password"
                class="change-password-input change-password-input--password"
                autocomplete="new-password"
                required
              >
              <button
                type="button"
                class="change-password-input-toggle"
                data-password-toggle
                data-target="#password"
                aria-label="Hiện mật khẩu"
                aria-pressed="false"
              >
                <i class="bi bi-eye"></i>
              </button>
            </div>
          </div>

          <div class="change-password-field">
            <label for="password_confirmation" class="change-password-label">Xác nhận mật khẩu mới</label>
            <div class="change-password-input-wrap">
              <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                class="change-password-input change-password-input--password"
                autocomplete="new-password"
                required
              >
              <button
                type="button"
                class="change-password-input-toggle"
                data-password-toggle
                data-target="#password_confirmation"
                aria-label="Hiện mật khẩu"
                aria-pressed="false"
              >
                <i class="bi bi-eye"></i>
              </button>
            </div>
          </div>

          <button type="submit" class="change-password-submit">Đổi mật khẩu</button>
        </form>

        <a href="{{ route('frontend.profile') }}" class="change-password-back">Quay lại tài khoản</a>
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
