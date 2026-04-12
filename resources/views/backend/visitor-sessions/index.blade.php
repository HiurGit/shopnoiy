@extends('backend.layouts.app')

@section('title', 'Khách đang online')

@section('content')
<div class="app-content-header">
  <div class="container-fluid">
    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
      <h1 class="mb-0">Khách đang online</h1>
      <form method="GET" action="{{ route('backend.visitor-sessions') }}" class="d-flex gap-2">
        <input type="text" class="form-control" name="q" value="{{ $query }}" placeholder="Tìm theo hoạt động, sản phẩm, từ khóa, mã khách..." style="min-width: 320px;">
        <button type="submit" class="btn btn-primary">Lọc</button>
      </form>
    </div>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">
    <div class="row g-3 mb-3">
      <div class="col-md-3">
        <div class="card h-100"><div class="card-body"><div class="text-secondary small">Online lúc này</div><div class="fs-3 fw-bold">{{ number_format($summary['online_now']) }}</div></div></div>
      </div>
      <div class="col-md-3">
        <div class="card h-100"><div class="card-body"><div class="text-secondary small">Hoạt động {{ $activeWithinMinutes }} phút</div><div class="fs-3 fw-bold">{{ number_format($summary['active_15m']) }}</div></div></div>
      </div>
      <div class="col-md-3">
        <div class="card h-100"><div class="card-body"><div class="text-secondary small">Đang checkout</div><div class="fs-3 fw-bold">{{ number_format($summary['in_checkout']) }}</div></div></div>
      </div>
      <div class="col-md-3">
        <div class="card h-100"><div class="card-body"><div class="text-secondary small">Có sản phẩm trong giỏ</div><div class="fs-3 fw-bold">{{ number_format($summary['with_cart']) }}</div></div></div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h3 class="card-title mb-0">Hoạt động khách chưa đăng nhập</h3>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>Trạng thái</th>
                <th>Khách</th>
                <th>Đang làm gì</th>
                <th>Sản phẩm quan tâm</th>
                <th>Giỏ hàng</th>
                <th>Truy cập đầu</th>
                <th>Hoạt động cuối</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($visitors as $visitor)
                @php
                  $isOnlineNow = optional($visitor->last_seen_at)->greaterThanOrEqualTo(now()->subMinutes(2));
                  $meta = is_array($visitor->meta_json) ? $visitor->meta_json : [];
                  $productName = trim((string) ($meta['product_name'] ?? ''));
                  $searchQuery = trim((string) ($meta['search_query'] ?? ''));
                  $customerPhone = trim((string) ($meta['customer_phone'] ?? ''));
                  $customerName = trim((string) ($meta['customer_name'] ?? ''));
                  $customerLabel = $customerPhone !== '' ? $customerPhone : \Illuminate\Support\Str::limit($visitor->visitor_token, 12, '...');
                  $interestLabel = $productName !== ''
                    ? $productName
                    : ($searchQuery !== '' ? 'Tìm: ' . $searchQuery : 'Chưa rõ');
                  $interestHint = $productName !== ''
                    ? 'Ưu tiên từ hành vi xem hoặc thêm vào giỏ'
                    : ($searchQuery !== '' ? 'Suy ra từ từ khóa khách đang tìm' : '-');
                @endphp
                <tr>
                  <td>
                    <span class="badge {{ $isOnlineNow ? 'bg-success' : 'bg-secondary' }}">
                      {{ $isOnlineNow ? 'Đang online' : 'Vừa rời đi' }}
                    </span>
                  </td>
                  <td>
                    <div class="fw-semibold">{{ $customerLabel }}</div>
                    @if ($customerName !== '')
                      <div class="small text-secondary">{{ $customerName }}</div>
                    @endif
                    <div class="small text-secondary">{{ $visitor->ip_address ?: '-' }}</div>
                  </td>
                  <td>
                    <div class="fw-semibold">{{ $visitor->activity_label ?: 'Đang xem website' }}</div>
                    @if ($visitor->current_url)
                      <a href="{{ $visitor->current_url }}" target="_blank" rel="noopener" class="small">{{ \Illuminate\Support\Str::limit($visitor->current_path ?: $visitor->current_url, 52) }}</a>
                    @endif
                  </td>
                  <td>
                    <div class="fw-semibold">{{ $interestLabel }}</div>
                    <div class="small text-secondary">{{ $interestHint }}</div>
                  </td>
                  <td>
                    <div>{{ number_format((int) $visitor->cart_count) }} món</div>
                    <div class="small text-secondary">{{ number_format((int) $visitor->cart_value) }}đ</div>
                  </td>
                  <td>{{ optional($visitor->first_seen_at)->format('d/m H:i:s') ?: '-' }}</td>
                  <td>{{ optional($visitor->last_seen_at)->diffForHumans() ?: '-' }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center text-secondary">Chưa có khách nào hoạt động trong {{ $activeWithinMinutes }} phút gần đây.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
