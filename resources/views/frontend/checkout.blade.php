@extends('frontend.layouts.app')

@section('title', 'Thanh toán')

@section('content')
@php
  $paymentSettings = $frontendPaymentSettings ?? [];
  $vietqrExpireMinutes = max(1, (int) ($paymentSettings['vietqr_expire_minutes'] ?? 30));
  $isCodEnabled = (bool) ($paymentSettings['cod_enabled'] ?? true);
  $isVietqrEnabled = (bool) ($paymentSettings['vietqr_enabled'] ?? false);
  $codTitle = trim((string) ($paymentSettings['cod_title'] ?? '')) ?: 'Thanh toán khi nhận hàng';
  $codDescription = trim((string) ($paymentSettings['cod_description'] ?? ''));
  $vietqrTitle = trim((string) ($paymentSettings['vietqr_title'] ?? '')) ?: 'Chuyển khoản VietQR';
  $vietqrDescription = trim((string) ($paymentSettings['vietqr_description'] ?? '')) ?: 'Quét mã QR ngân hàng để chuyển khoản.';
  $defaultPaymentMethod = trim((string) ($paymentSettings['default_method'] ?? 'cod'));
  $availablePaymentMethods = collect([
    $isCodEnabled ? 'cod' : null,
    $isVietqrEnabled ? 'vietqr' : null,
  ])->filter()->values();
  $defaultPaymentMethod = $availablePaymentMethods->contains($defaultPaymentMethod)
    ? $defaultPaymentMethod
    : ($availablePaymentMethods->first() ?? 'cod');
  $paymentCheckoutNote = trim((string) ($paymentSettings['checkout_note'] ?? ''));
