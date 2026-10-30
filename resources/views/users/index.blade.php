@extends('layouts.core')

@section('title', 'Usuarios')

@section('content')
    <div class="d-flex justify-content-between align-items-end mb-3">
        <div>
            <h4 class="mb-0">Usuarios</h4>
            <span class="muted small">{{ number_format($users->total(), 0, ',', '.') }} cuentas</span>
        </div>
        <a href="{{ route('users.create') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-user-plus me-1"></i>Nuevo usuario
        </a>
    </div>

    <form method="GET" action="{{ route('users.index') }}" class="search-box mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small mb-1">Buscar</label>
                <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Nombre o correo">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Rol</label>
                <select name="rol" class="form-select">
                    <option value="">Todos</option>
                    <option value="admin" @selected($role === 'admin')>Administrador</option>
                    <option value="operador" @selected($role === 'operador')>Operador</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Buscar</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary w-100">Limpiar</a>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th style="width:150px">Rol</th>
                        <th style="width:120px">Creado</th>
                        <th style="width:190px"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>
                            {{ $user->name }}
                            @if ($user->id === auth()->id())
                                <span class="badge text-bg-light border ms-1">usted</span>
                            @endif
                        </td>
                        <td class="small">{{ $user->email }}</td>
                        <td>
                            <span class="badge {{ $user->isAdmin() ? 'text-bg-danger' : 'text-bg-secondary' }}">
                                {{ $user->roleLabel() }}
                            </span>
                        </td>
                        <td class="small">{{ $user->created_at?->format('d/m/Y') }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-pen me-1"></i>Editar
                            </a>
                            @if ($user->id !== auth()->id())
                                <form method="POST" action="{{ route('users.destroy', $user) }}" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar al usuario {{ $user->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash me-1"></i>Eliminar
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-4 muted">Sin usuarios.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $users->links('pagination::bootstrap-5') }}</div>
@endsection
