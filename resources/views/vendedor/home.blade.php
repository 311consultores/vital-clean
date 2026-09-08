@extends('layouts.app')

@section('title', 'Vital Clean — Vendedor')

@section('content')
    <div class="card">
        <h1>Hola, {{ auth()->user()->nombre_completo ?? auth()->user()->username }}</h1>
        <div class="form-actions">
            <a href="{{ route('vendedor.recoleccion.create') }}" class="btn">+ Nuevo Pedido</a>
            <a href="{{ route('vendedor.pedidos.index') }}" class="btn btn-secondary">Mis Pedidos</a>
            <a href="{{ route('entrega.buscar') }}" class="btn btn-secondary">Cierre de Entrega</a>
        </div>
    </div>
@endsection