@endphp
<main class="phone checkout-phone">
  <header class="cart-topbar">
    <a href="{{ route('frontend.home') }}" class="logo">{{ $frontendLogoPrimary }}@if ($frontendLogoAccent) <span>{{ $frontendLogoAccent }}</span>@endif</a>
    <div class="actions">
      <i class="bi bi-search"></i>
      <a href="{{ route('frontend.cart') }}" class="bell-wrap" aria-label="Mở giỏ hàng"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"></path></svg></a>
      <i class="bi bi-person-circle"></i>
    </div>
  </header>

  <section class="cart-subhead">
    <a href="{{ route('frontend.cart') }}" class="cart-subhead-back" aria-label="Quay lại" data-history-back="true">
      <i class="bi bi-arrow-left"></i>
    </a>
    <h1>Xác nhận đặt hàng</h1>
    <span class="cart-subhead-spacer"></span>
  </section>

  <section class="checkout-page-panel">
    <section class="checkout-page">
      <p class="checkout-customer-note">
        - Vui lòng nhập đúng SĐT để gọi nhận hàng.<br>
        - Mail không bắt buộc nhưng nếu nhập đúng mail bạn sẽ nhận được mail theo dõi đơn hàng.
      </p>

      <div class="checkout-tabs">
        <button class="checkout-tab active" type="button" data-mode="delivery">Giao tận nơi</button>
        <button class="checkout-tab" type="button" data-mode="pickup">Nhận tại cửa hàng</button>
      </div>

      <div class="checkout-gender">
        <label><input type="radio" name="gender" value="male" checked /> <span>Nam</span></label>
        <label><input type="radio" name="gender" value="female" /> <span>Nữ</span></label>
      </div>

      <div class="checkout-row two-cols">
        <input type="text" name="customer_name" value="" placeholder="Nhập tên khách hàng" data-checkout-name autocomplete="name" maxlength="150" />
        <input type="tel" name="customer_phone" value="" placeholder="Nhập số điện thoại" data-checkout-phone inputmode="tel" autocomplete="tel" maxlength="15" />
      </div>

      <div class="checkout-row">
        <input type="email" name="customer_email" value="" placeholder="Nhập email để có thể theo dõi đơn hàng" data-checkout-email autocomplete="email" maxlength="190" />
      </div>

      <div class="checkout-row checkout-delivery-row">
        <select class="checkout-select" name="address_level1" data-checkout-province autocomplete="address-level1">
          <option value="">Chọn tỉnh / thành phố</option>
        </select>
      </div>

      <div class="checkout-row checkout-delivery-row">
        <select class="checkout-select" name="address_level2" data-checkout-district autocomplete="address-level2" disabled>
          <option value="">Chọn quận / huyện</option>
        </select>
      </div>

      <div class="checkout-row checkout-delivery-row">
        <select class="checkout-select" name="address_level3" data-checkout-ward disabled>
          <option value="">Chọn phường / xã</option>
        </select>
      </div>

      <div class="checkout-row checkout-delivery-row">
        <input type="text" name="street_address" value="" placeholder="Số nhà, tên đường..." data-checkout-address-line autocomplete="street-address" maxlength="255" />
      </div>

      <div class="checkout-row checkout-pickup-row" hidden>
        <select class="checkout-select" name="store_id" data-checkout-store-id>
          <option value="">Chọn cửa hàng nhận hàng</option>
          @foreach ($stores as $store)
            <option value="{{ $store->id }}">{{ $store->name }} - {{ $store->address_line }}, {{ $store->district }}, {{ $store->province }}</option>
          @endforeach
        </select>
      </div>

      <div class="checkout-row">
        <textarea name="order_note" placeholder="Ghi chú thêm (nếu có)" data-checkout-note autocomplete="off" maxlength="500"></textarea>
      </div>

      <section class="checkout-payment-block">
        <div class="checkout-order-title">
          
          Hình thức thanh toán
        </div>

        <div class="checkout-payment-list" role="radiogroup" aria-label="Hình thức thanh toán">
          @if ($isCodEnabled)
          <label class="checkout-payment-card is-selected" data-payment-card>
            <input type="radio" name="payment_method" value="cod" {{ $defaultPaymentMethod === 'cod' ? 'checked' : '' }} data-payment-method />
            <span class="checkout-payment-radio" aria-hidden="true"></span>
            <span class="checkout-payment-media checkout-payment-media-cod">
              <img src="{{ asset('codpay.avif') }}" alt="COD" loading="lazy" />
            </span>
            <span class="checkout-payment-copy">
              <strong>{{ $codTitle }}</strong>
              @if ($codDescription !== '')
                <small>{{ $codDescription }}</small>
              @endif
            </span>
          </label>
          @endif

          @if ($isVietqrEnabled)
          <label class="checkout-payment-card" data-payment-card>
            <input type="radio" name="payment_method" value="vietqr" {{ $defaultPaymentMethod === 'vietqr' ? 'checked' : '' }} data-payment-method />
            <span class="checkout-payment-radio" aria-hidden="true"></span>
            <span class="checkout-payment-media">
              <img src="{{ asset('vnpay.avif') }}" alt="VietQR" loading="lazy" />
            </span>
            <span class="checkout-payment-copy">
              <strong>{{ $vietqrTitle }}</strong>
              @if ($vietqrDescription !== '')
                <small>{{ $vietqrDescription }}</small>
              @endif
            </span>
          </label>
          @endif
        </div>
        @if ($paymentCheckoutNote !== '')
          <p class="checkout-payment-note">{{ $paymentCheckoutNote }}</p>
        @endif
      </section>
  
      <div class="checkout-order-title">
     
        Chi tiết đơn hàng
      </div>
      <section class="checkout-order-block">
        <div data-checkout-items></div>
      </section>
    </section>
  </section>

  <section class="checkout-summary-bar">
    <div class="checkout-summary-total">
      <span>Tổng cộng</span>
      <strong data-checkout-subtotal>0đ</strong>
    </div>
    <a href="#" class="checkout-order-btn" data-checkout-submit>Đặt hàng</a>
  </section>

  <div class="checkout-popup" data-checkout-popup hidden>
    <div class="checkout-popup__backdrop" data-checkout-popup-close></div>
    <div class="checkout-popup__dialog" role="dialog" aria-modal="true" aria-labelledby="checkout-popup-title">
      <div class="checkout-popup__icon" aria-hidden="true">
        <i class="bi bi-info-circle"></i>
      </div>
      <h2 id="checkout-popup-title" data-checkout-popup-title>Thông báo</h2>
      <p data-checkout-popup-message></p>
      <div class="checkout-popup__actions">
        <button type="button" class="checkout-popup__button" data-checkout-popup-ok>Đã hiểu</button>
      </div>
    </div>
  </div>
