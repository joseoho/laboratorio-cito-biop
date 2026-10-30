<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Quarantine;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QuarantineController extends Controller
{
    public function index(Request $request): View
    {
        $status = (string) $request->query('estado', 'pending');
        if (! in_array($status, ['pending', 'reviewed', 'ignored', 'all'], true)) {
            $status = 'pending';
        }

        $bucket = $request->query('bucket');

        $items = Quarantine::query()
            ->bucket($bucket)
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->orderBy('bucket')
            ->orderBy('id')
            ->paginate(50)
            ->withQueryString();

        $counts = Quarantine::query()
            ->selectRaw('bucket, status, COUNT(*) as n')
            ->groupBy('bucket', 'status')
            ->get()
            ->groupBy('bucket')
            ->map(fn ($rows) => [
                'pending' => (int) $rows->firstWhere('status', 'pending')?->n,
                'reviewed' => (int) $rows->firstWhere('status', 'reviewed')?->n,
                'ignored' => (int) $rows->firstWhere('status', 'ignored')?->n,
                'total' => (int) $rows->sum('n'),
            ])
            ->sortKeys();

        // Para enlazar a la ficha: legacy_key guarda el IDPaciente del legado.
        $legacyIds = $items->pluck('legacy_key')->filter()->unique()->values()->all();
        $patientMap = Patient::query()
            ->whereIn('legacy_id', array_map('intval', $legacyIds))
            ->pluck('id', 'legacy_id');

        return view('quarantine.index', compact('items', 'counts', 'status', 'bucket', 'patientMap'));
    }

    public function resolve(Request $request, Quarantine $quarantine, AuditLogger $audit): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'reviewed', 'ignored'])],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $quarantine->update([
            'status' => $data['status'],
            'note' => $data['note'] ?? null,
            'reviewed_by' => $data['status'] === 'pending' ? null : auth()->id(),
            'reviewed_at' => $data['status'] === 'pending' ? null : now(),
        ]);

        $audit->log('quarantine.' . $data['status'], $quarantine, [
            'bucket' => $quarantine->bucket,
            'key' => $quarantine->legacy_key,
            'note' => $data['note'] ?? null,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Caso de cuarentena actualizado.');
    }
}
