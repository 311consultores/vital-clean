@extends('layouts.app')

@section('title', 'Vital Clean — Nuevo Servicio')

@section('content')
    <div class="page-header"><h1>Nuevo Servicio</h1></div>

    <div class="card" style="max-width:480px;">
        <form method="POST" action="{{ route('operaciones.servicios.store') }}">
            @include('operaciones.servicios._form')
            <div class="form-actions">
                <button type="submit" class="btn">Guardar</button>
                <a href="{{ route('operaciones.servicios.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
