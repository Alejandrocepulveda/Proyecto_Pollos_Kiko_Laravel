@extends('layouts.panel')
@section('titulo', 'Nuevo producto')

@section('contenido')
    <form method="POST" action="{{ route('productos.store') }}">
        @csrf
        @include('productos._form')
    </form>
@endsection