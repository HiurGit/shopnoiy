<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $site_name }} - Đặt lại mật khẩu</title>
</head>
<body style="margin:0;background:#e9e2da;font-family:Arial,sans-serif;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#e9e2da;">
    <tr>
      <td align="center" style="padding:24px 12px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:680px;background:#ffffff;border-radius:20px;overflow:hidden;">
          <tr>
            <td style="background:linear-gradient(135deg,#2f241a 0%,#533823 100%);padding:26px 24px;text-align:center;">
              @if (!empty($logo_url))
                <img src="{{ $logo_url }}" alt="{{ $brand_name }}" style="max-width:180px;max-height:64px;display:block;margin:0 auto 14px;">
              @endif
              <h1 style="margin:0;color:#fff;font-size:28px;line-height:1.2;">Đặt lại mật khẩu</h1>
              <p style="margin:10px 0 0;color:#f8efe9;font-size:14px;line-height:1.6;">
                Yêu cầu đặt lại mật khẩu cho tài khoản {{ $email }}
              </p>
            </td>
          </tr>

          <tr>
            <td style="padding:24px;">
              <p style="margin:0 0 12px;color:#2f241a;font-size:16px;line-height:1.7;">
                Chúng tôi đã nhận được yêu cầu đặt lại mật khẩu tài khoản của bạn.
              </p>
              <p style="margin:0 0 18px;color:#2f241a;font-size:16px;line-height:1.7;">
                Nhấn nút bên dưới để tạo mật khẩu mới. Liên kết có hiệu lực trong {{ $expires_in }} phút.
              </p>

              <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 18px;">
                <tr>
                  <td style="border-radius:999px;background:#252c2c;">
                    <a href="{{ $reset_url }}" style="display:inline-block;padding:14px 24px;color:#fff;text-decoration:none;font-size:14px;letter-spacing:0.08em;text-transform:uppercase;">
                      Đặt lại mật khẩu
                    </a>
                  </td>
                </tr>
              </table>

              <p style="margin:0;color:#6d6f6b;font-size:14px;line-height:1.7;">
                Nếu bạn không yêu cầu thao tác này, vui lòng bỏ qua email.
              </p>
            </td>
          </tr>

          <tr>
            <td style="padding:20px 24px;border-top:1px solid #efe7df;background:#faf8f5;">
              <p style="margin:0 0 8px;color:#2f241a;font-size:14px;font-weight:bold;">{{ $brand_name }}</p>
              @if (!empty($contact_phone))
                <p style="margin:0 0 4px;color:#6d6f6b;font-size:13px;">Phone: {{ $contact_phone }}</p>
              @endif
              @if (!empty($contact_email))
                <p style="margin:0 0 4px;color:#6d6f6b;font-size:13px;">Email: {{ $contact_email }}</p>
              @endif
              @if (!empty($contact_address))
                <p style="margin:0;color:#6d6f6b;font-size:13px;">Address: {{ $contact_address }}</p>
              @endif
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
