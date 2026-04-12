@php
  $dark = '#20140f';
  $darkSoft = '#33231a';
  $gold = '#f6c55b';
  $goldSoft = '#fef0c7';
  $cream = '#fbf8f3';
  $text = '#241815';
  $muted = '#6b5b54';
  $line = '#eadfce';
@endphp
<div class="email-shell" style="max-width:680px;margin:0 auto;padding:20px 16px 32px;font-family:Arial,sans-serif;">
  <div style="background:{{ $cream }};overflow:hidden;box-shadow:0 18px 40px rgba(36,24,21,.08);">
    <div class="email-hero" style="background:{{ $dark }};color:#fff;padding:28px 38px 40px;">
      <div style="text-align:center;">
        @if (!empty($payload['logo_image'] ?? $payload['logo_url']))
          <img class="email-logo" src="{{ $payload['logo_image'] ?? $payload['logo_url'] }}" alt="{{ $payload['site_name'] }}" style="max-width:260px;max-height:78px;object-fit:contain;display:block;margin:0 auto 20px;">
        @else
          <div style="font-size:30px;line-height:1.2;color:#fff;">{{ $payload['site_name'] }}</div>
        @endif

        <h1 class="email-title" style="margin:20px 0 12px;font-size:46px;line-height:1.12;letter-spacing:-0.03em;">{{ $payload['heading'] }}</h1>
        <p class="email-intro" style="max-width:540px;margin:0 auto;font-size:18px;line-height:1.8;color:#f3e9df;">{{ $payload['intro'] }}</p>

        <div style="margin:26px 0 14px;">
          <a class="email-button" href="{{ $payload['tracking_url'] }}" style="display:inline-block;padding:16px 34px;border-radius:999px;background:{{ $gold }};color:#2c1b10;text-decoration:none;font-size:19px;">Theo dõi đơn hàng</a>
        </div>

        <div class="email-note" style="font-size:14px;line-height:1.7;color:#ccbfb5;">{{ $payload['hero_note'] }}</div>
      </div>

      <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top:34px;border-collapse:collapse;">
        <tr>
          <td style="padding:0 0 14px;vertical-align:top;">
            <div style="padding:20px 18px;border-radius:16px;background:{{ $darkSoft }};">
              <div style="font-size:13px;color:{{ $gold }};margin-bottom:12px;letter-spacing:.04em;text-transform:uppercase;">Tóm tắt đơn hàng</div>
              <div style="font-size:22px;line-height:1.25;color:#fff;">{{ $payload['summary_label'] }}</div>
              <div style="margin-top:12px;font-size:14px;line-height:1.7;color:#eadfd5;">#{{ $payload['order_code'] }} · {{ $payload['order_date'] }}</div>
              <div style="margin-top:10px;font-size:14px;line-height:1.7;color:#eadfd5;">Thanh toán: {{ $payload['payment_method'] }}</div>
              <div style="margin-top:2px;font-size:14px;line-height:1.7;color:#eadfd5;">Trạng thái: {{ $payload['payment_status'] }}</div>
              <div style="margin-top:16px;font-size:28px;line-height:1.2;color:#fff;">{{ $payload['total_amount'] }}</div>
            </div>
          </td>
        </tr>
        <tr>
          <td style="padding:0;vertical-align:top;">
            <div style="padding:20px 18px;border-radius:16px;background:{{ $darkSoft }};">
              <div style="font-size:13px;color:{{ $gold }};margin-bottom:12px;letter-spacing:.04em;text-transform:uppercase;">{{ $payload['delivery_label'] }}</div>
              <div style="font-size:22px;line-height:1.35;color:#fff;">{{ $payload['customer_name'] }}</div>
              <div style="margin-top:12px;font-size:14px;line-height:1.8;color:#eadfd5;">{{ $payload['delivery_value'] }}</div>
              <div style="margin-top:10px;font-size:14px;line-height:1.7;color:#eadfd5;">Điện thoại: {{ $payload['customer_phone'] }}</div>
              @if (!empty($payload['customer_email']))
                <div style="margin-top:2px;font-size:14px;line-height:1.7;color:#eadfd5;word-break:break-word;">Email: {{ $payload['customer_email'] }}</div>
              @endif
            </div>
          </td>
        </tr>
      </table>
    </div>

    <div style="height:18px;background:
      linear-gradient(-45deg, transparent 12px, {{ $cream }} 0) 0 0/24px 18px repeat-x,
      linear-gradient(45deg, transparent 12px, {{ $cream }} 0) 12px 0/24px 18px repeat-x;">
    </div>

    <div class="email-content" style="padding:34px 38px 24px;">
      <div style="text-align:center;">
        <div class="email-section-title" style="font-size:42px;line-height:1.15;letter-spacing:-0.03em;">Sản phẩm trong đơn hàng</div>
        <div class="email-section-subtitle" style="margin-top:8px;font-size:18px;color:{{ $muted }};">Mã đơn: #{{ $payload['order_code'] }}</div>
      </div>

      <div style="margin-top:30px;">
        @foreach ($payload['items'] as $item)
          <table role="presentation" width="100%" cellspacing="0" cellpadding="0" class="order-item-row" style="border-collapse:collapse;margin-bottom:20px;">
            <tr>
              <td class="order-item-row__thumb" style="width:96px;vertical-align:top;">
                <div class="order-item-image-wrap" style="width:84px;height:84px;border-radius:14px;overflow:hidden;background:#fff4db;">
                  <img class="order-item-image" src="{{ $item['image'] ?? ($item['image_url'] ?? '') }}" alt="{{ $item['name'] }}" style="width:84px;height:84px;object-fit:cover;display:block;">
                </div>
              </td>
              <td class="order-item-row__content" style="vertical-align:top;">
                <div class="order-item-name" style="font-size:18px;line-height:1.4;">{{ $item['name'] }}</div>
                <div class="order-item-meta" style="margin-top:6px;font-size:15px;line-height:1.7;color:{{ $muted }};">
                  @if ($item['variant'] !== '')
                    {{ $item['variant'] }} ·
                  @endif
                  Số lượng: {{ $item['qty'] }}
                </div>
                <div class="order-item-price" style="margin-top:4px;font-size:15px;color:{{ $muted }};">Đơn giá: {{ $item['unit_price'] }}</div>
                <div class="order-item-total" style="margin-top:8px;font-size:17px;color:#0f9d44;">{{ $item['line_total'] }}</div>
              </td>
            </tr>
          </table>
        @endforeach
      </div>

      <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top:18px;border-collapse:collapse;">
        <tr>
          <td style="padding:10px 0;font-size:16px;color:{{ $text }};">Tạm tính</td>
          <td style="padding:10px 0;text-align:right;font-size:16px;color:{{ $text }};">{{ $payload['subtotal'] }}</td>
        </tr>
        <tr>
          <td style="padding:10px 0;font-size:16px;color:{{ $text }};">Phí vận chuyển</td>
          <td style="padding:10px 0;text-align:right;font-size:16px;color:{{ $text }};">{{ $payload['shipping_fee'] }}</td>
        </tr>
        <tr>
          <td colspan="2" style="padding-top:8px;border-bottom:1px solid {{ $line }};"></td>
        </tr>
        <tr>
          <td style="padding:16px 0 4px;font-size:18px;color:{{ $text }};">Tổng cộng</td>
          <td style="padding:16px 0 4px;text-align:right;font-size:18px;color:{{ $text }};">{{ $payload['total_amount'] }}</td>
        </tr>
      </table>

      @if (!empty($payload['note']))
        <div style="margin-top:20px;padding:16px 18px;border-radius:14px;background:#fff3e8;color:#8a4b14;font-size:15px;line-height:1.7;">
          Ghi chú đơn hàng: {{ $payload['note'] }}
        </div>
      @endif

      
    </div>

    <div style="padding:0 0 18px;background:{{ $goldSoft }};">
      <div style="height:18px;background:
        linear-gradient(-45deg, transparent 12px, {{ $goldSoft }} 0) 0 0/24px 18px repeat-x,
        linear-gradient(45deg, transparent 12px, {{ $goldSoft }} 0) 12px 0/24px 18px repeat-x;">
      </div>
    </div>

    <div class="email-footer" style="background:{{ $dark }};color:#fff;padding:26px 38px 34px;text-align:center;">
      <div class="email-footer-title" style="font-size:28px;line-height:1.2;">{{ $payload['site_name'] }}</div>
      @if (!empty($payload['contact_address']))
        <div style="margin-top:14px;font-size:14px;line-height:1.8;color:#d8cabf;">{{ $payload['contact_address'] }}</div>
      @endif
    </div>
  </div>
</div>
