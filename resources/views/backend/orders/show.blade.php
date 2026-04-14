@extends('backend.layouts.app')

@section('title', 'Chi tiết đơn hàng')

@section('content')
@php
  $tierLabels = [
      'new' => 'Khách hàng mới',
      'friendly' => 'Khách hàng thân thiện',
      'loyal' => 'Khách hàng trung thành',
      'vip' => 'Khách hàng VIP',
      'diamond' => 'Khách hàng Kim cương',
  ];
  $totalUnits = (int) $items->sum('qty');
  $totalLines = (int) $items->count();
  $pickList = $items->map(function ($item) {
      $parts = collect(explode('|', (string) ($item->variant_name_snapshot ?? '')))
          ->map(fn ($part) => trim($part))
          ->filter()
          ->values();

      return (object) [
          'id' => $item->id,
          'product_id' => $item->product_id,
          'product_name' => $item->product_name_snapshot,
          'image_url' => $item->preview_image_url,
          'sku' => $item->sku_snapshot ?: '-',
          'color' => $parts->get(0, '-'),
          'size' => $parts->get(1, '-'),
          'variant' => $item->variant_name_snapshot ?: '-',
          'qty' => (int) $item->qty,
          'unit_price' => (float) $item->unit_price,
          'line_total' => (float) $item->line_total,
      ];
  });
@endphp
<style>
  .pick-summary-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
  }
  .pick-summary-card {
    border-radius: 16px;
    padding: 14px 16px;
    background: linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
    border: 1px solid #e2e8f0;
  }
  .pick-summary-card strong {
    display: block;
    margin-top: 6px;
    font-size: 1.4rem;
    color: #16253a;
  }
  .pick-summary-card span {
    color: #64748b;
    font-size: .9rem;
  }
  .pick-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 999px;
    background: #eef6ff;
    color: #1d4f91;
    font-size: .8rem;
    font-weight: 700;
  }
  .pick-qty {
    display: inline-flex;
    min-width: 38px;
    justify-content: center;
    padding: 6px 10px;
    border-radius: 12px;
    background: #16253a;
    color: #fff;
    font-weight: 800;
  }
  .pick-checkbox {
    width: 18px;
    height: 18px;
  }
  .pick-thumb {
    width: 52px;
    height: 52px;
    border-radius: 12px;
    object-fit: cover;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    flex-shrink: 0;
  }
  .pick-thumb-placeholder {
    width: 52px;
    height: 52px;
    border-radius: 12px;
    border: 1px dashed #cbd5e1;
    background: #f8fafc;
    color: #94a3b8;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .tracking-link-box {
    display: grid;
    gap: 10px;
    padding: 12px;
    border-radius: 12px;
    /* background: linear-gradient(180deg, #f8fafc 0%, #eef6ff 100%); */
    border: 1px solid #dbeafe;
  }
  .tracking-link-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
  }
  .tracking-link-code {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 999px;
    background: #dbeafe;
    color: #1d4ed8;
    font-size: .8rem;
    font-weight: 700;
  }
  .tracking-link-url {
    display: block;
    width: 100%;
    word-break: break-all;
    color: #0f172a;
    font-weight: 600;
  }
  @media (max-width: 991.98px) {
    .pick-summary-grid {
      grid-template-columns: 1fr;
    }
  }
