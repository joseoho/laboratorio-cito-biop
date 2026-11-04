<?php

namespace App\Http\Controllers;

use App\Models\ClinicalCase;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $currentYear = (int) now()->year;

        // Anios que realmente tienen estudios (para marcar los vacios).
        $dataYears = ClinicalCase::query()
            ->select('year_no')
            ->distinct()
            ->whereNotNull('year_no')
            ->pluck('year_no')
            ->map(fn ($y) => (int) $y)
            ->sort()
            ->values()
            ->all();

        // Por defecto, el ultimo anio con datos (no un anio vacio).
        $defaultYear = $dataYears ? max($dataYears) : $currentYear;

        $year = (int) $request->query('anio', $defaultYear);
        if ($year < 2000 || $year > 2100) {
            $year = $defaultYear;
        }

        $type = $request->query('tipo');
        if (! in_array($type, ['biopsy', 'cytology'], true)) {
            $type = null;
        }

        $start = Carbon::create($year, 1, 1)->startOfDay();
        $end = (clone $start)->addYear()->subSecond();

        $base = ClinicalCase::query()->whereBetween('received_at', [$start, $end]);
        if ($type) {
            $base->where('type', $type);
        }

        $totals = (clone $base)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(type = "biopsy") as biopsies')
            ->selectRaw('SUM(type = "cytology") as cytologies')
            ->selectRaw('SUM(status = "delivered") as delivered')
            ->selectRaw('SUM(status = "in_progress") as in_progress')
            ->first();

        $monthly = array_fill(1, 12, 0);
        $monthlyDelivered = array_fill(1, 12, 0);

        foreach ((clone $base)
            ->selectRaw('MONTH(received_at) as m')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(status = "delivered") as delivered')
            ->groupBy('m')
            ->get() as $row) {
            $monthly[(int) $row->m] = (int) $row->total;
            $monthlyDelivered[(int) $row->m] = (int) $row->delivered;
        }

        $pending = (clone $base)
            ->where('status', 'in_progress')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(DATEDIFF(CURDATE(), received_at) <= 7) as up_to_7')
            ->selectRaw('SUM(DATEDIFF(CURDATE(), received_at) BETWEEN 8 AND 30) as up_to_30')
            ->selectRaw('SUM(DATEDIFF(CURDATE(), received_at) > 30) as over_30')
            ->first();

        $withoutPathologist = (clone $base)
            ->whereDoesntHave('assignments', fn ($q) => $q->where('role', 1))
            ->count();

        $pathologists = DB::table('case_assignments as a')
            ->join('cases as c', 'c.id', '=', 'a.case_id')
            ->where('a.role', 1)
            ->whereBetween('c.received_at', [$start, $end])
            ->when($type, fn ($q) => $q->where('c.type', $type))
            ->groupBy('a.pathologist_name')
            ->selectRaw('a.pathologist_name as name, COUNT(*) as cases, SUM(c.status = "delivered") as delivered')
            ->orderByDesc('cases')
            ->limit(15)
            ->get();

        $origins = DB::table('cases as c')
            ->join('patients as p', 'p.id', '=', 'c.patient_id')
            ->leftJoin('origins as o', 'o.id', '=', 'p.origin_id')
            ->whereBetween('c.received_at', [$start, $end])
            ->when($type, fn ($q) => $q->where('c.type', $type))
            ->groupBy('o.legacy_code')
            ->selectRaw('COALESCE(o.legacy_code, "(sin procedencia)") as label, COUNT(*) as cases')
            ->orderByDesc('cases')
            ->limit(10)
            ->get();

        $doctors = DB::table('cases as c')
            ->join('patients as p', 'p.id', '=', 'c.patient_id')
            ->leftJoin('doctors as d', 'd.id', '=', 'p.doctor_id')
            ->whereBetween('c.received_at', [$start, $end])
            ->when($type, fn ($q) => $q->where('c.type', $type))
            ->groupBy('d.name')
            ->selectRaw('COALESCE(d.name, "(sin médico)") as label, COUNT(*) as cases')
            ->orderByDesc('cases')
            ->limit(10)
            ->get();

        // Lista continua: 2001..2026, incluidos los anios sin estudios (ej. 2024),
        // para que el selector no salte de 2023 a 2026.
        $minYear = $dataYears ? min($dataYears) : $currentYear;
        $maxYear = max(array_merge($dataYears, [$currentYear]));
        $years = range($maxYear, $minYear);

        if (! in_array($year, $years, true)) {
            $years = array_merge([$year], $years);
        }

        $yearsWithData = $dataYears;

        $maxMonthly = max($monthly) ?: 1;

        return view('reports.index', compact(
            'year', 'type', 'totals', 'monthly', 'monthlyDelivered', 'maxMonthly',
            'pending', 'withoutPathologist', 'pathologists', 'origins', 'doctors', 'years', 'yearsWithData'
        ));
    }
}
