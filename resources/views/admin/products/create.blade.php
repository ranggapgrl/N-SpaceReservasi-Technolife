@extends('admin.layouts.app')

@section('page_title', 'Tambah Produk')

@section('content')
    @include('admin.products._form', ['product' => null])
@endsection
