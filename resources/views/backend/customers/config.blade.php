@extends('backend.layouts.app')

@section('title', 'Cau hinh rank')

@section('content')
<style>
  .customer-config-shell {
    background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    padding: 18px;
  }
  .customer-config-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 18px;
    box-shadow: 0 10px 24px rgba(15, 23, 42, .04);
  }
  .customer-config-note {
    padding: 14px 16px;
    border-radius: 14px;
    background: linear-gradient(180deg, #eff6ff 0%, #e0ecff 100%);
    color: #1e3a5f;
    font-size: .94rem;
  }
</style>

<div class="app-content-header">
  <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      <h1 class="mb-0">Cau hinh rank</h1>
      <p class="text-secondary mb-0">Quan ly 5 cap rank khach hang dua theo tong chi tieu tich luy.</p>
    </div>
    <a href="{{ route('backend.customers') }}" class="btn btn-outline-secondary btn-sm">Quay lai khach hang</a>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">
    @if (session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('backend.customers.config.update') }}">
      @csrf
      <div class="customer-config-shell">
        <div class="customer-config-card">
          <div class="row g-3">
            @foreach ($fields as $key => $field)
              <div class="col-md-6">
                <label class="form-label">{{ $field['label'] }}</label>
                <input
                  type="number"
                  min="0"
                  step="1000"
                  class="form-control"
                  name="{{ $key }}"
                  value="{{ old($key, $settings[$key] ?? $field['default']) }}"
                >
                <div class="form-text">{{ $field['help'] }}</div>
              </div>
            @endforeach
            <div class="col-12">
              <div class="customer-config-note">
                Rank hien tai duoc tinh tu dong tu cac don hang da xac minh. Thu tu rank la: Khach hang moi, Khach hang than thien, Khach hang trung thanh, Khach hang VIP, Khach hang Kim cuong. Neu moc sau nho hon moc truoc, he thong se tu dong day len bang moc truoc de tranh sai logic.
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-3">
        <button type="submit" class="btn btn-primary">Luu cau hinh rank</button>
      </div>
    </form>
  </div>
</div>
@endsection
