@extends('layouts.app')

@section('title', 'Vital Clean — Nuevo Usuario')

@section('content')
    <div class="page-header"><h1>Nuevo Usuario</h1></div>

    <div class="card" style="max-width:480px;">
        <form method="POST" action="{{ route('operaciones.usuarios.store') }}">
            @include('operaciones.usuarios._form')
            <div class="form-actions">
                <button type="submit" class="btn">Guardar</button>
                <a href="{{ route('operaciones.usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
