@extends('backend.layouts.app')
@section('title','Sửa cửa hàng')
@section('content')<div class="app-content-header"><div class="container-fluid"><h1 class="mb-0">Sửa cửa hàng</h1></div></div><div class="app-content"><div class="container-fluid"><div class="card"><div class="card-body"><form method="POST" action="{{ route('backend.stores.update', $store) }}">@csrf @method('PUT') @include('backend.stores._form')</form></div></div></div></div>@endsection
