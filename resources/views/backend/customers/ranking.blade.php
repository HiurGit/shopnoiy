@extends('backend.layouts.app')

@section('title', 'Xếp hạng mua hàng')

@php
  $metricLabels = [
      'total_spent' => 'Tổng chi tiêu',
      'total_orders' => 'Tổng số đơn',
      'recent_spent' => 'Chi tiêu gần đây',
      'recent_orders' => 'Đơn gần đây',
  ];

  $tierLabels = [
      'new' => 'Khách hàng mới',
      'friendly' => 'Khách hàng thân thiện',
      'loyal' => 'Khách hàng trung thành',
      'vip' => 'Khách hàng VIP',
      'diamond' => 'Khách hàng Kim cương',
  ];

  $periodLabel = match ($period) {
      '30' => '30 ngày gần nhất',
      '90' => '90 ngày gần nhất',
      default => 'Toàn thời gian',
  };

  $tierBadgeClass = function (string $value): string {
      return match ($value) {
          'diamond' => 'bg-primary-subtle text-primary-emphasis',
          'vip' => 'bg-warning-subtle text-warning-emphasis',
          'loyal' => 'bg-success-subtle text-success-emphasis',
          'friendly' => 'bg-secondary-subtle text-secondary-emphasis',
          default => 'bg-info-subtle text-info-emphasis',
      };
  };

  $metricValue = function ($customer) use ($sort) {
      return match ($sort) {
          'total_orders' => (int) $customer->total_orders,
          'recent_spent' => (float) $customer->recent_spent,
          'recent_orders' => (int) $customer->recent_orders,
          default => (float) $customer->total_spent,
      };
  };

  $formatMetric = function ($customer) use ($sort, $metricValue) {
      $value = $metricValue($customer);

      return in_array($sort, ['total_spent', 'recent_spent'], true)
          ? number_format((float) $value, 0, ',', '.') . 'đ'
          : number_format((int) $value, 0, ',', '.') . ' đơn';
  };
@endphp

