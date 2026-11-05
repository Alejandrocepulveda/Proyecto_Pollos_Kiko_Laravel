@extends('layouts.panel')
@section('titulo', 'Nuevo usuario')

@section('contenido')
    <form method="POST" action="{{ route('usuarios.store') }}">
        @csrf
        @include('usuarios._form')
    </form>
@endsection
