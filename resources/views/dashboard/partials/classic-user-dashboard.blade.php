@php
    $tenant = company();
    $tenantName = $tenant?->company_name ?? $tenant?->app_name ?? 'Your firm';
    $allPlans = ($plans ?? collect())->values();
    $topPlans = $allPlans->take(2);
@endphp

<div class="classic-dash-grid @if($allPlans->isEmpty()) classic-dash-grid--empty-plans @endif">
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

    @if($allPlans->isEmpty())
        <div class="classic-dash-card plan-card" style="justify-content:center">
            <h2 class="plan-card-title">Next steps</h2>
            <p class="classic-lead" style="margin-bottom:16px">
                Your administrator has not published subscription tiers yet. Connect QuickBooks when available,
                or open Billing to review your workspace.
            </p>
            @if(auth()->user()->can('view_quickbooks'))
                <a href="{{ route('quickbooks.index') }}" class="plan-btn btn-orange" style="text-decoration:none;margin-bottom:10px">QuickBooks</a>
            @endif
            <a href="{{ route('billing.index') }}" class="plan-btn btn-orange-outline" style="text-decoration:none">Billing &amp; plan</a>
        </div>
    @else
        @foreach($topPlans as $plan)
            @php
                $isCurrent = $currentPlan && (int) $currentPlan->id === (int) $plan->id;
                $featList = is_array($plan->features ?? null) ? array_values(array_filter($plan->features)) : [];
                $monthlyInsights = $plan->insights_per_month ?? null;
                $maxCo = $plan->max_companies ?? null;
                $isFree = !empty($plan->is_free) || (float) ($plan->monthly_price ?? 0) <= 0;
            @endphp
            <div class="classic-dash-card plan-card">
                <div class="plan-card-title">
                    {{ $plan->name }}
                    @if($isCurrent)
                        <span class="plan-badge">CURRENT</span>
                    @elseif(!empty($plan->is_recommended))
                        <span class="plan-star" title="Recommended">★</span>
                    @endif
                </div>
                @if($plan->description)
                    <p class="plan-card-subtitle" style="margin-bottom:10px">{{ $plan->description }}</p>
                @endif
                @if($featList)
                    <ul class="plan-feature-list">
                        @foreach(array_slice($featList, 0, 8) as $feat)
                            <li>
                                <svg class="feat-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                <span class="feat-title">{{ $feat }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <div class="classic-stat-row">
                    <div class="classic-stat-box">
                        <div class="lbl">INSIGHTS / MONTH</div>
                        <div class="val">{{ $monthlyInsights !== null ? (int) $monthlyInsights : '—' }}</div>
                    </div>
                    <div class="classic-stat-box">
                        <div class="lbl">MAX COMPANIES</div>
                        <div class="val">{{ $maxCo !== null ? (int) $maxCo : '—' }}</div>
                    </div>
                </div>

                @if($isFree)
                    <div class="classic-price-strip">FREE PLAN @if($monthlyInsights) — INCLUDES {{ (int) $monthlyInsights }} ANALYSIS RUNS @endif</div>
                @else
                    <div class="classic-price-strip">
                        ${{ number_format((float) $plan->monthly_price, 2) }}/MONTH
                        @if($monthlyInsights)
                            — INCLUDES {{ (int) $monthlyInsights }} ANALYSIS RUNS
                        @endif
                    </div>
                @endif

                <div class="mt-auto classic-dash-actions">
                    @if($isCurrent)
                        @if(!empty($isQuickbooksConnected) && auth()->user()->can('view_quickbooks'))
                            <button type="button" id="dashGenerateBtn" class="plan-btn btn-orange"
                                    onclick="dashTriggerGenerate()"
                                    style="{{ !empty($insightInProgress) ? 'display:none;' : '' }}">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg>
                                Generate Insights
                            </button>
                            <div id="dashGeneratingLoader" class="dash-gen-loader"
                                 style="{{ empty($insightInProgress) ? 'display:none;' : '' }}">
                                <span class="dash-gen-spinner"></span>
                                <span>Generating insights…</span>
                            </div>
                        @elseif(auth()->user()->can('view_quickbooks'))
                            <a href="{{ route('quickbooks.index') }}" class="plan-btn btn-orange" style="text-decoration:none">Connect QuickBooks</a>
                        @endif
                        <a href="{{ route('billing.index') }}" class="plan-btn btn-orange-outline" style="text-decoration:none">Billing &amp; plan</a>
                    @else
                        <form method="POST" action="{{ route('dashboard.assign-plan') }}" style="margin:0">
                            @csrf
                            <input type="hidden" name="client_package_id" value="{{ $plan->id }}">
                            <button type="submit" class="plan-btn btn-orange">Choose {{ $plan->name }}</button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach

        @if($topPlans->count() < 2)
            <div class="classic-dash-card plan-card" style="justify-content:center">
                <h2 class="plan-card-title">More options</h2>
                <p class="classic-lead" style="margin-bottom:16px">
                    @if($allPlans->count() > 2)
                        Your firm offers additional plans. Compare tiers and manage payment methods anytime.
                    @else
                        Connect QuickBooks to generate your first insight, or open Billing to review your subscription.
                    @endif
                </p>
                @if(auth()->user()->can('view_quickbooks'))
                    <a href="{{ route('quickbooks.index') }}" class="plan-btn btn-orange" style="text-decoration:none;margin-bottom:10px">QuickBooks</a>
                @endif
                <a href="{{ route('billing.index') }}" class="plan-btn btn-orange-outline" style="text-decoration:none">Billing &amp; plan</a>
                @if($allPlans->count() > 2)
                    <p class="plan-card-desc" style="margin-top:16px;margin-bottom:0;font-size:12px">
                        {{ $allPlans->count() - 2 }} more plan{{ $allPlans->count() - 2 === 1 ? '' : 's' }} available under Billing.
                    </p>
                @endif
            </div>
        @endif
    @endif
</div>
