@extends('backend.layouts.app')

@section('title', 'Banner')

@section('content')
<div class="app-content-header">
  <div class="container-fluid d-flex justify-content-between align-items-center">
    <h1 class="mb-0">Quản lý banner</h1>
    <a href="{{ route('backend.banners.create') }}" class="btn btn-primary btn-sm">Thêm banner</a>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">
    @if (session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
      <div class="card-body">
        <table class="table table-striped table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Vị trí</th>
              <th>Tiêu đề</th>
              <th>Ảnh</th>
              <th>Link</th>
              <th>Thứ tự</th>
              <th>Trạng thái</th>
              <th>Thao tác</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($banners as $banner)
              <tr>
                <td>{{ $banner->id }}</td>
                <td>{{ $positions[$banner->section_id] ?? $banner->section_id }}</td>
                <td>{{ $banner->title }}</td>
                <td>{{ $banner->image_url }}</td>
                <td>{{ $banner->target_url }}</td>
                <td>{{ $banner->sort_order }}</td>
                <td>{{ $banner->is_active ? 'Bật' : 'Tắt' }}</td>
                <td>
                  <a href="{{ route('backend.banners.show', $banner) }}" class="btn btn-info btn-sm text-white">Xem</a>
                  <a href="{{ route('backend.banners.edit', $banner) }}" class="btn btn-outline-primary btn-sm">Sửa</a>
                  <form method="POST" action="{{ route('backend.banners.destroy', $banner) }}" class="d-inline" onsubmit="return confirm('Xóa banner này?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">Xóa</button>
                  </form>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-center">Chưa có banner.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