</main>
@endsection

@push('head')
<style>
  .checkout-customer-note {
    margin: 0 0 14px;
    padding: 10px 14px;
    border-radius: 14px;
    background: #fff7e8;
    border: 1px solid #f2d7a4;
    color: #8a5a12;
    font-size: 13px;
    line-height: 1.5;
  }

  .checkout-popup {
    position: fixed;
    inset: 0;
    z-index: 1400;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
  }

  .checkout-popup[hidden] {
    display: none !important;
  }

  .checkout-popup__backdrop {
    position: absolute;
    inset: 0;
    background: rgba(12, 24, 44, 0.46);
    backdrop-filter: blur(2px);
  }

  .checkout-popup__dialog {
    position: relative;
    width: min(100%, 360px);
    padding: 24px 20px 18px;
    border-radius: 24px;
    background: #fff;
    text-align: center;
    box-shadow: 0 24px 50px rgba(10, 31, 58, 0.18);
  }

  .checkout-popup__icon {
    width: 56px;
    height: 56px;
    margin: 0 auto 14px;
    border-radius: 999px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #eaf2ff;
    color: #1f5d8d;
    font-size: 24px;
  }

  .checkout-popup__dialog h2 {
    margin: 0 0 10px;
    color: #163358;
    font-size: 20px;
  }

  .checkout-popup__dialog p {
    margin: 0;
    color: #4b5563;
    font-size: 14px;
    line-height: 1.6;
  }

  .checkout-popup__actions {
    margin-top: 18px;
    display: flex;
    justify-content: center;
  }

  .checkout-popup__button {
    min-width: 132px;
    min-height: 44px;
    border: 0;
    border-radius: 999px;
    background: #163358;
    color: #fff;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
  }
</style>
@endpush

