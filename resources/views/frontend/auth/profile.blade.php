@extends('frontend.layouts.app')

@section('title', 'Tài khoản khách hàng')
@section('meta_title', 'Tài khoản khách hàng')
@section('meta_description', 'Trang thông tin tài khoản khách hàng tại Shop Nội Y Buôn Hồ.')
@section('meta_robots', 'noindex,nofollow')

@php
  $tierClass = match ((string) ($customerProfile->tier ?? 'new')) {
      'diamond' => 'is-diamond',
      'vip' => 'is-vip',
      'loyal' => 'is-loyal',
      'friendly' => 'is-friendly',
      default => 'is-new',
  };
@endphp

@push('head')
  <style>
    .profile-page {
      min-height: 100vh;
      background: #ffffffff;
      color: #1b2322;
      font-family: 'Be Vietnam Pro', sans-serif;
      padding: 60px 0 36px;
       
       
    }

    .profile-shell {
      max-width: 430px;
      margin: 0 auto;
      /* padding: 0 12px; */
    }

    .profile-card {
      background: rgba(255, 255, 255, 0.95);
      /* border-radius: 30px; */
      /* box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08); */
      padding: 22px 18px 24px;
    }

    .profile-hero {
      text-align: center;
      padding-top: 6px;
    }

    .profile-avatar-wrap {
      position: relative;
      width: 108px;
      height: 108px;
      margin: 0 auto 18px;
      border-radius: 999px;
      padding: 4px;
      background: linear-gradient(135deg, #005147 0%, #8fd8cb 100%);
    }

    .profile-avatar {
      width: 100%;
      height: 100%;
      border-radius: 999px;
      background: #f2f7f5;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #005147;
      font-size: 1.72rem;
      letter-spacing: 0.04em;
      border: 4px solid #fff;
    }

    .profile-avatar img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 999px;
      display: block;
    }

    .profile-verified {
      position: absolute;
      right: 4px;
      bottom: 4px;
      width: 28px;
      height: 28px;
      border-radius: 999px;
      background: #005147;
      color: #fff;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border: 3px solid #fff;
      box-shadow: 0 10px 24px rgba(0, 81, 71, 0.24);
      cursor: pointer;
      padding: 0;
      outline: 0;
    }

    .profile-avatar-uploader {
      display: none;
    }

    .profile-name {
      margin: 0;
      font-size: 1.8rem;
      color: #17201f;
    }

    .profile-meta {
      margin: 8px 0 0;
      color: #53615d;
      font-size: 0.94rem;
    }

    .profile-tier {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      margin-top: 12px;
      padding: 7px 14px;
      border-radius: 999px;
      font-size: 0.86rem;
      background: #edf2f1;
      color: #314240;
    }

    .profile-tier.is-friendly { background: #e7f7ef; color: #17603a; }
    .profile-tier.is-loyal { background: #e7f2ff; color: #1f5fb6; }
    .profile-tier.is-vip { background: #f3ebff; color: #7046c9; }
    .profile-tier.is-diamond { background: #fff4db; color: #9a6700; }

    .profile-alert {
      margin: 0 0 12px;
      border-radius: 12px;
      padding: 10px 12px;
      font-size: 0.86rem;
    }

    .profile-alert--success {
      background: #dcfce7;
      color: #166534;
      border: 1px solid #bbf7d0;
    }

    .profile-alert--error {
      background: #fee2e2;
      color: #991b1b;
      border: 1px solid #fecaca;
    }

    .profile-stats {
      display: grid;
      grid-template-columns: 1fr;
      gap: 0;
      margin-top: 24px;
    }

    .profile-stat {
      min-height: 126px;
      border-radius: 22px;
      padding: 18px 18px 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(180deg, #f7f9f8 0%, #f1f5f3 100%);
      border: 1px solid rgba(0, 81, 71, 0.06);
      box-shadow: 0 12px 24px rgba(15, 23, 42, 0.05);
    }

    .profile-stat__label {
      margin: 0 0 10px;
      font-size: 1.05rem;
      font-weight: 700;
      letter-spacing: 0.04em;
      text-transform: uppercase;
      color: #0f2d29;
      line-height: 1.2;
    }

    .profile-stat__value {
      margin: 0;
      font-size: 2.35rem;
      line-height: 1;
      font-weight: 700;
      color: #005147;
    }

    .profile-stat__content {
      width: 100%;
      text-align: center;
    }

    .profile-menu {
      margin-top: 22px;
      border-radius: 24px;
      overflow: hidden;
      background: #fff;
      box-shadow: 0 16px 32px rgba(15, 23, 42, 0.06);
    }

    .profile-menu__item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      padding: 18px;
      color: #17201f;
      text-decoration: none;
      border-bottom: 1px solid #edf1ef;
    }

    .profile-menu__item:last-child {
      border-bottom: 0;
    }

    .profile-menu__item:hover {
      background: #f8faf8;
      color: #17201f;
    }

    .profile-menu__lead {
      display: flex;
      align-items: center;
      gap: 14px;
      min-width: 0;
    }

    .profile-menu__lead > i {
      color: #005147;
      font-size: 1.1rem;
      line-height: 1;
    }

    .profile-section {
      margin-top: 22px;
      background: #fff;
      border-radius: 24px;
      padding: 18px;
      box-shadow: 0 16px 32px rgba(15, 23, 42, 0.06);
    }

    .profile-section h3 {
      margin: 0 0 14px;
      font-size: 1rem;
      color: #17201f;
    }

    .profile-info-list {
      display: grid;
      gap: 12px;
    }

    .profile-info-row {
      display: flex;
      justify-content: space-between;
      gap: 12px;
      padding-bottom: 12px;
      border-bottom: 1px dashed #d7dfdc;
    }

    .profile-info-row:last-child {
      border-bottom: 0;
      padding-bottom: 0;
    }

    .profile-info-row span:first-child {
      color: #677571;
      font-size: 0.9rem;
    }

    .profile-info-row strong {
      text-align: right;
      color: #17201f;
    }

    .profile-info-form {
      display: flex;
      gap: 8px;
      margin-bottom: 12px;
      padding-bottom: 12px;
      border-bottom: 1px dashed #d7dfdc;
    }

    .profile-info-form input {
      flex: 1;
      min-width: 0;
      border: 1px solid #d7dfdc;
      border-radius: 12px;
      padding: 10px 12px;
      font-size: 0.92rem;
      color: #17201f;
    }

    .profile-info-form button {
      border: 0;
      border-radius: 12px;
      padding: 10px 14px;
      background: #005147;
      color: #fff;
      font-size: 0.85rem;
      font-weight: 600;
      white-space: nowrap;
    }

    .profile-address-form {
      display: grid;
      gap: 10px;
      margin-bottom: 12px;
      padding-bottom: 12px;
      border-bottom: 1px dashed #d7dfdc;
    }

    .profile-address-form select,
    .profile-address-form input {
      width: 100%;
      border: 1px solid #d7dfdc;
      border-radius: 12px;
      padding: 10px 12px;
      font-size: 0.92rem;
      color: #17201f;
      background: #fff;
    }

    .profile-address-form select {
      appearance: none;
      -webkit-appearance: none;
      -moz-appearance: none;
      padding-right: 40px;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 16 16'%3E%3Cpath d='M4.2 6.2 8 10l3.8-3.8' fill='none' stroke='%23005147' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 12px center;
      background-size: 14px 14px;
    }

    .profile-address-form button {
      border: 0;
      border-radius: 12px;
      padding: 10px 14px;
      background: #0b7a69;
      color: #fff;
      font-size: 0.85rem;
      font-weight: 600;
      justify-self: start;
    }

    .profile-logout {
      margin-top: 24px;
    }

    .profile-logout button {
      width: 100%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      border: 0;
      border-radius: 999px;
      padding: 15px 20px;
      background: #252c2c;
      color: #fff;
      font-size: 0.98rem;
    }
  </style>
@endpush

@section('content')
  <main class="phone profile-page">
    @include('frontend.partials.topbar', [
      'headerClass' => 'topbar',
    ])

    <section class="cart-subhead">
      <a href="{{ route('frontend.home') }}" class="cart-subhead-back" aria-label="Quay lại" data-history-back="true">
        <i class="bi bi-arrow-left"></i>
      </a>
      <h1>Tài khoản</h1>
      <span class="cart-subhead-spacer"></span>
    </section>

    <div class="profile-shell">

      <section class="profile-card">
        @if (session('success'))
          <div class="profile-alert profile-alert--success">{{ session('success') }}</div>
        @endif

        @if ($errors->has('avatar') || $errors->has('full_name') || $errors->has('province') || $errors->has('district') || $errors->has('ward') || $errors->has('address_line'))
          <div class="profile-alert profile-alert--error">{{ $errors->first('avatar') ?: $errors->first('full_name') ?: $errors->first('province') ?: $errors->first('district') ?: $errors->first('ward') ?: $errors->first('address_line') }}</div>
        @endif

        <section class="profile-hero">
          <div class="profile-avatar-wrap">
            <div class="profile-avatar">
              @if (!empty($customerUser->avatar_url))
                <img
                  src="{{ str_starts_with((string) $customerUser->avatar_url, 'http://') || str_starts_with((string) $customerUser->avatar_url, 'https://') ? $customerUser->avatar_url : asset(ltrim((string) $customerUser->avatar_url, '/')) }}"
                  alt="Avatar khách hàng"
                  loading="lazy"
                  decoding="async"
                >
              @elseif (!empty($frontendLogoUrl))
                <img src="{{ $frontendLogoUrl }}" alt="Logo website" loading="lazy" decoding="async">
              @else
                {{ $customerInitials }}
              @endif
            </div>
            <form method="POST" action="{{ route('frontend.profile.avatar.update') }}" enctype="multipart/form-data">
              @csrf
              <input
                id="profile-avatar-upload"
                class="profile-avatar-uploader"
                type="file"
                name="avatar"
                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                onchange="if (this.files && this.files.length) { this.form.submit(); }"
              >
              <button
                type="button"
                class="profile-verified"
                aria-label="Đổi ảnh đại diện"
                onclick="document.getElementById('profile-avatar-upload').click();"
              >
                <i class="bi bi-camera-fill"></i>
              </button>
            </form>
          </div>
          <h2 class="profile-name">{{ $customerDisplayName }}</h2>
          <p class="profile-meta">  Tham gia ngày {{ $customerJoinedLabel }}</p>
          <span class="profile-tier {{ $tierClass }}">
            <i class="bi bi-stars"></i>
            {{ $customerTierLabel }}
          </span>
        </section>

        <section class="profile-stats">
          <article class="profile-stat">
            <div class="profile-stat__content">
              <p class="profile-stat__label">Tổng chi tiêu</p>
              <p class="profile-stat__value">{{ number_format($customerTotalSpent, 0, ',', '.') }}đ</p>
            </div>
          </article>
        </section>

        <section class="profile-menu">
       
           
          <a href="{{ route('frontend.password.change') }}" class="profile-menu__item">
            <div class="profile-menu__lead">
              <i class="bi bi-shield-lock"></i>
              <strong>Đổi mật khẩu</strong>
            </div>
            <i class="bi bi-chevron-right"></i>
          </a>
          <a href="{{ route('frontend.profile.orders') }}" class="profile-menu__item">
            <div class="profile-menu__lead">
              <i class="bi bi-receipt"></i>
              <strong>Lịch sử mua hàng</strong>
            </div>
            <i class="bi bi-chevron-right"></i>
          </a>
        </section>

        <section class="profile-section" id="profile-info">
          <h3>Thông tin cá nhân</h3>
          <div class="profile-info-list">
            <form method="POST" action="{{ route('frontend.profile.info.update') }}" class="profile-info-form">
              @csrf
              <input
                type="text"
                name="full_name"
                value="{{ old('full_name', $customerDisplayName) }}"
                placeholder="Nhập họ và tên"
                maxlength="150"
                required
              >
              <button type="submit">Lưu tên</button>
            </form>
            <form method="POST" action="{{ route('frontend.profile.address.update') }}" class="profile-address-form">
              @csrf
              <select name="province" data-profile-province required>
                <option value="">Chọn tỉnh / thành phố</option>
              </select>
              <select name="district" data-profile-district required disabled>
                <option value="">Chọn quận / huyện</option>
              </select>
              <select name="ward" data-profile-ward required disabled>
                <option value="">Chọn phường / xã</option>
              </select>
              <input
                type="text"
                name="address_line"
                value="{{ old('address_line', (string) ($customerDefaultAddress->address_line ?? '')) }}"
                placeholder="Số nhà, tên đường..."
                maxlength="255"
                required
              >
              <button type="submit">Lưu địa chỉ</button>
            </form>
            <div class="profile-info-row">
              <span>Họ và tên</span>
              <strong>{{ $customerDisplayName ?: '-' }}</strong>
            </div>
            <div class="profile-info-row">
              <span>Số điện thoại</span>
              <strong>{{ $customerUser->phone ?: '-' }}</strong>
            </div>
            <div class="profile-info-row">
              <span>Email</span>
              <strong>{{ $customerUser->email ?: '-' }}</strong>
            </div>
            <div class="profile-info-row">
              <span>Địa chỉ</span>
              <strong>{{ trim(implode(', ', array_filter([(string) ($customerDefaultAddress->address_line ?? ''), (string) ($customerDefaultAddress->ward ?? ''), (string) ($customerDefaultAddress->district ?? ''), (string) ($customerDefaultAddress->province ?? '')]))) ?: '-' }}</strong>
            </div>
            <div class="profile-info-row">
              <span>Ngày tham gia</span>
              <strong>{{ $customerJoinedLabel ?: '-' }}</strong>
            </div>
            <div class="profile-info-row">
              <span>Hạng thành viên</span>
              <strong>{{ $customerTierLabel ?: '-' }}</strong>
            </div>
            <div class="profile-info-row">
              <span>Trạng thái</span>
              <strong>{{ $customerUser->status === 'active' ? 'Đang hoạt động' : ucfirst((string) $customerUser->status) }}</strong>
            </div>
          </div>
        </section>

        <section class="profile-section" id="profile-spending">
          <h3>Thống kê mua sắm</h3>
          <div class="profile-info-list">
            <div class="profile-info-row">
              <span>Tổng đơn đã đặt</span>
              <strong>{{ number_format($customerPlacedOrders, 0, ',', '.') }}</strong>
            </div>
            <div class="profile-info-row">
              <span>Tổng đơn đã xác minh</span>
              <strong>{{ number_format($customerTotalOrders, 0, ',', '.') }}</strong>
            </div>
            <div class="profile-info-row">
              <span>Tổng chi tiêu</span>
              <strong>{{ number_format($customerTotalSpent, 0, ',', '.') }}đ</strong>
            </div>
            
          </div>
        </section>


        <form method="POST" action="{{ route('frontend.logout') }}" class="profile-logout">
          @csrf
          <button type="submit">
         
   ĐĂNG XUẤT
          </button>
        </form>
      </section>
    </div>
  </main>
@endsection

@push('scripts')
  <script>
    (() => {
      const provinceSelect = document.querySelector('[data-profile-province]');
      const districtSelect = document.querySelector('[data-profile-district]');
      const wardSelect = document.querySelector('[data-profile-ward]');

      if (!provinceSelect || !districtSelect || !wardSelect) {
        return;
      }

      const savedProvince = @json((string) old('province', (string) ($customerDefaultAddress->province ?? '')));
      const savedDistrict = @json((string) old('district', (string) ($customerDefaultAddress->district ?? '')));
      const savedWard = @json((string) old('ward', (string) ($customerDefaultAddress->ward ?? '')));
      let addressData = [];

      const resetSelect = (select, placeholder) => {
        select.innerHTML = `<option value="">${placeholder}</option>`;
        select.disabled = true;
      };

      const fillSelect = (select, list, placeholder) => {
        const options = [`<option value="">${placeholder}</option>`];
        list.forEach((item) => {
          const name = String(item.name || '').trim();
          if (!name) {
            return;
          }
          options.push(`<option value="${name.replace(/"/g, '&quot;')}">${name}</option>`);
        });
        select.innerHTML = options.join('');
        select.disabled = list.length === 0;
      };

      const selectedProvinceObj = () => addressData.find((p) => String(p.name || '').trim() === String(provinceSelect.value || '').trim()) || null;
      const selectedDistrictObj = () => {
        const province = selectedProvinceObj();
        return (province?.districts || []).find((d) => String(d.name || '').trim() === String(districtSelect.value || '').trim()) || null;
      };

      provinceSelect.addEventListener('change', () => {
        const province = selectedProvinceObj();
        fillSelect(districtSelect, province?.districts || [], 'Chọn quận / huyện');
        resetSelect(wardSelect, 'Chọn phường / xã');
      });

      districtSelect.addEventListener('change', () => {
        const district = selectedDistrictObj();
        fillSelect(wardSelect, district?.wards || [], 'Chọn phường / xã');
      });

      const restoreSavedAddress = () => {
        if (savedProvince !== '') {
          provinceSelect.value = savedProvince;
        }
        const province = selectedProvinceObj();
        fillSelect(districtSelect, province?.districts || [], 'Chọn quận / huyện');

        if (savedDistrict !== '') {
          districtSelect.value = savedDistrict;
        }
        const district = selectedDistrictObj();
        fillSelect(wardSelect, district?.wards || [], 'Chọn phường / xã');

        if (savedWard !== '') {
          wardSelect.value = savedWard;
        }
      };

      (async () => {
        try {
          const response = await fetch(@json(url('/frontend/vn-addresses.json')), {
            headers: { 'Accept': 'application/json' },
          });
          if (!response.ok) {
            throw new Error('load address failed');
          }

          addressData = await response.json();
          addressData = [...addressData].sort((a, b) => {
            const nameA = String(a?.name || '').trim();
            const nameB = String(b?.name || '').trim();
            return nameA.localeCompare(nameB, 'vi', { sensitivity: 'base', numeric: true });
          });
          fillSelect(provinceSelect, addressData, 'Chọn tỉnh / thành phố');
          restoreSavedAddress();
        } catch (error) {
          resetSelect(provinceSelect, 'Không tải được tỉnh / thành phố');
          resetSelect(districtSelect, 'Không tải được quận / huyện');
          resetSelect(wardSelect, 'Không tải được phường / xã');
        }
      })();
    })();
  </script>
@endpush
