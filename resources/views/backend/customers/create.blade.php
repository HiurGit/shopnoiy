@extends('backend.layouts.app')
@section('title','Thêm khách hàng')
@section('content')<div class="app-content-header"><div class="container-fluid"><h1 class="mb-0">Thêm khách hàng</h1></div></div><div class="app-content"><div class="container-fluid"><div class="card"><div class="card-body"><form method="POST" action="{{ route('backend.customers.store') }}">@csrf @include('backend.customers._form', ['profile' => null])</form></div></div></div></div>@endsection
