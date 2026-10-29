@extends('layouts.panel')
@section('titulo', 'Nuevo cliente')

@section('contenido')
    <form method="POST" action="{{ route('clientes.store') }}">
        @csrf
        @include('clientes._form')
    </form>
@endsection
