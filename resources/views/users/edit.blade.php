@extends('layouts.core')

@section('title', 'Editar ' . $user->name)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Editar usuario</h4>
            <span class="muted small">{{ $user->email }}</span>
        </div>
        <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary">Volver</a>
    </div>

    @include('users._form', ['user' => $user])
@endsection
