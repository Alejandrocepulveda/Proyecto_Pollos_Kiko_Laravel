@extends('layouts.panel')
@section('titulo', 'Editar usuario')

@section('contenido')
    <form method="POST" action="{{ route('usuarios.update', $usuario) }}">
        @csrf
        @method('PUT')
        @include('usuarios._form')
    </form>
@endsection
