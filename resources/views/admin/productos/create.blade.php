@extends('layouts.admin')
@section('title', 'Nuevo Producto')
@section('page_title', 'Nuevo Producto')

@section('content')
<form action="{{ route('admin.productos.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @include('admin.productos._form', ['producto' => null, 'categorias' => $categorias, 'unidades' => $unidades])
</form>
@endsection
