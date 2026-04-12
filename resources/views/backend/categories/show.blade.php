@extends('backend.layouts.app')

@section('title', 'Chi tiết danh mục')

@section('content')
<div class="app-content-header">
  <div class="container-fluid d-flex justify-content-between align-items-center">
    <h1 class="mb-0">Chi tiết danh mục</h1>
    <div class="d-flex gap-2">
      <a href="{{ route('backend.categories.edit', $category) }}" class="btn btn-outline-primary btn-sm">Sửa</a>
      <a href="{{ route('backend.categories') }}" class="btn btn-secondary btn-sm">Quay lại</a>
    </div>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title mb-0">{{ $category->name }}</h3>
      </div>
      <div class="card-body">
        <table class="table table-bordered mb-0">
          <tbody>
            <tr><th style="width: 220px;">ID</th><td>{{ $category->id }}</td></tr>
            <tr><th>Tên danh mục</th><td>{{ $category->name }}</td></tr>
            <tr><th>Slug</th><td>{{ $category->slug }}</td></tr>
            <tr><th>Loại</th><td>{{ $levelLabel }}</td></tr>
            <tr><th>Cấp</th><td>{{ $currentLevel }}</td></tr>
            <tr><th>Đường dẫn cây</th><td>{{ $currentPath }}</td></tr>
            <tr><th>Danh mục cha</th><td>{{ $category->parent?->name ?? '-' }}</td></tr>
            <tr><th>Đối tượng</th><td>{{ $category->target?->name ?? '-' }}</td></tr>
            <tr><th>Thứ tự</th><td>{{ $category->sort_order }}</td></tr>
            <tr><th>Trạng thái</th><td>{{ $category->status === 'active' ? 'Hoạt động' : 'Ngừng hoạt động' }}</td></tr>
            <tr><th>Icon class</th><td>{{ $category->icon_class ?: '-' }}</td></tr>
            <tr>
              <th>Hình ảnh</th>
              <td>
                @if ($category->image_url)
                  <div class="mb-2">
                    <img src="{{ asset($category->image_url) }}" alt="{{ $category->name }}" style="height: 120px; width: auto; border-radius: 8px;">
                  </div>
                  <div>{{ $category->image_url }}</div>
                @else
                  -
                @endif
              </td>
            </tr>
            <tr><th>Mô tả</th><td>{{ $category->description ?: '-' }}</td></tr>
            <tr><th>Số sản phẩm gắn danh mục</th><td>{{ $productCount }}</td></tr>
            <tr><th>Tạo lúc</th><td>{{ $category->created_at }}</td></tr>
            <tr><th>Cập nhật lúc</th><td>{{ $category->updated_at }}</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    @if ($children->isNotEmpty())
      <div class="card mt-3">
        <div class="card-header">
          <h3 class="card-title mb-0">Danh mục con trực tiếp của {{ $category->name }}</h3>
        </div>
        <div class="card-body">
          <table class="table table-striped table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>ID</th>
                <th>Tên</th>
                <th>Slug</th>
                <th>Thứ tự</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($children as $child)
                <tr>
                  <td>{{ $child->id }}</td>
                  <td>{{ $child->name }}</td>
                  <td>{{ $child->slug }}</td>
                  <td>{{ $child->sort_order }}</td>
                  <td>{{ $child->status === 'active' ? 'Hoạt động' : 'Ngừng hoạt động' }}</td>
                  <td>
                    <a href="{{ route('backend.categories.show', $child) }}" class="btn btn-info btn-sm text-white">Xem</a>
                    <a href="{{ route('backend.categories.edit', $child) }}" class="btn btn-outline-primary btn-sm">Sửa</a>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    @endif
  </div>
</div>
@endsection
