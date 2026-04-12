@extends('backend.layouts.app')

@section('title', 'Nhật ký hoạt động')

@section('content')
<div class="app-content-header">
  <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h1 class="mb-0">Nhật ký hoạt động</h1>
    <span class="text-secondary small">Theo dõi ai đã thêm, sửa, xóa hoặc thao tác gì trong backend.</span>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">
    <div class="card mb-3">
      <div class="card-body">
        <form method="GET" action="{{ route('backend.activity-logs') }}" class="row g-2">
          <div class="col-12 col-md-6">
            <input
              type="text"
              name="q"
              class="form-control"
              placeholder="Tìm theo tên, email, mô tả, route..."
              value="{{ request('q') }}"
            >
          </div>
          <div class="col-12 col-md-3">
            <select name="action" class="form-select">
              <option value="">Tất cả hành động</option>
              @foreach (['login' => 'Đăng nhập', 'logout' => 'Đăng xuất', 'create' => 'Tạo mới', 'update' => 'Cập nhật', 'delete' => 'Xóa'] as $value => $label)
                <option value="{{ $value }}" {{ request('action') === $value ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-12 col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-fill">Lọc</button>
            <a href="{{ route('backend.activity-logs') }}" class="btn btn-outline-secondary">Reset</a>
          </div>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-body table-responsive">
        <table class="table table-striped table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>Thời gian</th>
              <th>Người dùng</th>
              <th>Hành động</th>
              <th>Mô tả</th>
              <th>Đối tượng</th>
              <th>Route</th>
              <th>IP</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($logs as $log)
              <tr>
                <td class="text-nowrap">{{ $log->created_at?->format('d/m/Y H:i:s') }}</td>
                <td>
                  <div class="fw-semibold">{{ $log->user_name ?: 'Không xác định' }}</div>
                  <div class="small text-secondary">{{ $log->user_email }}</div>
                   
                </td>
                <td><span class="badge text-bg-secondary">{{ $log->action }}</span></td>
                <td style="min-width: 220px;">{{ $log->description }}</td>
                <td class="small">
                  @if ($log->subject_type)
                    <div>{{ $log->subject_type }}</div>
                    <div class="text-secondary">#{{ $log->subject_id }}</div>
                  @else
                    <span class="text-secondary">-</span>
                  @endif
                </td>
                <td class="small text-secondary">{{ $log->route_name }}</td>
                <td class="small text-secondary">{{ $log->ip_address }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center text-secondary py-4">Chưa có nhật ký hoạt động.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if ($logs->hasPages())
        <div class="card-footer">
          {{ $logs->links() }}
        </div>
      @endif
    </div>
  </div>
</div>
@endsection
