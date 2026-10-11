@extends('layouts.admin')
@section('title', 'Editar Usuario')
@section('page_title', 'Editar Usuario')

@section('content')
<div class="admin-card">
    <div class="admin-card__body">
        <form action="{{ route('admin.usuarios.update', $usuario) }}" method="POST">
            @csrf @method('PUT')
            @include('admin.usuarios._form', ['usuario' => $usuario])
        </form>
    </div>
</div>
@endsection
