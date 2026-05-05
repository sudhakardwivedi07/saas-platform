@extends('layouts.app')
@section('title', $clientUser->name . "'s KPI Dashboard")
@section('page-title', 'Client KPI Dashboard')

@section('content')

<div id="insights-refresh-target" data-refresh="1" style="display:none"></div>

<a href="{{ route('clients.show', $clientUser->id) }}" class="back-btn">
    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
    </svg>
    Back to {{ $clientUser->name }}
</a>

<div class="ph" style="margin-top:0.5rem;">
    <div class="ph-left">
        <h1 style="font-size:1.5rem;font-weight:700;color:var(--text-1);margin:0 0 2px 0;">
            KPI Dashboard &mdash; {{ $clientUser->name }}
        </h1>
        <p style="font-size:0.875rem;color:var(--text-2);margin:0;">Financial KPI insights and metrics.</p>
    </div>
</div>

<div class="user-kpi-fullbleed">
@include('dashboard.partials.kpi-dashboard-embed', [
    'kpiDataUrl' => route('clients.kpi-data', $clientUser->id),
    'kpiDetailBaseUrl' => url('/clients/'.$clientUser->id.'/kpi-detail'),
    'companies' => $companies,
    'selectedCompanyId' => $selectedCompanyId,
])
</div>

@endsection
