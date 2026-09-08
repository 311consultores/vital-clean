@extends('layouts.app')

@section('title', 'Vital Clean — Nuevo Cliente')

@section('content')
    <div class="page-header"><h1>Nuevo Cliente</h1></div>

    <div class="card" style="max-width:520px;">
        <form method="POST" action="{{ route('operaciones.clientes.store') }}">
            @include('operaciones.clientes._form')
            <div class="form-actions">
                <button type="submit" class="btn">Guardar</button>
                <a href="{{ route('operaciones.clientes.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
