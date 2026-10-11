@extends('layouts.admin')
@section('title', 'Nuevo Usuario')
@section('page_title', 'Nuevo Usuario')

@section('content')
<div class="admin-card">
    <div class="admin-card__body">
        <form action="{{ route('admin.usuarios.store') }}" method="POST">
            @csrf
            @include('admin.usuarios._form', ['usuario' => null])
        </form>
    </div>
</div>
@endsection
