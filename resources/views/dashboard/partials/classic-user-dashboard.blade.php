@php
    $tenant = company();
    $tenantName = $tenant?->company_name ?? $tenant?->app_name ?? 'Your firm';
    $currentPlan = $tenant->activePlan();
    $isCurrentPlanPaid = $currentPlan && !(!empty($currentPlan->is_free) || ((float) ($currentPlan->monthly_price ?? 0) <= 0));
    $allPlans = ($plans ?? collect())->values();
@endphp

<div class="classic-dash-grid @if($allPlans->isEmpty()) classic-dash-grid--empty-plans @elseif($allPlans->count() === 1) classic-dash-grid--single-plan @endif">
    {{-- Column 1: What your company does --}}
    <div class="classic-dash-card plan-card">
        <h2 class="plan-card-title" style="margin-bottom:10px">What {{ $tenantName }} does</h2>
        <p class="classic-lead">
            We help founders and finance leaders see performance clearly — with secure data ingestion,
            live dashboards, and AI-assisted insights so you can act with confidence.
        </p>
        <ul class="plan-feature-list" style="margin-bottom:0">
            <li>
                <svg class="feat-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <span class="feat-title">Flexible data ingestion and visibility</span>
            </li>
            <li>
                <svg class="feat-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <span class="feat-title">Live financial dashboards</span>
            </li>
            <li>
                <svg class="feat-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <span class="feat-title">Deeper KPI analysis</span>
            </li>
            <li>
                <svg class="feat-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <span class="feat-title">Secure financial data management</span>
            </li>
            <li>
                <svg class="feat-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <span class="feat-title">Ongoing guidance and support</span>
            </li>
        </ul>
    </div>
</div>
