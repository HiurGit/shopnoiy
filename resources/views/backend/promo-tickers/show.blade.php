@extends('backend.layouts.app')

@section('title', 'Chi tiet promo ticker')

@section('content')
<div class="app-content-header">
  <div class="container-fluid d-flex justify-content-between align-items-center">
    <h1 class="mb-0">Chi tiet promo ticker</h1>
    <a href="{{ route('backend.promo-tickers') }}" class="btn btn-secondary btn-sm">Quay lai</a>
  </div>
</div>
<div class="app-content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-body">
        <table class="table table-bordered">
          <tbody>
            <tr><th style="width:220px;">ID</th><td>{{ $promoTicker->id }}</td></tr>
            <tr><th>Ten</th><td>{{ $promoTicker->name }}</td></tr>
            <tr><th>Noi dung</th><td>{{ $promoTicker->content_text }}</td></tr>
            <tr><th>Style</th><td>{{ $promoTicker->background_style }}</td></tr>
            <tr><th>Color</th><td>{{ $promoTicker->text_color }}</td></tr>
            <tr><th>Toc do</th><td>{{ $promoTicker->speed_seconds }}</td></tr>
            <tr><th>Trang thai</th><td>{{ $promoTicker->status }}</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
