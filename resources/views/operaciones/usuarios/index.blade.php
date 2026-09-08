@extends('layouts.app')

@section('title', 'Vital Clean — Usuarios')

@section('content')
    <div class="page-header">
        <h1>Catálogo de Usuarios</h1>
        <a href="{{ route('operaciones.usuarios.create') }}" class="btn">+ Nuevo Usuario</a>
    </div>

    <div class="card" style="padding:0; overflow-x:auto;">
        <div style="padding:1rem 1rem 0;">
            <form method="GET" action="{{ route('operaciones.usuarios.index') }}" style="margin-bottom:1rem;">
                <input type="text" name="buscar" placeholder="Buscar por usuario o nombre..." value="{{ request('buscar') }}"
                       style="padding:.55rem .7rem; border:1px solid #d1d5db; border-radius:.375rem; min-width:260px;">
                <button type="submit" class="btn btn-sm">Buscar</button>
                @if (request('buscar'))
                    <a href="{{ route('operaciones.usuarios.index') }}" class="btn btn-sm btn-secondary">Limpiar</a>
                @endif
            </form>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Nombre Completo</th>
                    <th>Rol</th>
                    <th>Estatus</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($usuarios as $usuario)
                    <tr>
                        <td>
                            {{ $usuario->username }}
                            @if ($usuario->id_usuario === auth()->user()->id_usuario)
                                <span style="color:#6b7280; font-size:.8rem;">(tú)</span>
                            @endif
                        </td>
                        <td>{{ $usuario->nombre_completo ?? '—' }}</td>
                        <td><span class="badge badge-{{ strtolower($usuario->rol) }}">{{ $usuario->rol }}</span></td>
                        <td>
                            @if ($usuario->activo)
                                <span class="badge" style="background:#28a745;">Activo</span>
                            @else
                                <span class="badge" style="background:#C0392B;">Deshabilitado</span>
                            @endif
                        </td>
                        <td class="actions-cell">
                            <a class="btn btn-sm" href="{{ route('operaciones.usuarios.edit', $usuario) }}">Editar</a>
                            @unless ($usuario->id_usuario === auth()->user()->id_usuario)
                                <form method="POST" action="{{ route('operaciones.usuarios.destroy', $usuario) }}"
                                      onsubmit="return confirm('¿Eliminar este usuario?');" style="margin:0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">No hay usuarios que coincidan con la búsqueda.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div style="padding:1rem;">{{ $usuarios->links() }}</div>
    </div>
@endsection
