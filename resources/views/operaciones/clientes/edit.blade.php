@extends('layouts.app')

@section('title', 'Vital Clean — Editar Cliente')

@section('content')
    <div class="page-header"><h1>Editar Cliente</h1></div>

    <div class="card" style="max-width:520px;">
        <form method="POST" action="{{ route('operaciones.clientes.update', $cliente) }}">
            @method('PUT')
            @include('operaciones.clientes._form')
            <div class="form-actions">
                <button type="submit" class="btn">Actualizar</button>
                <a href="{{ route('operaciones.clientes.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
