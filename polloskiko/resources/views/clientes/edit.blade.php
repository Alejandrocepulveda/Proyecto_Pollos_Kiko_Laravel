@extends('layouts.panel')
@section('titulo', 'Editar cliente')

@section('contenido')
    <form method="POST" action="{{ route('clientes.update', $cliente) }}">
        @csrf
        @method('PUT')
        @include('clientes._form')
    </form>
@endsection
