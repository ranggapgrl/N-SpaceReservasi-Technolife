@extends('admin.layouts.app')

@section('page_title', 'Edit Produk')

@section('content')
    @include('admin.products._form', ['product' => $product])
@endsection
