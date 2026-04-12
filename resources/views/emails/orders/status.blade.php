<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $payload['subject'] }}</title>
  <style>
    @media only screen and (max-width: 640px) {
      .email-shell {
        padding: 10px 8px 24px !important;
      }

      .email-hero,
      .email-content,
      .email-footer {
        padding-left: 18px !important;
        padding-right: 18px !important;
      }

      .email-hero {
        padding-top: 22px !important;
        padding-bottom: 28px !important;
      }

      .email-logo {
        max-width: 190px !important;
        max-height: 62px !important;
        margin-bottom: 16px !important;
      }

      .email-title,
      .email-section-title {
        font-size: 30px !important;
        line-height: 1.2 !important;
      }

      .email-intro,
      .email-section-subtitle,
      .email-store-address {
        font-size: 15px !important;
        line-height: 1.7 !important;
      }

      .email-button {
        display: block !important;
        width: 100% !important;
        padding: 14px 18px !important;
        box-sizing: border-box !important;
        font-size: 16px !important;
        text-align: center !important;
      }

      .email-note {
        font-size: 13px !important;
      }

      .order-top-grid {
        border-spacing: 0 14px !important;
      }

      .order-top-grid,
      .order-top-grid tbody,
      .order-top-grid tr,
      .order-top-grid__cell {
        display: block !important;
        width: 100% !important;
      }

      .order-item-row,
      .order-item-row tbody,
      .order-item-row tr,
      .order-item-row__thumb,
      .order-item-row__content {
        display: block !important;
        width: 100% !important;
      }

      .order-item-row__thumb {
        padding-bottom: 12px !important;
      }

      .order-item-image-wrap,
      .order-item-image {
        width: 72px !important;
        height: 72px !important;
      }

      .order-item-name {
        font-size: 16px !important;
      }

      .order-item-meta,
      .order-item-price,
      .order-item-total {
        font-size: 14px !important;
      }

      .support-grid {
        border-spacing: 0 12px !important;
      }

      .support-grid,
      .support-grid tbody,
      .support-grid tr,
      .support-grid__cell {
        display: block !important;
        width: 100% !important;
      }

      .email-store-card {
        padding: 20px 16px !important;
        border-radius: 18px !important;
      }

      .email-store-title,
      .email-footer-title {
        font-size: 24px !important;
      }
    }
  </style>
</head>
<body style="margin:0;background:#e9e2da;font-family:Arial,sans-serif;">
  @include('emails.orders._content', ['payload' => $payload])
</body>
</html>
