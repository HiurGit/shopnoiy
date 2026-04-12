@extends('backend.layouts.app')

@section('title', 'Thêm đối tượng sản phẩm')

@section('content')
<div class="app-content-header"><div class="container-fluid"><h1 class="mb-0">Thêm đối tượng sản phẩm</h1></div></div>
<div class="app-content"><div class="container-fluid"><div class="card"><div class="card-body"><form method="POST" action="{{ route('backend.product-targets.store') }}">@csrf @include('backend.product-targets._form')</form></div></div></div></div>
@endsection
