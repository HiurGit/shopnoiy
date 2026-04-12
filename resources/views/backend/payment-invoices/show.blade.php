@extends('backend.layouts.app')

@section('title', 'Chi tiet hoa don')

@section('content')
<div class="app-content-header">
  <div class="container-fluid d-flex justify-content-between align-items-center">
    <h1 class="mb-0">Chi tiet hoa don</h1>
    <a href="{{ route('backend.payment-invoices') }}" class="btn btn-secondary btn-sm">Quay lai</a>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">
    <div class="row g-3">
      <div class="col-lg-5">
        <div class="card h-100">
          <div class="card-header">
            <h3 class="card-title mb-0">Thong tin hoa don</h3>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped align-middle mb-0">
                <tbody>
                  <tr><th>Ma hoa don</th><td><strong>{{ $paymentInvoice->invoice_code }}</strong></td></tr>
                  <tr><th>Khach hang</th><td>{{ $paymentInvoice->customer_name }}</td></tr>
                  <tr><th>So dien thoai</th><td>{{ $paymentInvoice->customer_phone }}</td></tr>
                  <tr><th>Email</th><td>{{ $paymentInvoice->customer_email ?: '-' }}</td></tr>
                  <tr><th>Hinh thuc nhan</th><td>{{ $paymentInvoice->delivery_type_label }}</td></tr>
                  <tr>
                    <th>Cua hang nhan</th>
                    <td>
                      @if ($paymentInvoice->delivery_type === 'pickup')
                        {{ $selectedStore?->name ?: '-' }}
                      @else
                        -
                      @endif
                    </td>
                  </tr>
                  <tr><th>Dia chi</th><td>{{ $paymentInvoice->shipping_address_text ?: '-' }}</td></tr>
                  <tr><th>Noi dung CK</th><td><code>{{ $paymentInvoice->transfer_content ?: '-' }}</code></td></tr>
                  <tr><th>Thanh toan</th><td>{{ $paymentInvoice->payment_method_label }} / {{ $paymentInvoice->payment_status_label }}</td></tr>
                  <tr><th>Trang thai hoa don</th><td>{{ $paymentInvoice->invoice_status_label }}</td></tr>
                  <tr><th>Da thanh toan luc</th><td>{{ optional($paymentInvoice->paid_at)->format('d/m/Y H:i') ?: '-' }}</td></tr>
                  <tr><th>Da tao don luc</th><td>{{ optional($paymentInvoice->converted_at)->format('d/m/Y H:i') ?: '-' }}</td></tr>
                  <tr>
                    <th>Don hang sinh ra</th>
                    <td>
                      @if ($order)
                        <a href="{{ route('backend.orders.show', $order->id) }}" class="text-decoration-none">{{ $order->order_code }}</a>
                      @else
                        -
                      @endif
                    </td>
                  </tr>
                  <tr><th>Tong tien</th><td class="fw-bold text-primary">{{ number_format((float) $paymentInvoice->total_amount, 0, ',', '.') }}d</td></tr>
                  <tr><th>Ghi chu</th><td>{{ $paymentInvoice->note ?: '-' }}</td></tr>
                  <tr><th>Ngay tao</th><td>{{ optional($paymentInvoice->created_at)->format('d/m/Y H:i') }}</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-7">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title mb-0">San pham trong hoa don</h3>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead>
                  <tr>
                    <th>San pham</th>
                    <th>Bien the</th>
                    <th>So luong</th>
                    <th>Don gia</th>
                    <th>Thanh tien</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($items as $item)
                    <tr>
                      <td>
                        <div class="d-flex align-items-center gap-2">
                          @if ($item->preview_image_url)
                            <img src="{{ str_starts_with($item->preview_image_url, 'http') ? $item->preview_image_url : asset(ltrim($item->preview_image_url, '/')) }}" alt="{{ $item->product_name_snapshot }}" style="width:48px;height:48px;object-fit:cover;border-radius:10px;border:1px solid #e2e8f0;">
                          @endif
                          <div>
                            <div class="fw-semibold">{{ $item->product_name_snapshot }}</div>
                            <div class="small text-muted">ID SP: {{ $item->product_id }}</div>
                          </div>
                        </div>
                      </td>
                      <td>{{ $item->variant_name_snapshot ?: '-' }}</td>
                      <td>{{ (int) $item->qty }}</td>
                      <td>{{ number_format((float) $item->unit_price, 0, ',', '.') }}d</td>
                      <td>{{ number_format((float) $item->line_total, 0, ',', '.') }}d</td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="5" class="text-center">Khong co san pham nao.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
