<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class AuditController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('audit.view');

        $activites = Activity::with('causer')
            ->when($request->filled('log'), fn ($q) => $q->where('log_name', $request->input('log')))
            ->when($request->filled('event'), fn ($q) => $q->where('event', $request->input('event')))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $logs = Activity::query()->distinct()->orderBy('log_name')->pluck('log_name')->filter();

        return view('audit.index', [
            'activites' => $activites,
            'logs'      => $logs,
            'filtres'   => $request->only(['log', 'event']),
        ]);
    }
}
