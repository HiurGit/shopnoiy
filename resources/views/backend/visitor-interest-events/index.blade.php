@extends('backend.layouts.app')

@section('title', 'Sản phẩm được quan tâm')

@section('content')
<div class="app-content-header">
  <div class="container-fluid">
    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
      <h1 class="mb-0">Sản phẩm được quan tâm</h1>
      <form method="GET" action="{{ route('backend.visitor-interest-events') }}" class="d-flex gap-2 align-items-center">
        <label class="small text-secondary mb-0">Khoảng thời gian</label>
        <select name="days" class="form-select" style="width: 140px;">
          @foreach ([1, 3, 7, 14, 30] as $option)
            <option value="{{ $option }}" {{ $days === $option ? 'selected' : '' }}>{{ $option }} ngày</option>
          @endforeach
        </select>
        <button type="submit" class="btn btn-primary">Xem</button>
      </form>
    </div>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">
    <div class="row g-3 mb-3">
      <div class="col-md-4">
        <div class="card h-100"><div class="card-body"><div class="text-secondary small">Lượt xem chi tiết</div><div class="fs-3 fw-bold">{{ number_format($summary['views']) }}</div></div></div>
      </div>
      <div class="col-md-4">
        <div class="card h-100"><div class="card-body"><div class="text-secondary small">Số lượng thêm giỏ</div><div class="fs-3 fw-bold">{{ number_format((int) $summary['add_to_cart']) }}</div></div></div>
      </div>
      <div class="col-md-4">
        <div class="card h-100"><div class="card-body"><div class="text-secondary small">Khách quan tâm</div><div class="fs-3 fw-bold">{{ number_format($summary['unique_visitors']) }}</div></div></div>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-lg-6">
        <div class="card h-100">
          <div class="card-header"><h3 class="card-title mb-0">Top sản phẩm được quan tâm</h3></div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover align-middle mb-0">
                <thead>
                  <tr>
                    <th>Sản phẩm</th>
                    <th>Lượt xem</th>
                    <th>Thêm giỏ</th>
                    <th>Khách</th>
                    <th>Gần nhất</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($topProducts as $product)
                    <tr>
                      <td>
                        <div class="fw-semibold">{{ $product->product_name ?: 'Sản phẩm không rõ' }}</div>
                        @if (!empty($product->product_slug))
                          <div class="small text-secondary">{{ $product->product_slug }}</div>
                        @endif
                      </td>
                      <td>{{ number_format((int) $product->views_count) }}</td>
                      <td>{{ number_format((int) $product->add_to_cart_qty) }}</td>
                      <td>{{ number_format((int) $product->unique_visitors) }}</td>
                      <td>{{ \Illuminate\Support\Carbon::parse($product->last_interest_at)->diffForHumans() }}</td>
                    </tr>
                  @empty
                    <tr><td colspan="5" class="text-center text-secondary">Chưa có dữ liệu quan tâm sản phẩm.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card h-100">
          <div class="card-header"><h3 class="card-title mb-0">Lịch sử quan tâm gần đây</h3></div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover align-middle mb-0">
                <thead>
                  <tr>
                    <th>Khách</th>
                    <th>Hành vi</th>
                    <th>Sản phẩm</th>
                    <th>Thời gian</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($recentEvents as $event)
                    <tr>
                      <td><span class="fw-semibold">{{ \Illuminate\Support\Str::limit($event->visitor_token, 12, '...') }}</span></td>
                      <td>
                        @if ($event->event_type === 'add_to_cart')
                          <span class="badge bg-warning text-dark">Thêm giỏ</span>
                          <span class="small text-secondary">SL: {{ number_format((int) $event->qty) }}</span>
                        @else
                          <span class="badge bg-info text-dark">Xem chi tiết</span>
                        @endif
                      </td>
                      <td>
                        <div class="fw-semibold">{{ $event->product_name ?: 'Sản phẩm không rõ' }}</div>
                        @if (!empty($event->product_slug))
                          <div class="small text-secondary">{{ $event->product_slug }}</div>
                        @endif
                      </td>
                      <td>{{ optional($event->created_at)->diffForHumans() }}</td>
                    </tr>
                  @empty
                    <tr><td colspan="4" class="text-center text-secondary">Chưa có lịch sử quan tâm sản phẩm.</td></tr>
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
