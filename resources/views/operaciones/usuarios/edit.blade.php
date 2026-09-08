@extends('layouts.app')

@section('title', 'Vital Clean — Editar Usuario')

@section('content')
    <div class="page-header"><h1>Editar Usuario</h1></div>

    <div class="card" style="max-width:480px;">
        <form method="POST" action="{{ route('operaciones.usuarios.update', $usuario) }}">
            @method('PUT')
            @include('operaciones.usuarios._form')
            <div class="form-actions">
                <button type="submit" class="btn">Actualizar</button>
                <a href="{{ route('operaciones.usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