@section('content')
<style>
  .ranking-hero {
    background: linear-gradient(135deg, #16253a 0%, #23405f 46%, #c89535 100%);
    color: #fff;
    border-radius: 20px;
    padding: 22px;
    box-shadow: 0 18px 40px rgba(22, 37, 58, .18);
  }
  .ranking-hero p {
    color: rgba(255,255,255,.78);
  }
  .ranking-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border-radius: 999px;
    background: rgba(255,255,255,.12);
    color: #fff;
    font-size: .85rem;
    font-weight: 600;
  }
  .ranking-filter-card,
  .ranking-summary-card,
  .ranking-podium-card,
  .ranking-table-card {
    border: 0;
    border-radius: 18px;
    box-shadow: 0 12px 28px rgba(15, 23, 42, .06);
  }
  .ranking-summary-card .metric {
    font-size: 1.55rem;
    font-weight: 800;
    color: #16253a;
  }
  .ranking-summary-card .label {
    color: #64748b;
    font-size: .92rem;
  }
  .ranking-podium {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
  }
  .ranking-podium-card {
    position: relative;
    overflow: hidden;
  }
  .ranking-podium-card::before {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(255,255,255,.72), rgba(255,255,255,0));
    pointer-events: none;
  }
  .ranking-podium-card.is-first {
    background: linear-gradient(180deg, #fff8df 0%, #fff1bf 100%);
  }
  .ranking-podium-card.is-second {
    background: linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
  }
  .ranking-podium-card.is-third {
    background: linear-gradient(180deg, #fff3eb 0%, #ffe1d1 100%);
  }
  .ranking-medal {
    width: 46px;
    height: 46px;
    border-radius: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    font-weight: 800;
    margin-bottom: 14px;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.65);
  }
  .ranking-medal.is-first { background: #f3c968; color: #6f4d00; }
  .ranking-medal.is-second { background: #d8dee7; color: #3f4d5d; }
  .ranking-medal.is-third { background: #efb08a; color: #6f3517; }
  .ranking-name {
    font-size: 1.02rem;
    font-weight: 700;
    color: #16253a;
  }
  .ranking-sub {
    color: #64748b;
    font-size: .9rem;
  }
  .ranking-strong {
    color: #16253a;
    font-size: 1.2rem;
    font-weight: 800;
  }
  .ranking-progress {
    height: 8px;
    border-radius: 999px;
    background: #e9eef5;
    overflow: hidden;
  }
  .ranking-progress-bar {
    height: 100%;
    border-radius: 999px;
    background: linear-gradient(90deg, #23405f, #d0a24a);
  }
  @media (max-width: 991.98px) {
    .ranking-podium {
      grid-template-columns: 1fr;
    }
  }
</style>

<div class="app-content-header">
  <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      <h1 class="mb-0">Xếp hạng mua hàng</h1>
      <p class="text-secondary mb-0">Theo dõi nhóm khách chi tiêu mạnh và tần suất mua hàng theo từng giai đoạn.</p>
    </div>
    <a href="{{ route('backend.customers') }}" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-people me-1"></i>Về danh sách khách hàng
    </a>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">
    <section class="ranking-hero mb-3">
      <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
          <span class="ranking-pill"><i class="bi bi-trophy"></i> Leaderboard khách hàng</span>
          <h2 class="mt-3 mb-2">Ưu tiên giữ chân nhóm khách mua mạnh nhất của shop</h2>
          <p class="mb-0">Bạn có thể nhìn nhanh ai đang mang doanh thu lớn, ai mua đều và nhóm nào cần chăm sóc thêm để tăng quay lại.</p>
        </div>
        <div class="d-flex flex-column gap-2">
          <span class="ranking-pill"><i class="bi bi-bar-chart"></i> Tiêu chí: {{ $metricLabels[$sort] }}</span>
          <span class="ranking-pill"><i class="bi bi-calendar3"></i> Phạm vi: {{ $periodLabel }}</span>
        </div>
      </div>
    </section>

    <div class="card ranking-filter-card mb-3">
      <div class="card-body">
        <form method="GET" action="{{ route('backend.customers.ranking') }}" class="row g-3 align-items-end">
          <div class="col-12 col-md-6 col-xl-3">
            <label class="form-label">Tìm khách hàng</label>
            <input type="text" name="q" class="form-control" value="{{ $keyword }}" placeholder="Tên, email hoặc số điện thoại">
          </div>
          <div class="col-6 col-md-3 col-xl-2">
            <label class="form-label">Xếp theo</label>
            <select name="sort" class="form-select">
              <option value="total_spent" {{ $sort === 'total_spent' ? 'selected' : '' }}>Tổng chi tiêu</option>
              <option value="total_orders" {{ $sort === 'total_orders' ? 'selected' : '' }}>Tổng số đơn</option>
              <option value="recent_spent" {{ $sort === 'recent_spent' ? 'selected' : '' }}>Chi tiêu gần đây</option>
              <option value="recent_orders" {{ $sort === 'recent_orders' ? 'selected' : '' }}>Đơn gần đây</option>
            </select>
          </div>
          <div class="col-6 col-md-3 col-xl-2">
            <label class="form-label">Rank khách</label>
            <select name="tier" class="form-select">
              <option value="all" {{ $tier === 'all' ? 'selected' : '' }}>Tất cả</option>
              <option value="diamond" {{ $tier === 'diamond' ? 'selected' : '' }}>Khách hàng Kim cương</option>
              <option value="vip" {{ $tier === 'vip' ? 'selected' : '' }}>Khách hàng VIP</option>
              <option value="loyal" {{ $tier === 'loyal' ? 'selected' : '' }}>Khách hàng trung thành</option>
              <option value="friendly" {{ $tier === 'friendly' ? 'selected' : '' }}>Khách hàng thân thiện</option>
              <option value="new" {{ $tier === 'new' ? 'selected' : '' }}>Khách hàng mới</option>
            </select>
          </div>
          <div class="col-6 col-md-3 col-xl-2">
            <label class="form-label">Thời gian</label>
            <select name="period" class="form-select">
              <option value="all" {{ $period === 'all' ? 'selected' : '' }}>Toàn thời gian</option>
              <option value="30" {{ $period === '30' ? 'selected' : '' }}>30 ngày</option>
              <option value="90" {{ $period === '90' ? 'selected' : '' }}>90 ngày</option>
            </select>
          </div>
          <div class="col-6 col-md-3 col-xl-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Lọc xếp hạng</button>
            <a href="{{ route('backend.customers.ranking') }}" class="btn btn-outline-secondary">Đặt lại</a>
          </div>
        </form>
      </div>
    </div>

    <div class="row g-3 mb-3">
      <div class="col-12 col-md-6 col-xl-3">
        <div class="card ranking-summary-card h-100">
          <div class="card-body">
            <div class="label">Khách trong bảng lọc</div>
            <div class="metric">{{ number_format($summary['customers_count']) }}</div>
            <div class="small text-secondary">Kim cương: {{ number_format($summary['diamond_count']) }} | VIP: {{ number_format($summary['vip_count']) }} | Trung thành: {{ number_format($summary['loyal_count']) }} | Thân thiện: {{ number_format($summary['friendly_count']) }} | Mới: {{ number_format($summary['new_count']) }}</div>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6 col-xl-3">
        <div class="card ranking-summary-card h-100">
          <div class="card-body">
            <div class="label">Tổng chi tiêu hồ sơ</div>
            <div class="metric">{{ number_format($summary['total_spent'], 0, ',', '.') }}đ</div>
            <div class="small text-secondary">{{ number_format($summary['total_orders']) }} đơn tích lũy</div>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6 col-xl-3">
        <div class="card ranking-summary-card h-100">
          <div class="card-body">
            <div class="label">Phát sinh trong kỳ lọc</div>
            <div class="metric">{{ number_format($summary['recent_spent'], 0, ',', '.') }}đ</div>
            <div class="small text-secondary">{{ number_format($summary['recent_orders']) }} đơn trong {{ strtolower($periodLabel) }}</div>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6 col-xl-3">
        <div class="card ranking-summary-card h-100">
          <div class="card-body">
            <div class="label">Mốc cao nhất hiện tại</div>
            <div class="metric">
              @if (in_array($sort, ['total_spent', 'recent_spent'], true))
                {{ number_format($summary['top_value'], 0, ',', '.') }}đ
              @else
                {{ number_format($summary['top_value']) }} đơn
              @endif
            </div>
            <div class="small text-secondary">{{ $metricLabels[$sort] }}</div>
          </div>
        </div>
      </div>
    </div>

    <div class="card ranking-podium-card mb-3">
      <div class="card-header bg-transparent border-0 pt-4 px-4">
        <h3 class="card-title mb-0">Top khách nổi bật</h3>
      </div>
      <div class="card-body pt-2">
        <div class="ranking-podium">
          @forelse ($podiumCustomers as $index => $customer)
            <div class="card ranking-podium-card {{ $index === 0 ? 'is-first' : ($index === 1 ? 'is-second' : 'is-third') }}">
              <div class="card-body p-4">
                <div class="ranking-medal {{ $index === 0 ? 'is-first' : ($index === 1 ? 'is-second' : 'is-third') }}">
                  #{{ $index + 1 }}
                </div>
                <div class="ranking-name">{{ $customer->full_name ?: 'Khách #' . $customer->id }}</div>
                <div class="ranking-sub mt-1">{{ $customer->email ?: ($customer->phone ?: 'Chưa có thông tin liên hệ') }}</div>
                <div class="d-flex gap-2 flex-wrap mt-3">
                  <span class="badge rounded-pill {{ $tierBadgeClass($customer->tier) }}">{{ $tierLabels[$customer->tier] ?? ucfirst($customer->tier) }}</span>
                  <span class="badge rounded-pill bg-dark-subtle text-dark-emphasis">{{ ucfirst($customer->status) }}</span>
                </div>
                <div class="ranking-strong mt-4">{{ $formatMetric($customer) }}</div>
                <div class="small text-secondary mt-1">Tổng chi: {{ number_format((float) $customer->total_spent, 0, ',', '.') }}đ | Tổng đơn: {{ number_format((int) $customer->total_orders) }}</div>
                <a href="{{ route('backend.customers.show', $customer->id) }}" class="btn btn-sm btn-outline-dark mt-3">Xem hồ sơ</a>
              </div>
            </div>
          @empty
            <div class="text-center text-secondary py-5">Chưa có dữ liệu khách hàng để xếp hạng.</div>
          @endforelse
        </div>
      </div>
    </div>

    <div class="card ranking-table-card">
      <div class="card-header bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h3 class="card-title mb-0">Bảng xếp hạng chi tiết</h3>
        <span class="text-secondary small">Hiển thị {{ $leaderboard->firstItem() ?? 0 }}-{{ $leaderboard->lastItem() ?? 0 }} / {{ $leaderboard->total() }} khách hàng</span>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th style="width: 90px;">Hạng</th>
                <th>Khách hàng</th>
                <th>Rank</th>
                <th>Tổng chi</th>
                <th>Tổng đơn</th>
                <th>Gần đây</th>
                <th style="width: 220px;">Mức đóng góp</th>
                <th>Thao tác</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($leaderboard as $customer)
                @php
                  $rank = ($leaderboard->firstItem() ?? 1) + $loop->index;
                  $value = $metricValue($customer);
                  $ratio = $summary['top_value'] > 0 ? max(6, min(100, round(($value / $summary['top_value']) * 100))) : 0;
                @endphp
                <tr>
                  <td>
                    <div class="fw-bold text-dark">#{{ $rank }}</div>
                    @if ($rank <= 3)
                      <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis">Top {{ $rank }}</span>
                    @endif
                  </td>
                  <td>
                    <div class="fw-semibold">{{ $customer->full_name ?: 'Khách #' . $customer->id }}</div>
                    <div class="small text-secondary">{{ $customer->email ?: 'Không có email' }}</div>
                    <div class="small text-secondary">{{ $customer->phone ?: 'Không có số điện thoại' }}</div>
                  </td>
                  <td><span class="badge rounded-pill {{ $tierBadgeClass($customer->tier) }}">{{ $tierLabels[$customer->tier] ?? ucfirst($customer->tier) }}</span></td>
                  <td>
                    <div class="fw-semibold">{{ number_format((float) $customer->total_spent, 0, ',', '.') }}đ</div>
                    <div class="small text-secondary">Tích lũy</div>
                  </td>
                  <td>
                    <div class="fw-semibold">{{ number_format((int) $customer->total_orders) }} đơn</div>
                    <div class="small text-secondary">Toàn thời gian</div>
                  </td>
                  <td>
                    <div class="fw-semibold">{{ number_format((float) $customer->recent_spent, 0, ',', '.') }}đ</div>
                    <div class="small text-secondary">{{ number_format((int) $customer->recent_orders) }} đơn / {{ strtolower($periodLabel) }}</div>
                  </td>
                  <td>
                    <div class="small text-secondary mb-2">{{ $metricLabels[$sort] }}: {{ $formatMetric($customer) }}</div>
                    <div class="ranking-progress">
                      <div class="ranking-progress-bar" style="width: {{ $ratio }}%;"></div>
                    </div>
                  </td>
                  <td><a href="{{ route('backend.customers.show', $customer->id) }}" class="btn btn-info btn-sm text-white">Xem</a></td>
                </tr>
              @empty
                <tr>
                  <td colspan="8" class="text-center py-5 text-secondary">Không tìm thấy khách hàng phù hợp với bộ lọc hiện tại.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="mt-3">{{ $leaderboard->links() }}</div>
      </div>
    </div>
  </div>
</div>
@endsection
