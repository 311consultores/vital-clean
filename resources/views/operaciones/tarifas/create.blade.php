@extends('layouts.app')

@section('title', 'Vital Clean — Nueva Tarifa')

@section('content')
    <div class="page-header"><h1>Nueva Tarifa</h1></div>

    <div class="card" style="max-width:480px;">
        <form method="POST" action="{{ route('operaciones.tarifas.store') }}">
            @include('operaciones.tarifas._form')
            <div class="form-actions">
                <button type="submit" class="btn">Guardar</button>
                <a href="{{ route('operaciones.tarifas.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