</style>
<div class="app-content-header">
  <div class="container-fluid d-flex justify-content-between align-items-center">
    <h1 class="mb-0">Chi tiết đơn hàng</h1>
    <div class="d-flex align-items-center gap-2">
      @if ($order->order_status !== 'verified')
        <form action="{{ route('backend.orders.mark-verified', $order) }}" method="POST" class="d-inline" onsubmit="return confirm('Xác minh đơn hàng này?')">
          @csrf
          @method('PATCH')
          <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
          <button class="btn btn-success btn-sm" type="submit">Đã xác minh</button>
        </form>
      @endif
      <a href="{{ route('backend.orders') }}" class="btn btn-secondary btn-sm">Quay lại</a>
    </div>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">
    <div class="row g-3">
      <div class="col-lg-5">
        <div class="card order-detail-card h-100">
          <div class="card-header">
            <h3 class="card-title mb-0">Thông tin đơn hàng</h3>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped align-middle mb-0 order-detail-meta">
                <tbody>
                  <tr>
                    <th>Mã đơn</th>
                    <td><strong>{{ $order->order_code }}</strong></td>
                  </tr>
                  <tr>
                    <th>Khách hàng</th>
                    <td>
                      <div class="fw-semibold">{{ $order->customer_name }}</div>
                      @if ($linkedCustomer)
                        <a href="{{ route('backend.customers.show', $linkedCustomer) }}" class="small text-decoration-none">Xem hồ sơ khách hàng</a>
                      @else
                        <span class="small text-muted">Chưa liên kết hồ sơ khách hàng</span>
                      @endif
                    </td>
                  </tr>
                  <tr>
                    <th>Số điện thoại</th>
                    <td>{{ $order->customer_phone ?: '-' }}</td>
                  </tr>
                  <tr>
                    <th>Email</th>
                    <td>{{ $order->customer_email ?: '-' }}</td>
                  </tr>
                  <tr>
                    <th>Hình thức nhận</th>
                    <td>{{ $order->delivery_type }}</td>
                  </tr>
                  <tr>
                    <th>Cửa hàng nhận</th>
                    <td>
                      @if ($order->delivery_type === 'pickup')
                        {{ $selectedStore?->name ?: '-' }}
                        @if ($selectedStore)
                          <div class="small text-muted mt-1">{{ $selectedStore->address_line }}, {{ $selectedStore->district }}, {{ $selectedStore->province }}</div>
                        @endif
                      @else
                        -
                      @endif
                    </td>
                  </tr>
                  <tr>
                    <th>Địa chỉ</th>
                    <td>{{ $order->shipping_address_text ?: '-' }}</td>
                  </tr>
                  <tr>
                    <th>Thanh toán</th>
                    <td>{{ $order->payment_method }} / {{ $order->payment_status }}</td>
                  </tr>
                  <tr>
                    <th>Trạng thái</th>
                    <td>{{ $order->order_status_label }}</td>
                  </tr>
                  <tr>
                    <th>Link theo dõi</th>
                    <td>
                      @php
                        $trackingToken = $order->customer_tracking_token ?: '-';
                        $trackingUrl = $order->customer_tracking_token
                            ? \Illuminate\Support\Facades\URL::temporarySignedRoute('frontend.order-tracking', now()->addDays(7), ['token' => $order->customer_tracking_token])
                            : null;
                      @endphp
                      <div class="tracking-link-box">
                         
                        @if ($trackingUrl)
                          <a href="{{ $trackingUrl }}" class="tracking-link-url" target="_blank" rel="noopener">{{ $trackingUrl }}</a>
                         
                        @else
                          <div class="small text-muted">Đơn hàng này chưa có link theo dõi.</div>
                        @endif
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <th>Giờ xác minh</th>
                    <td>{{ optional($order->verified_at)->format('d/m/Y H:i') ?: '-' }}</td>
                  </tr>
                  <tr>
                    <th>Thông tin mua hàng</th>
                    <td>
                      @if ($linkedCustomer && $customerProfile)
                        <div>Tổng chi đã tích lũy: <strong>{{ number_format((float) $customerProfile->total_spent, 0, ',', '.') }}đ</strong></div>
                        <div class="small text-muted">Tổng đơn đã xác minh: {{ number_format((int) $customerProfile->total_orders) }} | Hạng: {{ $tierLabels[$customerProfile->tier ?? 'new'] ?? ($customerProfile->tier ?? 'Khách hàng mới') }}</div>
                      @elseif ($linkedCustomer)
                        <span class="small text-muted">Đã liên kết khách hàng, chưa có dữ liệu tổng hợp.</span>
                      @else
                        <span class="small text-muted">Hồ sơ khách hàng sẽ được tạo khi bấm Đã xác minh.</span>
                      @endif
                    </td>
                  </tr>
                  <tr>
                    <th>Tổng tiền</th>
                    <td class="fw-bold text-primary">{{ number_format((float) $order->total_amount, 0, ',', '.') }}đ</td>
                  </tr>
                  <tr>
                    <th>Ghi chú</th>
                    <td>{{ $order->note ?: '-' }}</td>
                  </tr>
                  <tr>
                    <th>Ngày tạo</th>
                    <td>{{ optional($order->created_at)->format('d/m/Y H:i') }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-7">
        <div class="card order-detail-card mb-3">
          <div class="card-header">
            <h3 class="card-title mb-0">Tóm tắt soạn hàng</h3>
          </div>
          <div class="card-body">
            <div class="pick-summary-grid">
              <div class="pick-summary-card">
                <span>Số dòng sản phẩm</span>
                <strong>{{ number_format($totalLines) }}</strong>
              </div>
              <div class="pick-summary-card">
                <span>Tổng số lượng cần lấy</span>
                <strong>{{ number_format($totalUnits) }}</strong>
              </div>
              <div class="pick-summary-card">
                <span>Giá trị đơn</span>
                <strong>{{ number_format((float) $order->total_amount, 0, ',', '.') }}đ</strong>
              </div>
            </div>
          </div>
        </div>

        <div class="card order-detail-card">
          <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h3 class="card-title mb-0">Sản phẩm trong đơn</h3>
            <span class="pick-badge"><i class="bi bi-box-seam"></i> Ưu tiên soạn theo bảng này</span>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0 order-items-table">
                <thead>
                  <tr>
                    <th style="width: 54px;">Lấy</th>
                    <th>Sản phẩm</th>
                    <th>SKU</th>
                    <th>Màu</th>
                    <th>Size</th>
                    <th>Số lượng</th>
                    <th>Đơn giá</th>
                    <th>Thành tiền</th>
                    <th>Chi tiết</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($pickList as $item)
                    <tr>
                      <td><input class="form-check-input pick-checkbox" type="checkbox" value="{{ $item->id }}"></td>
                      <td>
                        <div class="d-flex align-items-center gap-3">
                          @if (!empty($item->product_id))
                            <a href="{{ route('backend.products.show', $item->product_id) }}" class="text-decoration-none" target="_blank" rel="noopener">
                              @if ($item->image_url)
                                <img src="{{ $item->image_url }}" alt="{{ $item->product_name }}" class="pick-thumb">
                              @else
                                <span class="pick-thumb-placeholder"><i class="bi bi-image"></i></span>
                              @endif
                            </a>
                          @elseif ($item->image_url)
                            <img src="{{ $item->image_url }}" alt="{{ $item->product_name }}" class="pick-thumb">
                          @else
                            <span class="pick-thumb-placeholder"><i class="bi bi-image"></i></span>
                          @endif
                          <div>
                            <div class="order-product-name fw-semibold">
                              @if (!empty($item->product_id))
                                <a href="{{ route('backend.products.show', $item->product_id) }}" class="text-decoration-none" target="_blank" rel="noopener">{{ $item->product_name }}</a>
                              @else
                                {{ $item->product_name }}
                              @endif
                            </div>
                            <div class="small text-muted mt-1">Biến thể: {{ $item->variant }}</div>
                          </div>
                        </div>
                      </td>
                      <td>{{ $item->sku }}</td>
                      <td>{{ $item->color }}</td>
                      <td>{{ $item->size }}</td>
                      <td><span class="pick-qty">{{ $item->qty }}</span></td>
                      <td>{{ number_format((float) $item->unit_price, 0, ',', '.') }}đ</td>
                      <td class="fw-bold">{{ number_format((float) $item->line_total, 0, ',', '.') }}đ</td>
                      <td>
                        @if (!empty($item->product_id))
                          <a href="{{ route('backend.products.show', $item->product_id) }}" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener">Xem SP</a>
                        @else
                          <span class="text-muted small">-</span>
                        @endif
                      </td>
                    </tr>
                  @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">Không có dữ liệu sản phẩm.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>

        @if ($payments->isNotEmpty())
          <div class="card order-detail-card mt-3">
            <div class="card-header">
              <h3 class="card-title mb-0">Lịch sử thanh toán</h3>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-sm table-striped align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Phương thức</th>
                      <th>Mã giao dịch</th>
                      <th>Số tiền</th>
                      <th>Trạng thái</th>
                      <th>Thời gian</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($payments as $payment)
                      <tr>
                        <td>{{ $payment->payment_method }}</td>
                        <td>{{ $payment->transaction_code ?: '-' }}</td>
                        <td>{{ number_format((float) $payment->amount, 0, ',', '.') }}đ</td>
                        <td>{{ $payment->status }}</td>
                        <td>{{ $payment->paid_at ?: '-' }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
