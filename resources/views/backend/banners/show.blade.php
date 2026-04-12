@php
  $imagePreviewUrl = $banner->image_url
    ? (str_starts_with($banner->image_url, 'http://') || str_starts_with($banner->image_url, 'https://') ? $banner->image_url : asset($banner->image_url))
    : null;
@endphp

@extends('backend.layouts.app')

@section('title', 'Chi tiet banner')

@section('content')
<div class="app-content-header">
  <div class="container-fluid d-flex justify-content-between align-items-center">
    <h1 class="mb-0">Chi tiet banner</h1>
    <a href="{{ route('backend.banners') }}" class="btn btn-secondary btn-sm">Quay lai</a>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-body">
        <table class="table table-bordered mb-0">
          <tbody>
            <tr><th style="width: 220px;">ID</th><td>{{ $banner->id }}</td></tr>
            <tr><th>Vi tri</th><td>{{ $position->title ?? '-' }} ({{ $position->section_key ?? '-' }})</td></tr>
            <tr><th>Tieu de</th><td>{{ $banner->title }}</td></tr>
            <tr><th>Phu de</th><td>{{ $banner->subtitle }}</td></tr>
            <tr>
              <th>Anh URL</th>
              <td>
                <div>{{ $banner->image_url }}</div>
                @if ($imagePreviewUrl)
                  <div class="mt-2">
                    <img src="{{ $imagePreviewUrl }}" alt="{{ $banner->title ?: 'Banner' }}" style="max-height: 180px; width: auto; border-radius: 8px; object-fit: cover;">
                  </div>
                @endif
              </td>
            </tr>
            <tr><th>Link dich</th><td>{{ $banner->target_url }}</td></tr>
            <tr><th>Thu tu</th><td>{{ $banner->sort_order }}</td></tr>
            <tr><th>Trang thai</th><td>{{ $banner->is_active ? 'Bat' : 'Tat' }}</td></tr>
            <tr><th>Bat dau</th><td>{{ $banner->start_at }}</td></tr>
            <tr><th>Ket thuc</th><td>{{ $banner->end_at }}</td></tr>
            <tr><th>Meta JSON</th><td>{{ $banner->meta_json }}</td></tr>
            <tr><th>Tao luc</th><td>{{ $banner->created_at }}</td></tr>
            <tr><th>Cap nhat luc</th><td>{{ $banner->updated_at }}</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
