@extends('frontend.layouts.app')

@section('title', 'Tìm kiếm sản phẩm')

@section('meta_title', $queryText !== '' ? 'Tìm kiếm: ' . $queryText : 'Tìm kiếm sản phẩm')
@section('meta_description', $queryText !== '' ? 'Kết quả tìm kiếm cho từ khóa ' . $queryText . '.' : 'Tìm kiếm sản phẩm phù hợp trên website.')
@section('meta_robots', 'noindex,nofollow')

@section('content')
<livewire:frontend.search-page mode="page" :initial-query="$queryText" :initial-preferred-product-id="$preferredProductId ?? 0" />
@endsection
