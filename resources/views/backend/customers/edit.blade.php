@extends('backend.layouts.app')
@section('title','Sửa khách hàng')
@section('content')<div class="app-content-header"><div class="container-fluid"><h1 class="mb-0">Sửa khách hàng</h1></div></div><div class="app-content"><div class="container-fluid"><div class="card"><div class="card-body"><form method="POST" action="{{ route('backend.customers.update', $customer) }}">@csrf @method('PUT') @include('backend.customers._form')</form></div></div></div></div>@endsection
