@extends('layouts.panel')
@section('titulo', 'Editar producto')

@section('contenido')
    <form method="POST" action="{{ route('productos.update', $producto) }}">
        @csrf
        @method('PUT')
        @include('productos._form')
    </form>
@endsection
