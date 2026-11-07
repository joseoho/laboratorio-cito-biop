<?php

namespace App\Http\Controllers;

use App\Services\AuditLogger;
use App\Support\Signature;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SignatureController extends Controller
{
    public function index(): View
    {
        return view('signature.index', [
            'preview' => Signature::dataUri(),
            'updatedAt' => Signature::updatedAt(),
        ]);
    }

    public function store(Request $request, AuditLogger $audit): RedirectResponse
    {
        $request->validate([
            'image' => ['required', 'file', 'mimes:png,jpg,jpeg', 'max:4096'],
        ], [
            'image.required' => 'Elegí la imagen de la firma.',
            'image.mimes' => 'La firma debe ser una imagen PNG o JPG.',
            'image.max' => 'La imagen no puede superar los 4 MB.',
        ]);

        $file = $request->file('image');
        $extension = strtolower($file->getClientOriginalExtension() ?: 'png');

        foreach (['png', 'jpg', 'jpeg'] as $old) {
            Storage::disk('local')->delete(Signature::DIR . '/' . Signature::NAME . '.' . $old);
        }

        $file->storeAs(Signature::DIR, Signature::NAME . '.' . $extension, 'local');

        $audit->logRaw('signature.updated', self::class, null, ['extension' => $extension]);

        return back()->with('success', 'Firma actualizada. Ya se puede usar en los informes.');
    }
}
