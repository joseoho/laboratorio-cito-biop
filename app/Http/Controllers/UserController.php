<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    private const ROLES = [User::ROLE_ADMIN, User::ROLE_OPERADOR];

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $role = (string) $request->query('rol', '');

        $users = User::query()
            ->when($q !== '', fn ($query) => $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%");
            }))
            ->when(in_array($role, self::ROLES, true), fn ($query) => $query->where('role', $role))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('users.index', compact('users', 'q', 'role'));
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(Request $request, AuditLogger $audit): RedirectResponse
    {
        $data = $this->validateData($request);

        $user = User::create([
            'name' => trim($data['name']),
            'email' => mb_strtolower(trim($data['email'])),
            'password' => $data['password'],
            'role' => $data['role'],
        ]);

        $audit->log('user.created', $user, [
            'email' => $user->email,
            'role' => $user->role,
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuario creado: ' . $user->name . '.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user, AuditLogger $audit): RedirectResponse
    {
        $data = $this->validateData($request, $user);

        if ($user->id === auth()->id() && $data['role'] !== $user->role) {
            throw ValidationException::withMessages([
                'role' => 'No puede cambiar su propio rol.',
            ]);
        }

        $changes = [
            'name' => $data['name'],
            'email' => mb_strtolower(trim($data['email'])),
            'role' => $data['role'],
        ];

        if (! empty($data['password'])) {
            $changes['password'] = $data['password'];
        }

        $user->update($changes);

        $audit->log('user.updated', $user, [
            'email' => $user->email,
            'role' => $user->role,
            'password_changed' => ! empty($data['password']),
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuario actualizado: ' . $user->name . '.');
    }

    public function destroy(User $user, AuditLogger $audit): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('users.index')
                ->with('error', 'No puede eliminar su propio usuario.');
        }

        if ($user->isAdmin() && User::query()->where('role', User::ROLE_ADMIN)->count() <= 1) {
            return redirect()
                ->route('users.index')
                ->with('error', 'No puede eliminar al último administrador.');
        }

        $audit->log('user.deleted', $user, [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ]);

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuario eliminado.');
    }

    private function validateData(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'role' => ['required', Rule::in(self::ROLES)],
            'password' => [
                $user ? 'nullable' : 'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo es obligatorio.',
            'email.unique' => 'Ese correo ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'role.required' => 'El rol es obligatorio.',
        ]);
    }
}
