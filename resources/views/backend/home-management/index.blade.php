@extends('backend.layouts.app')

@section('title', 'Quản lý trang chủ')

@section('content')
<div class="app-content-header">
  <div class="container-fluid">
    <h1 class="mb-0">Quản lý trang chủ</h1>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">
    @if (session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card mb-3">
      <div class="card-header">
        <h3 class="card-title mb-0">Hiển thị section</h3>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>Section</th>
                <th>Key</th>
                <th>Loại</th>
                <th>Sort</th>
                <th>Hiển thị</th>
                <th>Thao tác</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($sectionStates as $sectionState)
                <tr>
                  <td>{{ $sectionState->title }}</td>
                  <td><code>{{ $sectionState->section_key }}</code></td>
                  <td>{{ $sectionState->section_type }}</td>
                  <td>{{ $sectionState->sort_order }}</td>
                  <td>{{ $sectionState->is_active ? 'Bật' : 'Tắt' }}</td>
                  <td>
                    <form method="POST" action="{{ route('backend.home-management.sections.visibility', $sectionState->id) }}" class="d-inline">
                      @csrf
                      @method('PATCH')
                      <input type="hidden" name="is_active" value="{{ $sectionState->is_active ? 0 : 1 }}">
                      <button type="submit" class="btn btn-sm {{ $sectionState->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }}">
                        {{ $sectionState->is_active ? 'Ẩn section' : 'Hiện section' }}
                      </button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center">Chưa có section.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Mục trang chủ</h3>
        <a href="{{ route('backend.home-management.create') }}" class="btn btn-primary btn-sm ms-auto">Thêm mới</a>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>ID</th>
                <th>Section</th>
                <th>Loại</th>
                <th>Tiêu đề</th>
                <th>URL</th>
                <th>Active</th>
                <th>Thao tác</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($items as $item)
                <tr>
                  <td>{{ $item->id }}</td>
                  <td>{{ $sections[$item->section_id] ?? $item->section_id }}</td>
                  <td>{{ $item->item_type }}</td>
                  <td>{{ $item->title }}</td>
                  <td>{{ $item->target_url }}</td>
                  <td>{{ $item->is_active ? 'Yes' : 'No' }}</td>
                  <td>
                    <a href="{{ route('backend.home-management.show', $item) }}" class="btn btn-info btn-sm text-white">Xem</a>
                    <a href="{{ route('backend.home-management.edit', $item) }}" class="btn btn-outline-primary btn-sm">Sửa</a>
                    <form method="POST" action="{{ route('backend.home-management.destroy', $item) }}" class="d-inline" onsubmit="return confirm('Xóa item này?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-outline-danger btn-sm">Xóa</button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center">Chưa có dữ liệu.</td>
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
