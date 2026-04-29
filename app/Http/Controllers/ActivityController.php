<?php

namespace App\Http\Controllers;

use App\Models\EventLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->pageTitle = 'Activity Log';
    }

    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $allowed = $user->roles()->whereIn('name', ['client_admin', 'manager'])->exists();
        abort_403(!$allowed);

        $activities = EventLog::query()
            ->where('company_id', company()?->id)
            ->when($request->filled('from'), fn ($query) => $query->whereDate('created_at', '>=', $request->string('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('created_at', '<=', $request->string('to')))
            ->when($request->filled('event_type'), fn ($query) => $query->where('event_type', $request->string('event_type')))
            ->with('user:id,name,email')
            ->latest('created_at')
            ->get();

        $eventTypes = EventLog::query()
            ->where('company_id', company()?->id)
            ->distinct()
            ->orderBy('event_type')
            ->pluck('event_type');

        $this->data['activities'] = $activities;
        $this->data['eventTypes'] = $eventTypes;

        return view('activity.index', $this->data);
    }
}
