@extends('backend.layouts.app')

@section('title', 'Cấu hình website')

@section('content')
<style>
  .settings-shell {
    background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 16px;
  }
  .settings-tabs {
    gap: 10px;
    border-bottom: 0;
    margin-bottom: 14px;
  }
  .settings-tabs .nav-link {
    border: 1px solid #d1d5db;
    background: #fff;
    color: #374151;
    border-radius: 10px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
  }
  .settings-tabs .nav-link.active {
    border-color: #0d6efd;
    background: #0d6efd;
    color: #fff;
    box-shadow: 0 6px 18px rgba(13, 110, 253, .25);
  }
  .settings-pane {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 16px;
  }
  .settings-pane .form-label {
    font-weight: 600;
  }
  .settings-title {
    font-size: 1.05rem;
    font-weight: 700;
    margin-bottom: 12px;
    color: #111827;
  }
  .settings-save {
    min-width: 150px;
  }
  .settings-upload-card {
    padding: 14px;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
  }
  .settings-upload-preview {
    margin-top: 10px;
    min-height: 82px;
    padding: 12px;
    border: 1px dashed #cbd5e1;
    border-radius: 12px;
    background: #f8fafc;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .settings-upload-preview.is-favicon {
    min-height: 96px;
  }
  .settings-upload-preview img {
    max-width: 100%;
    max-height: 56px;
    object-fit: contain;
    display: block;
  }
  .settings-upload-preview.is-favicon img {
    max-height: 48px;
  }
  .settings-upload-empty {
    color: #64748b;
    font-size: .92rem;
  }
  .settings-check {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-top: 10px;
    color: #475569;
    font-size: .92rem;
  }
  .settings-help {
    margin-top: 8px;
    color: #64748b;
    font-size: .88rem;
  }
  .settings-method-card {
    height: 100%;
    padding: 14px;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
  }
  .settings-method-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 12px;
  }
  .settings-method-title {
    margin: 0;
    font-size: .98rem;
    font-weight: 700;
    color: #0f172a;
  }
  .settings-switch {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: .92rem;
    color: #475569;
  }
  .settings-email-preview-shell {
    background: #d6ccc2;
    border: 1px solid #cbd5e1;
    border-radius: 16px;
    padding: 16px;
    overflow: auto;
  }
  .settings-email-preview-frame {
    min-width: 720px;
    transform: scale(.7);
    transform-origin: top left;
    width: 142.857%;
    margin-bottom: -28%;
  }
</style>

<div class="app-content-header">
  <div class="container-fluid"><h1 class="mb-0">Cấu hình website</h1></div>
