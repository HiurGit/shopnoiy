@extends('backend.layouts.app')
@section('title','Sửa đơn hàng')
@section('content')<div class="app-content-header"><div class="container-fluid"><h1 class="mb-0">Sửa đơn hàng</h1></div></div><div class="app-content"><div class="container-fluid"><div class="card"><div class="card-body"><form method="POST" action="{{ route('backend.orders.update', $order) }}">@csrf @method('PUT') @include('backend.orders._form')</form></div></div></div></div>@endsection
