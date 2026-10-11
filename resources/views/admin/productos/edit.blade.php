@extends('layouts.admin')
@section('title', 'Editar Producto')
@section('page_title', 'Editar Producto')

@section('content')
<form action="{{ route('admin.productos.update', $producto) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')
    @include('admin.productos._form', ['producto' => $producto])
</form>
@endsection
