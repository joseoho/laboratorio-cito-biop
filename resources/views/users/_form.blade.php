@php($u = $user ?? null)
@php($editing = (bool) ($u?->exists))

<form method="POST" action="{{ $editing ? route('users.update', $u) : route('users.store') }}">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nombre <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $u->name ?? '') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Correo <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email', $u->email ?? '') }}" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Rol <span class="text-danger">*</span></label>
                    <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                        <option value="operador" @selected(old('role', $u->role ?? 'operador') === 'operador')>Operador</option>
                        <option value="admin" @selected(old('role', $u->role ?? '') === 'admin')>Administrador</option>
                    </select>
                    @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="section-title">Contraseña</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">
                        Contraseña {{ $editing ? '' : '*' }}
                    </label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                           autocomplete="new-password">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">
                        {{ $editing ? 'Dejar vacío para no cambiarla.' : 'Mínimo 8 caracteres.' }}
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Confirmar contraseña</label>
                    <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i>{{ $editing ? 'Guardar cambios' : 'Crear usuario' }}
        </button>
    </div>
</form>
