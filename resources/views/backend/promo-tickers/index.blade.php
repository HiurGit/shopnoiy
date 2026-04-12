@extends('backend.layouts.app')

@section('title', 'Promo Ticker')

@section('content')
<div class="app-content-header">
  <div class="container-fluid">
    <h1 class="mb-0">Promo Ticker</h1>
  </div>
</div>
<div class="app-content">
  <div class="container-fluid">
    @if (session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Danh sach promo tickers</h3>
        <a href="{{ route('backend.promo-tickers.create') }}" class="btn btn-primary btn-sm ms-auto">Them moi</a>
      </div>
      <div class="card-body">
        <table class="table table-striped table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Ten</th>
              <th>Noi dung</th>
              <th>Toc do</th>
              <th>Trang thai</th>
              <th>Thao tac</th>
            </tr>
          </thead>
          <tbody>
            @forelse($promoTickers as $ticker)
              <tr>
                <td>{{ $ticker->id }}</td>
                <td>{{ $ticker->name }}</td>
                <td>{{ $ticker->content_text }}</td>
                <td>{{ $ticker->speed_seconds }}</td>
                <td>{{ $ticker->status }}</td>
                <td>
                  <a href="{{ route('backend.promo-tickers.show', $ticker) }}" class="btn btn-info btn-sm text-white">Xem</a>
                  <a href="{{ route('backend.promo-tickers.edit', $ticker) }}" class="btn btn-outline-primary btn-sm">Sua</a>
                  <form method="POST" action="{{ route('backend.promo-tickers.destroy', $ticker) }}" class="d-inline" onsubmit="return confirm('Xoa ticker nay?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">Xoa</button>
                  </form>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center">Chua co du lieu.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
