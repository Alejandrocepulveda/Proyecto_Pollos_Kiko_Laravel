@extends('layouts.panel')
@section('titulo', 'Editar proveedor')

@section('contenido')
    <form method="POST" action="{{ route('proveedores.update', $proveedor) }}">
        @csrf
        @method('PUT')
        @include('proveedores._form')
    </form>
@endsection