</div>
<div class="app-content">
  <div class="container-fluid">
    @if (session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('backend.settings.update') }}" enctype="multipart/form-data">
      @csrf
      <div class="settings-shell">
        <ul class="nav settings-tabs" id="settingTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-general" data-bs-toggle="tab" data-bs-target="#pane-general" type="button" role="tab">
              <i class="bi bi-grid-1x2"></i> Chung
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-contact" data-bs-toggle="tab" data-bs-target="#pane-contact" type="button" role="tab">
              <i class="bi bi-telephone"></i> Liên hệ
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-social" data-bs-toggle="tab" data-bs-target="#pane-social" type="button" role="tab">
              <i class="bi bi-share"></i> Mạng xã hội
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-seo" data-bs-toggle="tab" data-bs-target="#pane-seo" type="button" role="tab">
              <i class="bi bi-search"></i> SEO
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-payment" data-bs-toggle="tab" data-bs-target="#pane-payment" type="button" role="tab">
              <i class="bi bi-credit-card-2-front"></i> Thanh toán
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-email" data-bs-toggle="tab" data-bs-target="#pane-email" type="button" role="tab">
              <i class="bi bi-envelope"></i> Email
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-telegram" data-bs-toggle="tab" data-bs-target="#pane-telegram" type="button" role="tab">
              <i class="bi bi-telegram"></i> Telegram
            </button>
          </li>
        </ul>

        <div class="tab-content">
          <div class="tab-pane fade show active" id="pane-general" role="tabpanel" aria-labelledby="tab-general">
            <div class="settings-pane">
              <div class="settings-title">Thông tin website</div>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Tên website</label>
                  <input type="text" class="form-control" name="site_name" value="{{ old('site_name', $settings['site_name'] ?? '') }}">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Slogan</label>
                  <input type="text" class="form-control" name="site_slogan" value="{{ old('site_slogan', $settings['site_slogan'] ?? '') }}">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Logo website</label>
                  <div class="settings-upload-card">
                    <input type="file" class="form-control" name="site_logo_file" accept="image/*">
                    <input type="hidden" name="site_logo_url" value="{{ old('site_logo_url', $settings['site_logo_url'] ?? '') }}">
                    <div class="settings-upload-preview">
                      @if (!empty($settings['site_logo_url']))
                        <img src="{{ $settings['site_logo_url'] }}" alt="Logo website">
                      @else
                        <span class="settings-upload-empty">Chua co logo website</span>
                      @endif
                    </div>
                    @if (!empty($settings['site_logo_url']))
                      <label class="settings-check">
                        <input type="checkbox" name="remove_site_logo" value="1">
                        <span>Xoa logo hien tai</span>
                      </label>
                    @endif
                    <div class="settings-help">Tai len logo truc tiep. Nen dung PNG, SVG hoac WebP nen trong.</div>
                  </div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Icon website / Favicon</label>
                  <div class="settings-upload-card">
                    <input type="file" class="form-control" name="site_favicon_file" accept="image/*">
                    <input type="hidden" name="site_favicon_url" value="{{ old('site_favicon_url', $settings['site_favicon_url'] ?? '') }}">
                    <div class="settings-upload-preview is-favicon">
                      @if (!empty($settings['site_favicon_url']))
                        <img src="{{ $settings['site_favicon_url'] }}" alt="Favicon website">
                      @else
                        <span class="settings-upload-empty">Chua co favicon</span>
                      @endif
                    </div>
                    @if (!empty($settings['site_favicon_url']))
                      <label class="settings-check">
                        <input type="checkbox" name="remove_site_favicon" value="1">
                        <span>Xoa favicon hien tai</span>
                      </label>
                    @endif
                    <div class="settings-help">Nen dung icon vuong PNG hoac WebP kich thuoc 256x256 tro len.</div>
                  </div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Huong dan su dung website dat hang</label>
                  <textarea class="form-control" name="website_usage_guide" rows="5">{{ old('website_usage_guide', $settings['website_usage_guide'] ?? '') }}</textarea>
                  <div class="settings-help">Noi dung nay hien o muc Huong dan ngoai trang chu, dung de huong dan khach cach tim san pham, them vao gio va dat hang.</div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Huong dan chon size san pham</label>
                  <textarea class="form-control" name="product_size_guide" rows="5">{{ old('product_size_guide', $settings['product_size_guide'] ?? '') }}</textarea>
                  <div class="settings-help">Nhap moi dong theo dang: Can nang | Size | Goi y. Ban cung co the paste bang co 3 cot tu Excel hoac Google Sheets.</div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Chinh sach bao quan san pham</label>
                  <textarea class="form-control" name="product_care_policy" rows="5">{{ old('product_care_policy', $settings['product_care_policy'] ?? '') }}</textarea>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Chinh sach doi tra san pham</label>
                  <textarea class="form-control" name="product_return_policy" rows="5">{{ old('product_return_policy', $settings['product_return_policy'] ?? '') }}</textarea>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Chinh sach bao mat thong tin</label>
                  <textarea class="form-control" name="privacy_policy" rows="5">{{ old('privacy_policy', $settings['privacy_policy'] ?? '') }}</textarea>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Chinh sach van chuyen</label>
                  <textarea class="form-control" name="shipping_policy" rows="5">{{ old('shipping_policy', $settings['shipping_policy'] ?? '') }}</textarea>
                </div>
              </div>
            </div>
          </div>

          <div class="tab-pane fade" id="pane-contact" role="tabpanel" aria-labelledby="tab-contact">
            <div class="settings-pane">
              <div class="settings-title">Thông tin liên hệ</div>
              <div class="row g-3">
                <div class="col-md-4">
                  <label class="form-label">Hotline</label>
                  <input type="text" class="form-control" name="hotline" value="{{ old('hotline', $settings['hotline'] ?? '') }}">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Điện thoại</label>
                  <input type="text" class="form-control" name="contact_phone" value="{{ old('contact_phone', $settings['contact_phone'] ?? '') }}">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Email liên hệ</label>
                  <input type="text" class="form-control" name="contact_email" value="{{ old('contact_email', $settings['contact_email'] ?? '') }}">
                </div>
                <div class="col-md-12">
                  <label class="form-label">Địa chỉ</label>
                  <input type="text" class="form-control" name="contact_address" value="{{ old('contact_address', $settings['contact_address'] ?? '') }}">
                </div>
              </div>
            </div>
          </div>

          <div class="tab-pane fade" id="pane-social" role="tabpanel" aria-labelledby="tab-social">
            <div class="settings-pane">
              <div class="settings-title">Đường dẫn mạng xã hội</div>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Link Zalo</label>
                  <input type="text" class="form-control" name="zalo_url" value="{{ old('zalo_url', $settings['zalo_url'] ?? '') }}">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Link nhóm Zalo</label>
                  <input type="text" class="form-control" name="zalo_group_url" value="{{ old('zalo_group_url', $settings['zalo_group_url'] ?? '') }}">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Link Facebook</label>
                  <input type="text" class="form-control" name="facebook_url" value="{{ old('facebook_url', $settings['facebook_url'] ?? '') }}">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Link Instagram</label>
                  <input type="text" class="form-control" name="instagram_url" value="{{ old('instagram_url', $settings['instagram_url'] ?? '') }}">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Link Youtube</label>
                  <input type="text" class="form-control" name="youtube_url" value="{{ old('youtube_url', $settings['youtube_url'] ?? '') }}">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Link TikTok</label>
                  <input type="text" class="form-control" name="tiktok_url" value="{{ old('tiktok_url', $settings['tiktok_url'] ?? '') }}">
                </div>
              </div>
            </div>
          </div>

          <div class="tab-pane fade" id="pane-seo" role="tabpanel" aria-labelledby="tab-seo">
            <div class="settings-pane">
              <div class="settings-title">SEO cơ bản</div>
              <div class="row g-3">
                <div class="col-md-12">
                  <label class="form-label">Meta title mặc định</label>
                  <input type="text" class="form-control" name="seo_meta_title" value="{{ old('seo_meta_title', $settings['seo_meta_title'] ?? '') }}">
                </div>
                <div class="col-md-12">
                  <label class="form-label">Meta description mặc định</label>
                  <textarea class="form-control" name="seo_meta_description" rows="3">{{ old('seo_meta_description', $settings['seo_meta_description'] ?? '') }}</textarea>
                </div>
                <div class="col-md-12">
                  <label class="form-label">Meta keywords mặc định</label>
                  <textarea class="form-control" name="seo_meta_keywords" rows="2">{{ old('seo_meta_keywords', $settings['seo_meta_keywords'] ?? '') }}</textarea>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Robots mặc định</label>
                  <input type="text" class="form-control" name="seo_robots" value="{{ old('seo_robots', $settings['seo_robots'] ?? 'index,follow') }}">
                </div>
                <div class="col-md-8">
                  <label class="form-label">Canonical mặc định</label>
                  <input type="text" class="form-control" name="seo_canonical_url" value="{{ old('seo_canonical_url', $settings['seo_canonical_url'] ?? url('/')) }}">
                </div>
                <div class="col-md-12">
                  <label class="form-label">OG image khi chia sẻ link</label>
                  <div class="settings-upload-card">
                    <input type="file" class="form-control" name="seo_og_image_file" accept="image/*">
                    <input type="hidden" name="seo_og_image_url" value="{{ old('seo_og_image_url', $settings['seo_og_image_url'] ?? '') }}">
                    <div class="settings-upload-preview">
                      @if (!empty($settings['seo_og_image_url']))
                        <img src="{{ $settings['seo_og_image_url'] }}" alt="OG image">
                      @else
                        <span class="settings-upload-empty">Chua co anh chia se</span>
                      @endif
                    </div>
                    @if (!empty($settings['seo_og_image_url']))
                      <label class="settings-check">
                        <input type="checkbox" name="remove_seo_og_image" value="1">
                        <span>Xoa anh chia se hien tai</span>
                      </label>
                    @endif
                    <div class="settings-help">Nen dung anh ngang 1200x630 de hien thi dep khi chia se len Facebook, Zalo, Telegram.</div>
                  </div>
                </div>
                <div class="col-md-12">
                  <label class="form-label">OG image URL</label>
                  <input type="text" class="form-control" name="seo_og_image_url" value="{{ old('seo_og_image_url', $settings['seo_og_image_url'] ?? '') }}">
                  <div class="settings-help">Co the de trong neu ban upload anh o muc phia tren.</div>
                </div>
              </div>
            </div>
          </div>

          <div class="tab-pane fade" id="pane-payment" role="tabpanel" aria-labelledby="tab-payment">
            <div class="settings-pane">
              <div class="settings-title">Cấu hình thanh toán</div>
              @php
                $defaultPaymentMethod = old('payment_default_method', $settings['payment_default_method'] ?? 'cod');
              @endphp
              <div class="row g-3">
                <div class="col-md-4">
                  <label class="form-label">Phương thức mặc định</label>
                  <select class="form-select" name="payment_default_method">
                    <option value="cod" {{ $defaultPaymentMethod === 'cod' ? 'selected' : '' }}>COD</option>
                    <option value="vietqr" {{ $defaultPaymentMethod === 'vietqr' ? 'selected' : '' }}>VietQR</option>
                  </select>
                </div>
                <div class="col-md-8">
                  <label class="form-label">Ghi chú chung thanh toán</label>
                  <input type="text" class="form-control" name="payment_checkout_note" value="{{ old('payment_checkout_note', $settings['payment_checkout_note'] ?? '') }}" placeholder="Ví dụ: Nhân viên sẽ gọi xác nhận trước khi xử lý đơn hàng.">
                  <div class="settings-help">Nơi lưu nội dung nhắc khách hàng khi chọn phương thức thanh toán ở checkout.</div>
                </div>

                <div class="col-md-6">
                  <div class="settings-method-card">
                    <div class="settings-method-head">
                      <h3 class="settings-method-title">COD</h3>
                      <label class="settings-switch">
                        <input type="checkbox" name="payment_cod_enabled" value="1" {{ old('payment_cod_enabled', $settings['payment_cod_enabled'] ?? '1') ? 'checked' : '' }}>
                        <span>Bật</span>
                      </label>
                    </div>
                    <div class="row g-3">
                      <div class="col-12">
                        <label class="form-label">Tiêu đề hiển thị</label>
                        <input type="text" class="form-control" name="payment_cod_title" value="{{ old('payment_cod_title', $settings['payment_cod_title'] ?? 'Thanh toán khi nhận hàng') }}">
                      </div>
                      <div class="col-12">
                        <label class="form-label">Mô tả ngắn</label>
                        <input type="text" class="form-control" name="payment_cod_description" value="{{ old('payment_cod_description', $settings['payment_cod_description'] ?? 'Khách thanh toán trực tiếp khi nhận đơn.') }}">
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="settings-method-card">
                    <div class="settings-method-head">
                      <h3 class="settings-method-title">VietQR</h3>
                      <label class="settings-switch">
                        <input type="checkbox" name="payment_vietqr_enabled" value="1" {{ old('payment_vietqr_enabled', $settings['payment_vietqr_enabled'] ?? '0') ? 'checked' : '' }}>
                        <span>Bật</span>
                      </label>
                    </div>
                    <div class="row g-3">
                      <div class="col-12">
                        <label class="form-label">Tiêu đề hiển thị</label>
                        <input type="text" class="form-control" name="payment_vietqr_title" value="{{ old('payment_vietqr_title', $settings['payment_vietqr_title'] ?? 'Chuyển khoản VietQR') }}">
                      </div>
                      <div class="col-12">
                        <label class="form-label">Mô tả ngắn</label>
                        <input type="text" class="form-control" name="payment_vietqr_description" value="{{ old('payment_vietqr_description', $settings['payment_vietqr_description'] ?? 'Quét mã QR ngân hàng để chuyển khoản.') }}">
                      </div>
                      <div class="col-12">
                        <label class="form-label">Mã ngân hàng / BIN</label>
                        <input type="text" class="form-control" name="payment_vietqr_bank_bin" value="{{ old('payment_vietqr_bank_bin', $settings['payment_vietqr_bank_bin'] ?? '') }}" placeholder="Ví dụ: 970422">
                        <div class="settings-help">Dùng mã BIN ngân hàng theo VietQR, ví dụ MBBank là 970422.</div>
                      </div>
                      <div class="col-12">
                        <label class="form-label">Tên ngân hàng</label>
                        <input type="text" class="form-control" name="payment_vietqr_bank_name" value="{{ old('payment_vietqr_bank_name', $settings['payment_vietqr_bank_name'] ?? '') }}" placeholder="Ví dụ: MBBank">
                      </div>
                      <div class="col-12">
                        <label class="form-label">Số tài khoản</label>
                        <input type="text" class="form-control" name="payment_vietqr_account_no" value="{{ old('payment_vietqr_account_no', $settings['payment_vietqr_account_no'] ?? '') }}">
                      </div>
                      <div class="col-12">
                        <label class="form-label">Tên chủ tài khoản</label>
                        <input type="text" class="form-control" name="payment_vietqr_account_name" value="{{ old('payment_vietqr_account_name', $settings['payment_vietqr_account_name'] ?? '') }}" placeholder="Ví dụ: NGUYEN VAN A">
                      </div>
                        <div class="col-12">
                          <label class="form-label">Template QR</label>
                          <input type="text" class="form-control" name="payment_vietqr_template" value="{{ old('payment_vietqr_template', $settings['payment_vietqr_template'] ?? 'compact2') }}">
                        </div>
                        <div class="col-12">
                          <label class="form-label">Tiền tố nội dung chuyển khoản</label>
                          <input type="text" class="form-control" name="payment_vietqr_transfer_prefix" value="{{ old('payment_vietqr_transfer_prefix', $settings['payment_vietqr_transfer_prefix'] ?? 'TT') }}" placeholder="Ví dụ: TT">
                          <div class="settings-help">Hệ thống sẽ tạo nội dung theo dạng: tiền tố + mã tham chiếu, không có khoảng trắng và không có dấu gạch ngang. Ví dụ: <code>TTINV2026040612</code></div>
                        </div>
                        <div class="col-12">
                          <label class="form-label">Thời gian giữ hóa đơn thanh toán (phút)</label>
                          <input type="number" min="1" max="1440" step="1" class="form-control" name="payment_vietqr_expire_minutes" value="{{ old('payment_vietqr_expire_minutes', $settings['payment_vietqr_expire_minutes'] ?? 30) }}" placeholder="Ví dụ: 30">
                          <div class="settings-help">Áp dụng cho đồng hồ đếm ngược trên hóa đơn VietQR và icon nhắc quay lại thanh toán.</div>
                        </div>
                        <div class="col-12">
                          <label class="form-label">SePay Webhook Secret</label>
                          <input type="text" class="form-control" name="payment_sepay_webhook_secret" value="{{ old('payment_sepay_webhook_secret', $settings['payment_sepay_webhook_secret'] ?? '') }}" placeholder="Nhập secret hoặc Bearer token của SePay">
                        <div class="settings-help">Webhook URL dùng với SePay: <code>{{ route('frontend.vietqr.sepay-webhook') }}</code></div>
                      </div>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>

          <div class="tab-pane fade" id="pane-email" role="tabpanel" aria-labelledby="tab-email">
            <div class="settings-pane">
              <div class="settings-title">Cấu hình gửi email</div>
              <div class="row g-3">
                <div class="col-lg-8">
                  <div class="settings-method-card">
                    <div class="settings-method-head">
                      <h3 class="settings-method-title">SMTP / Gmail</h3>
                    </div>
                    <div class="row g-3">
                      <div class="col-md-4">
                        <label class="form-label">Mailer</label>
                        <input type="text" class="form-control" name="mail_mailer" value="{{ old('mail_mailer', $settings['mail_mailer'] ?? env('MAIL_MAILER', 'smtp')) }}" placeholder="smtp">
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Scheme</label>
                        <input type="text" class="form-control" name="mail_scheme" value="{{ old('mail_scheme', $settings['mail_scheme'] ?? env('MAIL_SCHEME', 'smtp')) }}" placeholder="smtp">
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Port</label>
                        <input type="number" min="1" max="65535" class="form-control" name="mail_port" value="{{ old('mail_port', $settings['mail_port'] ?? env('MAIL_PORT', 587)) }}" placeholder="587">
                      </div>
                      <div class="col-md-12">
                        <label class="form-label">SMTP Host</label>
                        <input type="text" class="form-control" name="mail_host" value="{{ old('mail_host', $settings['mail_host'] ?? env('MAIL_HOST', 'smtp.gmail.com')) }}" placeholder="smtp.gmail.com">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Email gửi</label>
                        <input type="email" class="form-control" name="mail_username" value="{{ old('mail_username', $settings['mail_username'] ?? env('MAIL_USERNAME')) }}" placeholder="your-gmail@gmail.com">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">App password</label>
                        <input type="text" class="form-control" name="mail_password" value="{{ old('mail_password', $settings['mail_password'] ?? env('MAIL_PASSWORD')) }}" placeholder="Mật khẩu ứng dụng Gmail">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">From address</label>
                        <input type="email" class="form-control" name="mail_from_address" value="{{ old('mail_from_address', $settings['mail_from_address'] ?? env('MAIL_FROM_ADDRESS')) }}" placeholder="your-gmail@gmail.com">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">From name</label>
                        <input type="text" class="form-control" name="mail_from_name" value="{{ old('mail_from_name', $settings['mail_from_name'] ?? ($settings['site_name'] ?? env('MAIL_FROM_NAME', config('app.name')))) }}" placeholder="Tên shop hiển thị">
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="settings-method-card">
                    <div class="settings-method-head">
                      <h3 class="settings-method-title">Gợi ý Gmail</h3>
                    </div>
                    <div class="settings-help" style="margin-top: 0;">
                      Với Gmail thông thường, dùng <strong>Mailer</strong> = <code>smtp</code>, <strong>Host</strong> = <code>smtp.gmail.com</code>, <strong>Port</strong> = <code>587</code>, <strong>Scheme</strong> = <code>smtp</code>.
                    </div>
                    <div class="settings-help">
                      Trường <strong>App password</strong> cần là mật khẩu ứng dụng của Google, không phải mật khẩu đăng nhập Gmail chính.
                    </div>
                    <div class="settings-help">
                      Nếu để trống các ô này, hệ thống sẽ fallback về cấu hình trong file <code>.env</code>.
                    </div>
                    <div class="settings-help">
                      Sau khi lưu, các email xác nhận đơn mới sẽ dùng cấu hình này.
                    </div>
                  </div>
                </div>
                <div class="col-12">
                  <div class="settings-method-card">
                    <div class="settings-method-head">
                      <h3 class="settings-method-title">Xem trước email</h3>
                    </div>
                    <div class="settings-help" style="margin-top: 0;">
                      Đây là bản xem trước với dữ liệu mẫu để bạn hình dung giao diện email khách sẽ nhận.
                    </div>
                    <div class="settings-email-preview-shell mt-3">
                      <div class="settings-email-preview-frame">
                        @include('emails.orders._content', ['payload' => $emailPreviewPayload])
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="tab-pane fade" id="pane-telegram" role="tabpanel" aria-labelledby="tab-telegram">
            <div class="settings-pane">
              <div class="settings-title">Thong bao don hang qua Telegram</div>
              <div class="row g-3">
                <div class="col-lg-8">
                  <div class="settings-method-card">
                    <div class="settings-method-head">
                      <h3 class="settings-method-title">Bot Telegram</h3>
                      <label class="settings-switch">
                        <input type="checkbox" name="telegram_notifications_enabled" value="1" {{ old('telegram_notifications_enabled', $settings['telegram_notifications_enabled'] ?? '0') ? 'checked' : '' }}>
                        <span>Bat</span>
                      </label>
                    </div>
                    <div class="row g-3">
                      <div class="col-md-12">
                        <label class="form-label">Bot token</label>
                        <input type="text" class="form-control" name="telegram_bot_token" value="{{ old('telegram_bot_token', $settings['telegram_bot_token'] ?? '') }}" placeholder="123456789:AA...">
                      </div>
                      <div class="col-md-12">
                        <label class="form-label">Chat ID nhan thong bao</label>
                        <input type="text" class="form-control" name="telegram_chat_id" value="{{ old('telegram_chat_id', $settings['telegram_chat_id'] ?? '') }}" placeholder="Vi du: 123456789 hoac -100xxxxxxxxxx">
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="settings-method-card">
                    <div class="settings-method-head">
                      <h3 class="settings-method-title">Huong dan nhanh</h3>
                    </div>
                    <div class="settings-help" style="margin-top: 0;">
                      Tao bot voi <code>@BotFather</code>, lay <strong>bot token</strong>, sau do nhan tin cho bot hoac them bot vao nhom Telegram cua ban.
                    </div>
                    <div class="settings-help">
                      Lay <strong>chat id</strong> cua tai khoan/nhom, nhap vao day va bat thong bao. Moi don moi se duoc gui ngay khi he thong tao don.
                    </div>
                    <div class="settings-help">
                      Neu Telegram loi, he thong van tao don binh thuong va khong chan khach dat hang.
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-3">
        <button type="submit" class="btn btn-primary settings-save">Lưu cấu hình</button>
      </div>
    </form>
  </div>
</div>
@endsection
