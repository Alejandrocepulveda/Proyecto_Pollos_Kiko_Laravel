@extends('layouts.panel')
@section('titulo', 'Nuevo proveedor')

@section('contenido')
    <form method="POST" action="{{ route('proveedores.store') }}">
        @csrf
        @include('proveedores._form')
    </form>
@endsection