@push('scripts')
<script>
  (() => {
    const defaultPaymentMethod = @json($defaultPaymentMethod);
    const cartUrl = @json(route('frontend.cart'));
    const checkoutStorageKey = 'shopnoiy:checkout-profile:v1';
    const vietqrResumeStorageKey = 'shopnoiy:vietqr-pending:v1';
    const vietqrExpireMinutes = @json($vietqrExpireMinutes);
    const checkoutTabs = document.querySelectorAll('.checkout-tab');
    const deliveryRows = document.querySelectorAll('.checkout-delivery-row');
    const pickupRows = document.querySelectorAll('.checkout-pickup-row');
    const checkoutItems = document.querySelector('[data-checkout-items]');
    const checkoutSubtotal = document.querySelector('[data-checkout-subtotal]');
    const checkoutSubmit = document.querySelector('[data-checkout-submit]');
    const checkoutName = document.querySelector('[data-checkout-name]');
    const checkoutPhone = document.querySelector('[data-checkout-phone]');
    const checkoutEmail = document.querySelector('[data-checkout-email]');
    const checkoutProvince = document.querySelector('[data-checkout-province]');
    const checkoutDistrict = document.querySelector('[data-checkout-district]');
    const checkoutWard = document.querySelector('[data-checkout-ward]');
    const checkoutAddressLine = document.querySelector('[data-checkout-address-line]');
    const checkoutNote = document.querySelector('[data-checkout-note]');
    const checkoutStoreSelect = document.querySelector('[data-checkout-store-id]');
    const checkoutGenderInputs = document.querySelectorAll('input[name="gender"]');
    const checkoutPaymentInputs = document.querySelectorAll('[data-payment-method]');
    const checkoutPaymentCards = document.querySelectorAll('[data-payment-card]');
    const checkoutPopup = document.querySelector('[data-checkout-popup]');
    const checkoutPopupTitle = document.querySelector('[data-checkout-popup-title]');
    const checkoutPopupMessage = document.querySelector('[data-checkout-popup-message]');
    const checkoutPopupOk = document.querySelector('[data-checkout-popup-ok]');
    let checkoutAddressData = [];
    let restoredCheckoutProfile = null;

    checkoutTabs.forEach((tab) => {
      tab.addEventListener('click', () => {
        applyDeliveryMode(tab.dataset.mode);
        saveCheckoutProfile();
      });
    });

    if (!checkoutItems || !window.ShopNoiyCart) {
      return;
    }

    const hidePopup = () => {
      if (!checkoutPopup) {
        return;
      }

      checkoutPopup.hidden = true;
      document.body.style.overflow = '';
    };

    const showPopup = ({ title = 'Thông báo', message = '', onClose = null } = {}) => new Promise((resolve) => {
      if (!checkoutPopup || !checkoutPopupTitle || !checkoutPopupMessage || !checkoutPopupOk) {
        resolve();
        return;
      }

      checkoutPopupTitle.textContent = title;
      checkoutPopupMessage.textContent = message;
      checkoutPopup.hidden = false;
      document.body.style.overflow = 'hidden';

      const closeElements = checkoutPopup.querySelectorAll('[data-checkout-popup-close]');

      const finalize = () => {
        checkoutPopupOk.removeEventListener('click', handleOk);
        closeElements.forEach((element) => {
          element.removeEventListener('click', handleClose);
        });
        hidePopup();
        if (typeof onClose === 'function') {
          onClose();
        }
        resolve();
      };

      const handleOk = () => {
        finalize();
      };

      const handleClose = () => {
        finalize();
      };

      checkoutPopupOk.addEventListener('click', handleOk);
      closeElements.forEach((element) => {
        element.addEventListener('click', handleClose);
      });
    });

    const getCheckoutProfile = () => {
      try {
        const payload = JSON.parse(localStorage.getItem(checkoutStorageKey) || '{}');
        return payload && typeof payload === 'object' ? payload : {};
      } catch (error) {
        return {};
      }
    };

    const formatMoney = (value) => `${new Intl.NumberFormat('vi-VN').format(Number(value) || 0)}đ`;
    const escapeHtml = (value) => String(value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');

    const normalizeVietnamPhone = (value) => {
      let phone = String(value || '').trim().replace(/[^\d+]/g, '');

      if (phone.startsWith('+84')) {
        phone = `0${phone.slice(3)}`;
      } else if (phone.startsWith('84')) {
        phone = `0${phone.slice(2)}`;
      }

      return phone;
    };

    const redirectToCartIfEmpty = () => {
      const items = window.ShopNoiyCart.getItems();

      if (Array.isArray(items) && items.length > 0) {
        return false;
      }

      window.location.replace(cartUrl);
      return true;
    };

    const saveCheckoutProfile = () => {
      const activeTab = document.querySelector('.checkout-tab.active');
      const genderInput = document.querySelector('input[name="gender"]:checked');

      const payload = {
        delivery_mode: activeTab?.dataset.mode === 'pickup' ? 'pickup' : 'delivery',
        gender: genderInput?.value || '',
        customer_name: checkoutName?.value?.trim().replace(/\s+/g, ' ') || '',
        customer_phone: normalizeVietnamPhone(checkoutPhone?.value || ''),
        customer_email: checkoutEmail?.value?.trim() || '',
        province_code: checkoutProvince?.value || '',
        district_code: checkoutDistrict?.value || '',
        ward_code: checkoutWard?.value || '',
        address_line: checkoutAddressLine?.value?.trim().replace(/\s+/g, ' ') || '',
        store_id: checkoutStoreSelect?.value || '',
        payment_method: document.querySelector('[data-payment-method]:checked')?.value || 'cod',
        note: checkoutNote?.value?.trim() || '',
      };

      localStorage.setItem(checkoutStorageKey, JSON.stringify(payload));
    };

    const syncPaymentCards = () => {
      checkoutPaymentCards.forEach((card) => {
        const input = card.querySelector('[data-payment-method]');
        card.classList.toggle('is-selected', Boolean(input?.checked));
      });
    };

    const applyDeliveryMode = (mode) => {
      const normalizedMode = mode === 'pickup' ? 'pickup' : 'delivery';
      const isPickup = normalizedMode === 'pickup';

      checkoutTabs.forEach((item) => {
        item.classList.toggle('active', item.dataset.mode === normalizedMode);
      });

      deliveryRows.forEach((row) => {
        row.hidden = isPickup;
      });
      pickupRows.forEach((row) => {
        row.hidden = !isPickup;
      });
    };

    const isValidVietnamMobile = (value) => /^0(3|5|7|8|9)\d{8}$/.test(value);
    const isDetailedAddress = (value) => String(value || '').trim().length >= 6;
    const resetSelectOptions = (select, placeholder) => {
      if (!select) {
        return;
      }

      select.innerHTML = `<option value="">${placeholder}</option>`;
      select.value = '';
      select.disabled = true;
    };

    const fillSelectOptions = (select, items, placeholder) => {
      if (!select) {
        return;
      }

      select.innerHTML = [
        `<option value="">${placeholder}</option>`,
        ...items.map((item) => `<option value="${escapeHtml(item.code)}">${escapeHtml(item.name)}</option>`),
      ].join('');
      select.disabled = items.length === 0;
    };

    const getSelectedProvince = () => checkoutAddressData.find((item) => String(item.code) === String(checkoutProvince?.value || '')) || null;
    const getSelectedDistrict = () => {
      const province = getSelectedProvince();
      return province?.districts?.find((item) => String(item.code) === String(checkoutDistrict?.value || '')) || null;
    };
    const getSelectedWard = () => {
      const district = getSelectedDistrict();
      return district?.wards?.find((item) => String(item.code) === String(checkoutWard?.value || '')) || null;
    };
    const composeShippingAddress = () => {
      const addressLine = checkoutAddressLine?.value?.trim().replace(/\s+/g, ' ') || '';
      const wardName = getSelectedWard()?.name || '';
      const districtName = getSelectedDistrict()?.name || '';
      const provinceName = getSelectedProvince()?.name || '';

      return [addressLine, wardName, districtName, provinceName].filter(Boolean).join(', ');
    };

    const restoreCheckoutProfile = () => {
      const savedProfile = getCheckoutProfile();
      restoredCheckoutProfile = savedProfile;

      if (checkoutName && savedProfile.customer_name) {
        checkoutName.value = savedProfile.customer_name;
      }

      if (checkoutPhone && savedProfile.customer_phone) {
        checkoutPhone.value = normalizeVietnamPhone(savedProfile.customer_phone);
      }

      if (checkoutEmail && savedProfile.customer_email) {
        checkoutEmail.value = String(savedProfile.customer_email).trim();
      }

      if (checkoutAddressLine && savedProfile.address_line) {
        checkoutAddressLine.value = savedProfile.address_line;
      }

      if (checkoutNote && savedProfile.note) {
        checkoutNote.value = savedProfile.note;
      }

      if (checkoutStoreSelect && savedProfile.store_id) {
        checkoutStoreSelect.value = String(savedProfile.store_id);
      }

      const availablePaymentMethods = Array.from(checkoutPaymentInputs).map((input) => input.value);
      const savedPaymentMethod = String(savedProfile.payment_method || '').trim();
      const activePaymentMethod = availablePaymentMethods.includes(savedPaymentMethod)
        ? savedPaymentMethod
        : (availablePaymentMethods.includes(defaultPaymentMethod) ? defaultPaymentMethod : (availablePaymentMethods[0] || ''));
      if (checkoutPaymentInputs.length) {
        checkoutPaymentInputs.forEach((input) => {
          input.checked = input.value === activePaymentMethod;
        });
      }
      syncPaymentCards();

      applyDeliveryMode(savedProfile.delivery_mode);

      const savedGender = String(savedProfile.gender || '').trim();
      if (savedGender !== '' && checkoutGenderInputs.length) {
        checkoutGenderInputs.forEach((input) => {
          input.checked = input.value === savedGender;
        });
      }
    };

    const restoreAddressSelections = () => {
      if (!restoredCheckoutProfile) {
        return;
      }

      const provinceCode = String(restoredCheckoutProfile.province_code || '');
      const districtCode = String(restoredCheckoutProfile.district_code || '');
      const wardCode = String(restoredCheckoutProfile.ward_code || '');

      if (checkoutProvince && provinceCode !== '') {
        checkoutProvince.value = provinceCode;
      }

      const province = getSelectedProvince();
      fillSelectOptions(checkoutDistrict, province?.districts || [], 'Chọn quận / huyện');

      if (checkoutDistrict && districtCode !== '') {
        checkoutDistrict.value = districtCode;
      }

      const district = getSelectedDistrict();
      fillSelectOptions(checkoutWard, district?.wards || [], 'Chọn phường / xã');

      if (checkoutWard && wardCode !== '') {
        checkoutWard.value = wardCode;
      }
    };

    const loadAddressData = async () => {
      if (!checkoutProvince || !checkoutDistrict || !checkoutWard) {
        return;
      }

      try {
        const response = await fetch(@json(url('/frontend/vn-addresses.json')), {
          headers: {
            'Accept': 'application/json',
          },
        });

        if (!response.ok) {
          throw new Error('Không thể tải dữ liệu địa chỉ.');
        }

        checkoutAddressData = await response.json();
        fillSelectOptions(checkoutProvince, checkoutAddressData, 'Chọn tỉnh / thành phố');
        restoreAddressSelections();
      } catch (error) {
        resetSelectOptions(checkoutProvince, 'Không tải được tỉnh / thành phố');
      }
    };

    const renderCheckoutItems = () => {
      const items = window.ShopNoiyCart.getItems();
      const subtotal = items.reduce((sum, item) => sum + ((Number(item.price) || 0) * (Number(item.qty) || 0)), 0);

      if (!items.length) {
        redirectToCartIfEmpty();
        checkoutItems.innerHTML = `
          <article class="checkout-item">
            <div class="checkout-item-info">
              <h2>Chưa có sản phẩm trong giỏ</h2>
            </div>
          </article>
        `;

        if (checkoutSubtotal) {
          checkoutSubtotal.textContent = formatMoney(0);
        }

        if (checkoutSubmit) {
          checkoutSubmit.classList.add('is-disabled');
          checkoutSubmit.setAttribute('aria-disabled', 'true');
        }

        return;
      }

      checkoutItems.innerHTML = items.map((item) => `
        <article class="checkout-item">
          <div class="checkout-item-thumb">
            <img src="${escapeHtml(item.image_url || '')}" alt="${escapeHtml(item.name || '')}" loading="lazy" decoding="async" />
          </div>
          <div class="checkout-item-info">
            <h2>${escapeHtml(item.name || '')}</h2>
            <p>${escapeHtml([item.color, item.size].filter(Boolean).join(' | ') || '-')}</p>
            <div class="checkout-item-bottom">
              <span>Số lượng: ${Number(item.qty) || 1}</span>
              <strong>${formatMoney(item.price)}</strong>
            </div>
          </div>
        </article>
      `).join('');

      if (checkoutSubtotal) {
        checkoutSubtotal.textContent = formatMoney(subtotal);
      }

      if (checkoutSubmit) {
        checkoutSubmit.classList.remove('is-disabled');
        checkoutSubmit.setAttribute('aria-disabled', 'false');
      }
    };

    checkoutPhone?.addEventListener('blur', () => {
      checkoutPhone.value = normalizeVietnamPhone(checkoutPhone.value);
      saveCheckoutProfile();
    });

    checkoutAddressLine?.addEventListener('blur', () => {
      checkoutAddressLine.value = checkoutAddressLine.value.trim().replace(/\s+/g, ' ');
      saveCheckoutProfile();
    });

    checkoutProvince?.addEventListener('change', () => {
      const province = getSelectedProvince();
      fillSelectOptions(checkoutDistrict, province?.districts || [], 'Chọn quận / huyện');
      resetSelectOptions(checkoutWard, 'Chọn phường / xã');
      saveCheckoutProfile();
    });

    checkoutDistrict?.addEventListener('change', () => {
      const district = getSelectedDistrict();
      fillSelectOptions(checkoutWard, district?.wards || [], 'Chọn phường / xã');
      saveCheckoutProfile();
    });

    checkoutWard?.addEventListener('change', saveCheckoutProfile);
    checkoutStoreSelect?.addEventListener('change', saveCheckoutProfile);
    checkoutNote?.addEventListener('input', saveCheckoutProfile);
    checkoutName?.addEventListener('input', saveCheckoutProfile);
    checkoutPhone?.addEventListener('input', saveCheckoutProfile);
    checkoutEmail?.addEventListener('input', saveCheckoutProfile);
    checkoutAddressLine?.addEventListener('input', saveCheckoutProfile);
    checkoutGenderInputs.forEach((input) => {
      input.addEventListener('change', saveCheckoutProfile);
    });
    checkoutPaymentInputs.forEach((input) => {
      input.addEventListener('change', () => {
        syncPaymentCards();
        saveCheckoutProfile();
      });
    });

    const trackCheckoutActivity = (activityLabel) => {
      window.ShopNoiyVisitorTracking?.update({
        activity_label: activityLabel,
        meta: {
          customer_name: checkoutName?.value?.trim()?.replace(/\s+/g, ' ') || '',
          customer_phone: normalizeVietnamPhone(checkoutPhone?.value || ''),
          customer_email: checkoutEmail?.value?.trim() || '',
        }
      });
    };

    let checkoutTrackTimer = null;
    const scheduleCheckoutTracking = (activityLabel, delay = 1500) => {
      if (checkoutTrackTimer) {
        window.clearTimeout(checkoutTrackTimer);
      }

      checkoutTrackTimer = window.setTimeout(() => {
        trackCheckoutActivity(activityLabel);
      }, delay);
    };

    [
      checkoutName,
      checkoutPhone,
      checkoutEmail,
      checkoutAddressLine,
      checkoutNote,
    ].forEach((field) => {
      field?.addEventListener('input', () => scheduleCheckoutTracking('Đang nhập thông tin thanh toán'));
      field?.addEventListener('blur', () => trackCheckoutActivity('Đang nhập thông tin thanh toán'));
    });

    [
      checkoutProvince,
      checkoutDistrict,
      checkoutWard,
      checkoutStoreSelect,
    ].forEach((field) => {
      field?.addEventListener('change', () => trackCheckoutActivity('Đang nhập thông tin thanh toán'));
    });

    restoreCheckoutProfile();
    syncPaymentCards();
    trackCheckoutActivity('Đang nhập thông tin thanh toán');

    if (checkoutSubmit) {
      checkoutSubmit.addEventListener('click', async (event) => {
        if (checkoutSubmit.getAttribute('aria-disabled') === 'true') {
          event.preventDefault();
          return;
        }

        event.preventDefault();

        if (redirectToCartIfEmpty()) {
          return;
        }

        const activeTab = document.querySelector('.checkout-tab.active');
        const deliveryType = activeTab?.dataset.mode === 'pickup' ? 'pickup' : 'delivery';
        const customerName = checkoutName?.value?.trim().replace(/\s+/g, ' ') || '';
        const customerPhone = normalizeVietnamPhone(checkoutPhone?.value || '');
        const customerEmail = checkoutEmail?.value?.trim() || '';
        const addressLine = checkoutAddressLine?.value?.trim().replace(/\s+/g, ' ') || '';
        const selectedProvince = getSelectedProvince();
        const selectedDistrict = getSelectedDistrict();
        const selectedWard = getSelectedWard();
        const shippingAddress = composeShippingAddress();
        const note = checkoutNote?.value?.trim() || '';
        const items = window.ShopNoiyCart.getItems();

        if (checkoutName) {
          checkoutName.value = customerName;
        }

        if (checkoutPhone) {
          checkoutPhone.value = customerPhone;
        }

        if (checkoutEmail) {
          checkoutEmail.value = customerEmail;
        }

        if (checkoutAddressLine) {
          checkoutAddressLine.value = addressLine;
        }

        saveCheckoutProfile();

        if (!customerName) {
          showPopup({ message: 'Vui lòng nhập tên khách hàng.' });
          checkoutName?.focus();
          return;
        }

        if (customerName.length < 2) {
          showPopup({ message: 'Họ tên cần có ít nhất 2 ký tự.' });
          checkoutName?.focus();
          return;
        }

        if (!customerPhone) {
          showPopup({ message: 'Vui lòng nhập số điện thoại.' });
          checkoutPhone?.focus();
          return;
        }

        if (!isValidVietnamMobile(customerPhone)) {
          showPopup({ message: 'Số điện thoại cần đúng định dạng di động Việt Nam, ví dụ 0901234567.' });
          checkoutPhone?.focus();
          return;
        }

        if (deliveryType === 'delivery' && !selectedProvince) {
          showPopup({ message: 'Vui lòng chọn tỉnh / thành phố.' });
          checkoutProvince?.focus();
          return;
        }

        if (deliveryType === 'delivery' && !selectedDistrict) {
          showPopup({ message: 'Vui lòng chọn quận / huyện.' });
          checkoutDistrict?.focus();
          return;
        }

        if (deliveryType === 'delivery' && !selectedWard) {
          showPopup({ message: 'Vui lòng chọn phường / xã.' });
          checkoutWard?.focus();
          return;
        }

        if (deliveryType === 'delivery' && !addressLine) {
          showPopup({ message: 'Vui lòng nhập số nhà, tên đường.' });
          checkoutAddressLine?.focus();
          return;
        }

        if (deliveryType === 'delivery' && !isDetailedAddress(addressLine)) {
          showPopup({ message: 'Phần số nhà, tên đường cần rõ hơn, tối thiểu 6 ký tự.' });
          checkoutAddressLine?.focus();
          return;
        }

        if (deliveryType === 'pickup' && !(checkoutStoreSelect?.value || '').trim()) {
          showPopup({ message: 'Vui lòng chọn cửa hàng nhận hàng.' });
          checkoutStoreSelect?.focus();
          return;
        }

        try {
          checkoutSubmit.classList.add('is-disabled');
          checkoutSubmit.setAttribute('aria-disabled', 'true');
          trackCheckoutActivity('Đang gửi đơn hàng');

          const response = await fetch(@json(route('frontend.place-order')), {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': @json(csrf_token()),
            },
            body: JSON.stringify({
              customer_name: customerName,
              customer_phone: customerPhone,
              customer_email: customerEmail || null,
              delivery_type: deliveryType,
              shipping_address_text: deliveryType === 'delivery' ? shippingAddress : null,
              store_id: deliveryType === 'pickup' ? (checkoutStoreSelect?.value || null) : null,
              payment_method: document.querySelector('[data-payment-method]:checked')?.value || 'cod',
              note,
              items: items.map((item) => ({
                product_id: item.product_id,
                qty: item.qty,
                color: item.color || '',
                size: item.size || '',
              })),
            })
          });

          const payload = await response.json();

          if (!response.ok) {
            if (response.status === 409 && payload.requires_existing_invoice && payload.existing_invoice?.redirect_url) {
              if (payload.existing_invoice?.status_url && payload.existing_invoice?.invoice_code && payload.existing_invoice?.expires_at) {
                localStorage.setItem(vietqrResumeStorageKey, JSON.stringify({
                  url: payload.existing_invoice.redirect_url,
                  status_url: payload.existing_invoice.status_url,
                  invoice_code: payload.existing_invoice.invoice_code,
                  expires_at: Number(payload.existing_invoice.expires_at) || (Date.now() + (Number(vietqrExpireMinutes || 30) * 60 * 1000))
                }));
              }

              showPopup({
                title: 'Đang có hóa đơn chờ',
                message: payload.message || 'Bạn đang có hóa đơn VietQR chưa hoàn thành.',
                onClose: () => {
                  window.location.href = payload.existing_invoice.redirect_url;
                }
              });
              return;
            }

            showPopup({ message: payload.message || 'Không thể đặt hàng lúc này.' });
            checkoutSubmit.classList.remove('is-disabled');
            checkoutSubmit.setAttribute('aria-disabled', 'false');
            return;
          }

          if ((document.querySelector('[data-payment-method]:checked')?.value || 'cod') === 'vietqr' && payload.redirect_url) {
            localStorage.setItem(vietqrResumeStorageKey, JSON.stringify({
              url: payload.redirect_url,
              status_url: payload.status_url || '',
              invoice_code: payload.invoice_code || '',
              expires_at: Date.now() + (Number(vietqrExpireMinutes || 30) * 60 * 1000)
            }));
          }

          if (payload.clear_cart) {
            window.ShopNoiyCart.clear();
          }
          window.location.href = payload.redirect_url;
        } catch (error) {
          checkoutSubmit.classList.remove('is-disabled');
          checkoutSubmit.setAttribute('aria-disabled', 'false');
          showPopup({ message: 'Có lỗi xảy ra khi đặt hàng. Vui lòng thử lại.' });
        }
      });
    }

    window.addEventListener('shopnoiy-cart-updated', renderCheckoutItems);
    loadAddressData();
    if (redirectToCartIfEmpty()) {
      return;
    }
    renderCheckoutItems();
  })();
</script>
@endpush
