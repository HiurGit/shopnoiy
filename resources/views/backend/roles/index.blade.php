@extends('backend.layouts.app')

@section('title', 'Quản lý role')

@section('content')
<div class="app-content-header">
  <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h1 class="mb-0">Quản lý role</h1>
    <span class="text-secondary small">Phân quyền theo role, mọi tài khoản cùng role sẽ dùng chung quyền.</span>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">
    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
      <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
      <div class="card-header">
        <h3 class="card-title mb-0">Danh sách role backend</h3>
      </div>
      <div class="card-body">
        @foreach($roles as $role)
          @php
            $rolePermissions = $role->normalizedPermissions();
          @endphp

          <form method="POST" action="{{ route('backend.roles.update', $role) }}" class="border rounded-3 p-3 mb-3">
            @csrf

            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
              <div>
                <h4 class="mb-1 fs-5">{{ $role->display_name }}</h4>
                <div class="text-secondary small">Key: <code>{{ $role->role_key }}</code></div>
              </div>
              @if ($role->role_key === 'admin')
                <span class="badge text-bg-primary">Toàn quyền</span>
              @elseif ($role->is_locked)
                <span class="badge text-bg-secondary">Khóa hệ thống</span>
              @else
                <span class="badge text-bg-success">Có thể chỉnh</span>
              @endif
            </div>

            <div class="row g-2">
              @foreach($permissionDefinitions as $permissionKey => $permissionDefinition)
                <div class="col-12 col-md-6 col-xl-4">
                  <label class="form-check border rounded-3 px-3 py-2 h-100 d-flex align-items-center gap-2">
                    <input
                      class="form-check-input mt-0"
                      type="checkbox"
                      name="permissions[]"
                      value="{{ $permissionKey }}"
                      {{ in_array($permissionKey, $rolePermissions, true) ? 'checked' : '' }}
                      {{ $role->role_key === 'admin' ? 'disabled' : '' }}
                    >
                    <span class="form-check-label">{{ $permissionDefinition['label'] }}</span>
                  </label>
                </div>
              @endforeach
            </div>

            @if ($role->role_key !== 'admin')
              <div class="mt-3 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Lưu quyền role</button>
              </div>
            @endif
          </form>
        @endforeach
      </div>
    </div>
  </div>
</div>
@endsection
