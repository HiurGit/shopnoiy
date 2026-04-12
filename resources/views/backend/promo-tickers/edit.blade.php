@extends('backend.layouts.app')
@section('title','Sửa promo ticker')
@section('content')<div class="app-content-header"><div class="container-fluid"><h1 class="mb-0">Sửa promo ticker</h1></div></div><div class="app-content"><div class="container-fluid"><div class="card"><div class="card-body"><form method="POST" action="{{ route('backend.promo-tickers.update', $promoTicker) }}">@csrf @method('PUT') @include('backend.promo-tickers._form')</form></div></div></div></div>@endsection
