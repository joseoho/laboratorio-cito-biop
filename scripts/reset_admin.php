<?php
/**
 * Restablece la contrasena de un usuario, o lista los usuarios existentes.
 *
 * Uso:
 *   php scripts/reset_admin.php --list
 *   php scripts/reset_admin.php
 *   php scripts/reset_admin.php admin@laboratorio.local "NuevaClave123"
 *
 * Crea el usuario si no existe y siempre lo deja con rol admin y correo verificado.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

$args = array_slice($argv, 1);

echo 'Base de datos: ' . Illuminate\Support\Facades\DB::connection()->getDatabaseName() . PHP_EOL;

if (in_array('--list', $args, true)) {
    $users = User::orderBy('id')->get(['id', 'name', 'email', 'role', 'email_verified_at']);

    if ($users->isEmpty()) {
        echo 'No hay usuarios.' . PHP_EOL;
        exit(1);
    }

    foreach ($users as $u) {
        printf(
            "%3d  %-28s  %-9s  %-8s  %s\n",
            $u->id,
            $u->email,
            (string) $u->role,
            $u->email_verified_at ? 'verificado' : 'sin verificar',
            $u->name
        );
    }

    exit(0);
}

$email = $args[0] ?? 'admin@laboratorio.local';
$password = $args[1] ?? 'cambiar123';

if (strlen($password) < 8) {
    fwrite(STDERR, 'La contrasena debe tener al menos 8 caracteres.' . PHP_EOL);
    exit(1);
}

$user = User::where('email', $email)->first();
$creado = false;

if (! $user) {
    $user = new User();
    $user->name = 'Administrador';
    $user->email = $email;
    $creado = true;
}

$user->password = $password;            // el cast "hashed" la cifra
$user->role = User::ROLE_ADMIN;
$user->email_verified_at = $user->email_verified_at ?? now();
$user->save();

echo ($creado ? 'Usuario creado: ' : 'Contrasena actualizada: ') . $user->email . PHP_EOL;
echo 'Rol: ' . $user->role . PHP_EOL;
echo 'Contrasena: ' . $password . PHP_EOL;
echo 'Ya podes iniciar sesion.' . PHP_EOL;
