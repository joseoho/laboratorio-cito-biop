<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Firma digitalizada del laboratorio.
 *
 * Se guarda una sola imagen en storage/app/private/firma (disco "local") y se
 * incrusta en la impresion y en el PDF como data URI, para que no dependa de
 * rutas publicas ni del servidor web.
 */
class Signature
{
    public const DIR = 'firma';

    public const NAME = 'firma';

    /** Ruta relativa del archivo cargado, o null si no hay firma. */
    public static function path(): ?string
    {
        foreach (['png', 'jpg', 'jpeg'] as $extension) {
            $path = self::DIR . '/' . self::NAME . '.' . $extension;

            if (Storage::disk('local')->exists($path)) {
                return $path;
            }
        }

        return null;
    }

    public static function exists(): bool
    {
        return self::path() !== null;
    }

    /** Imagen lista para incrustar en HTML o en dompdf. */
    public static function dataUri(): ?string
    {
        $path = self::path();

        if ($path === null) {
            return null;
        }

        $mime = str_ends_with($path, '.png') ? 'image/png' : 'image/jpeg';

        return 'data:' . $mime . ';base64,' . base64_encode(Storage::disk('local')->get($path));
    }

    /** Fecha de la ultima carga, para mostrarla en la pantalla de administracion. */
    public static function updatedAt(): ?int
    {
        $path = self::path();

        return $path === null ? null : Storage::disk('local')->lastModified($path);
    }
}
