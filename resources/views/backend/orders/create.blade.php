@extends('backend.layouts.app')
@section('title','Thêm đơn hàng')
@section('content')<div class="app-content-header"><div class="container-fluid"><h1 class="mb-0">Thêm đơn hàng</h1></div></div><div class="app-content"><div class="container-fluid"><div class="card"><div class="card-body"><form method="POST" action="{{ route('backend.orders.store') }}">@csrf @include('backend.orders._form')</form></div></div></div></div>@endsection
