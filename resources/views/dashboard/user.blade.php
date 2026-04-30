@extends('layouts.app')
@section('page-title', 'Dashboard')

@section('content')

<style>
.user-db-header { margin-bottom: 24px; }
.user-db-header h1 { font-size: 22px; font-weight: 700; color: var(--text-1); margin: 0 0 6px; }
.user-db-header h1 span { color: var(--orange); }
.user-db-header p { font-size: 13.5px; color: var(--text-3); margin: 0; }
.user-db-header p a { color: var(--orange); text-decoration: none; font-weight: 500; }
.user-db-header p a:hover { text-decoration: underline; }

.plan-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; align-items: start; }
@media (max-width: 900px) { .plan-grid { grid-template-columns: 1fr; } }

.plan-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 24px;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.plan-card-title { font-size: 16px; font-weight: 700; color: var(--text-1); margin: 0 0 12px; display: flex; align-items: center; gap: 8px; }
.plan-card-title .plan-badge {
    font-size: 11px;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 4px;
    background: var(--text-1);
    color: #fff;
    letter-spacing: .3px;
}
.plan-card-title .plan-star { color: #F59E0B; font-size: 16px; }

.plan-card-subtitle { font-size: 13px; color: var(--text-3); margin: 0 0 6px; font-weight: 500; }
.plan-card-desc { font-size: 13px; color: var(--text-2); line-height: 1.6; margin: 0 0 16px; }
.plan-card-desc strong { font-weight: 600; color: var(--text-1); }

.plan-feature-list { list-style: none; margin: 0 0 20px; padding: 0; display: flex; flex-direction: column; gap: 8px; }
.plan-feature-list li { display: flex; align-items: flex-start; gap: 8px; font-size: 13px; color: var(--text-2); }
.plan-feature-list li .feat-check {
    width: 16px; height: 16px; flex-shrink: 0; margin-top: 1px;
    color: var(--green);
}
.plan-feature-list li .feat-title { font-weight: 600; color: var(--text-1); }
.plan-feature-list li .feat-body { color: var(--text-3); font-size: 12.5px; margin-top: 2px; line-height: 1.5; }

.entitlement-box {
    background: #FFF8F3;
    border: 1px solid #FBDCC8;
    border-radius: 8px;
    padding: 14px;
    margin: 0 0 18px;
}
.entitlement-label {
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: .6px;
    text-transform: uppercase;
    color: var(--orange);
    display: flex;
    align-items: center;
    gap: 5px;
    margin-bottom: 8px;
}
.entitlement-title { font-size: 13px; font-weight: 600; color: var(--text-1); margin-bottom: 3px; }
.entitlement-sub { font-size: 12px; color: var(--text-3); }

.plan-price-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .5px;
    text-transform: uppercase;
    color: var(--text-3);
    margin-bottom: 14px;
    margin-top: auto;
}

.plan-btn {
    display: flex; align-items: center; justify-content: center;
    gap: 6px;
    width: 100%;
    padding: 11px 16px;
    border-radius: 8px;
    font-size: 13.5px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    text-decoration: none;
    transition: opacity .15s, transform .1s;
}
.plan-btn:hover { opacity: .88; transform: translateY(-1px); }
.plan-btn.btn-orange { background: var(--orange); color: #fff; }
.plan-btn.btn-orange-outline { background: #fff; color: var(--orange); border: 2px solid var(--orange); }
.plan-btn:disabled { opacity: .55; cursor: not-allowed; transform: none; }

.mt-auto { margin-top: auto; }

/* ── Generating Loader ──────────────────────────────────────────── */
.dash-gen-loader {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    padding: 11px 16px;
    border-radius: 8px;
    font-size: 13.5px;
    font-weight: 600;
    color: #1a6aa8;
    background: #e8f2fb;
    border: 1.5px solid #b3d4f0;
    cursor: not-allowed;
    user-select: none;
}
.dash-gen-spinner {
    width: 16px; height: 16px;
    border: 2.5px solid #b3d4f0;
    border-top-color: #1a6aa8;
    border-radius: 50%;
    animation: dashSpin 0.75s linear infinite;
    flex-shrink: 0;
}
@keyframes dashSpin { to { transform: rotate(360deg); } }

/* ── Processing bar ──────────────────────────────────────────────── */
.db-thinking-bar {
    display: flex; align-items: center; gap: 10px;
    padding: 14px 18px;
    background: linear-gradient(135deg,#f0f7ff 0%,#f5f0ff 100%);
    border: 1px solid #dde8ff; border-radius: 10px;
    font-size: .875rem; color: #3b5bdb; font-weight: 500;
    margin-bottom: 24px;
}
.db-thinking-dots span {
    display: inline-block; width: 7px; height: 7px;
    border-radius: 50%; background: #3b5bdb; margin: 0 2px;
    animation: dbBounce 1.2s infinite ease-in-out;
}
.db-thinking-dots span:nth-child(2) { animation-delay: .2s; }
.db-thinking-dots span:nth-child(3) { animation-delay: .4s; }
@keyframes dbBounce {
    0%,80%,100% { transform: scale(.6); opacity: .5; }
    40%         { transform: scale(1); opacity: 1; }
}

/* ── Industry Modal ─────────────────────────────────────────────── */
.ind-modal-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,.45);
    z-index: 9999; display: none; align-items: center; justify-content: center;
}
.ind-modal-overlay.open { display: flex; }
.ind-modal {
    background: white; border-radius: 20px;
    padding: 36px 36px 28px; width: 480px; max-width: 95vw;
    text-align: center; position: relative;
    animation: indSlide .25s ease;
    box-shadow: 0 20px 60px rgba(0,0,0,.18);
}
@keyframes indSlide { from { opacity: 0; transform: translateY(-16px); } to { opacity: 1; transform: translateY(0); } }
.ind-modal h3 { font-size: 22px; font-weight: 700; color: #1a202c; margin: 0 0 10px; }
.ind-modal p  { font-size: 14px; color: #6b7280; line-height: 1.6; margin: 0 0 20px; }
.ind-modal select {
    width: 100%; padding: 11px 14px; border: 1.5px solid #e2e8f0;
    border-radius: 8px; font-size: 14px; font-family: inherit;
    color: #1a202c; background: #fff; outline: none; cursor: pointer;
    margin-bottom: 20px; transition: border-color .2s;
}
.ind-modal select:focus { border-color: var(--orange); }
.ind-modal-actions { display: flex; gap: 12px; }
.ind-btn-confirm {
    flex: 1; background: var(--orange); color: white; border: none;
    padding: 13px 20px; border-radius: 10px; font-size: 14px; font-weight: 600;
    cursor: pointer; transition: background .2s;
}
.ind-btn-confirm:hover { background: var(--orange-dark); }
.ind-btn-confirm:disabled { opacity: .6; cursor: not-allowed; }
.ind-btn-cancel {
    flex: 1; background: transparent; color: var(--orange);
    border: 1.5px solid var(--orange); padding: 12px 20px; border-radius: 10px;
    font-size: 14px; font-weight: 500; cursor: pointer; transition: all .2s;
}
.ind-btn-cancel:hover { background: #fff4ee; }

/* ── QB Confirm Modal ───────────────────────────────────────────── */
.qb-modal-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,.45);
    z-index: 9998; display: none; align-items: center; justify-content: center;
}
.qb-modal-overlay.open { display: flex; }
.qb-modal {
    background: white; border-radius: 20px;
    padding: 40px 40px 32px; width: 520px; max-width: 95vw;
    text-align: center; position: relative;
    animation: qbSlide .25s ease;
    box-shadow: 0 20px 60px rgba(0,0,0,.18);
}
@keyframes qbSlide { from { opacity: 0; transform: translateY(-18px); } to { opacity: 1; transform: translateY(0); } }
.qb-modal h3 { font-size: 24px; font-weight: 700; color: #1a202c; margin: 0 0 12px; }
.qb-modal .qb-modal-sub { font-size: 15px; color: #6b7280; line-height: 1.6; margin: 0 0 4px; }
.qb-modal ul { list-style: none; padding: 0; margin: 12px auto 10px; text-align: left; display: inline-block; }
.qb-modal ul li { font-size: 15px; color: #374151; padding: 3px 0; display: flex; align-items: center; gap: 10px; }
.qb-modal ul li::before { content: '○'; color: #6b7280; flex-shrink: 0; }
.qb-modal .qb-modal-timing { font-size: 15px; font-weight: 700; color: var(--orange); margin: 10px 0 24px; }
.qb-modal .qb-modal-actions { display: flex; flex-direction: row; gap: 12px; }
.qb-modal .btn-modal-confirm {
    flex: 1; background: var(--orange); color: white; border: none;
    padding: 16px 20px; border-radius: 10px; font-size: 15px; font-weight: 600;
    cursor: pointer; transition: background .2s;
    box-shadow: 0 5px 10px rgba(240,107,37,.3);
}
.qb-modal .btn-modal-confirm:hover { background: var(--orange-dark); }
.qb-modal .btn-modal-cancel {
    flex: 1; background: transparent; color: var(--orange);
    border: 1.5px solid var(--orange); padding: 15px 20px; border-radius: 10px;
    font-size: 15px; font-weight: 500; cursor: pointer; transition: all .2s;
}
.qb-modal .btn-modal-cancel:hover { background: #fff4ee; }
</style>

{{-- ── Processing Bar (shows when generation is in progress) ─────────────── --}}
<div id="dbProcessingBar" style="display:none;">
    <div class="db-thinking-bar">
        <div class="db-thinking-dots"><span></span><span></span><span></span></div>
        <span id="dbProcessingMsg">Syncing your QuickBooks data and generating AI insights… This may take a few minutes.</span>
        <a href="{{ route('insights.index') }}" style="margin-left:auto;white-space:nowrap;font-size:13px;color:var(--orange);text-decoration:none;font-weight:600;">View Progress →</a>
    </div>
</div>

<div class="user-db-header">
    <h1>Hi <span>{{ auth()->user()->first_name }}</span></h1>
    <p>
        You're one step away from unlocking your first CFO-level financial insights.
        @if(auth()->user()->can('view_quickbooks'))
        <a href="{{ route('quickbooks.index') }}">Connect QuickBooks</a> to proceed.
        @else
        Connect QuickBooks to proceed.
        @endif
    </p>
</div>

<div class="plan-grid">

    {{-- Card 1: What this platform does --}}
    <div class="plan-card">
        <div class="plan-card-title">What {{ $clientAdminName }} does</div>
        <p class="plan-card-desc">
            {{ $clientAdminName }} helps founders and finance leaders turn raw financial data into clear, CFO-level intelligence—so you can understand performance, monitor cash flow, and identify risks without manual spreadsheets.
        </p>
        <p class="plan-card-desc"><strong>It provides a structured platform for:</strong></p>
        <ul class="plan-feature-list">
            <li>
                <svg class="feat-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <div>
                    <div class="feat-title">Flexible data ingestion and visibility</div>
                    <div class="feat-body">Bring in data via QuickBooks to create a single source of financial truth.</div>
                </div>
            </li>
            <li>
                <svg class="feat-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <div>
                    <div class="feat-title">Live financial dashboards</div>
                    <div class="feat-body">Automatically generate and track key financial metrics as your data is connected.</div>
                </div>
            </li>
            <li>
                <svg class="feat-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <div>
                    <div class="feat-title">Deeper KPI analysis</div>
                    <div class="feat-body">Go beyond surface metrics with detailed KPI views that explain what's driving performance.</div>
                </div>
            </li>
            <li>
                <svg class="feat-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <div>
                    <div class="feat-title">Secure financial data management</div>
                    <div class="feat-body">Your data is handled through a secure, read-only system designed for financial accuracy and trust.</div>
                </div>
            </li>
            <li>
                <svg class="feat-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <div>
                    <div class="feat-title">Ongoing guidance and support</div>
                    <div class="feat-body">Built to support informed decision-making as your business grows.</div>
                </div>
            </li>
        </ul>
    </div>

    {{-- Cards 2+: Dynamic plan cards from client_admin --}}
    @forelse($plans ?? collect() as $plan)
        @php
            $isCurrent = $currentPlan && $currentPlan->id === $plan->id;
            $features  = is_array($plan->features) ? array_filter($plan->features) : [];
            $isFree    = (bool)($plan->is_free ?? false);
            $price     = $isFree ? null : '$' . number_format((float)($plan->monthly_price ?? 0), 2);
            $insightsLimit  = $plan->insights_per_month ?? $plan->max_insights_per_month ?? null;
            $companiesLimit = $plan->max_companies ?? $plan->max_users ?? null;
        @endphp
        <div class="plan-card">
            <div class="plan-card-title">
                {{ $plan->name }}
                @if($isCurrent)
                    <span class="plan-badge">CURRENT</span>
                @elseif($plan->is_recommended ?? false)
                    <span class="plan-star">&#9733;</span>
                @endif
            </div>
            @if($plan->description)
                <p class="plan-card-desc">{{ $plan->description }}</p>
            @endif

            @if(count($features))
                <ul class="plan-feature-list" style="margin-bottom:auto">
                    @foreach($features as $feature)
                        <li>
                            <svg class="feat-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            {{ $feature }}
                        </li>
                    @endforeach
                </ul>
            @endif

            @if($isCurrent && $isFree)
                <div class="entitlement-box">
                    <div class="entitlement-label">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                        Entitlement
                    </div>
                    <div class="entitlement-title">Complimentary access</div>
                    <div class="entitlement-sub">Get started with your financial dashboard at no cost.</div>
                </div>
            @elseif($price)
                <div class="plan-price-label" style="margin-top:24px">
                    {{ $price }}/MONTH
                    @if($insightsLimit)
                        &bull; INCLUDES {{ $insightsLimit }} ANALYSIS RUNS
                    @endif
                </div>
            @endif

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin:12px 0 16px;">
                <div style="border:1px solid var(--border);border-radius:8px;padding:10px 12px;background:var(--bg)">
                    <div style="font-size:10.5px;font-weight:700;letter-spacing:.05em;color:var(--text-3);text-transform:uppercase">Insights / Month</div>
                    <div style="font-size:15px;font-weight:700;color:var(--text-1);margin-top:2px">{{ $insightsLimit ?: '—' }}</div>
                </div>
                <div style="border:1px solid var(--border);border-radius:8px;padding:10px 12px;background:var(--bg)">
                    <div style="font-size:10.5px;font-weight:700;letter-spacing:.05em;color:var(--text-3);text-transform:uppercase">Max Companies</div>
                    <div style="font-size:15px;font-weight:700;color:var(--text-1);margin-top:2px">{{ $companiesLimit ?: 'Unlimited' }}</div>
                </div>
            </div>

            @if($isCurrent)
                @if(!empty($isQuickbooksConnected))
                    {{-- Generate Insights button — replaced by loader when processing --}}
                    <div style="margin-top:auto;">
                        <button type="button" id="dashGenerateBtn" class="plan-btn btn-orange"
                                onclick="dashTriggerGenerate()"
                                style="{{ !empty($insightInProgress) ? 'display:none;' : '' }}">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg>
                            Generate Insights
                        </button>
                        <div id="dashGeneratingLoader" class="dash-gen-loader"
                             style="{{ empty($insightInProgress) ? 'display:none;' : '' }}">
                            <span class="dash-gen-spinner"></span>
                            <span>Generating insights…</span>
                        </div>
                    </div>
                @else
                    <a href="{{ route('quickbooks.index') }}" class="plan-btn btn-orange-outline" style="margin-top:auto">
                        Connect QuickBooks
                    </a>
                @endif
            @else
                <form method="POST" action="{{ route('dashboard.assign-plan') }}" style="margin-top:auto">
                    @csrf
                    <input type="hidden" name="client_package_id" value="{{ $plan->id }}">
                    <button type="submit" class="plan-btn btn-orange-outline">
                        Upgrade to {{ $plan->name }} &rarr;
                    </button>
                </form>
            @endif
        </div>
    @empty
        {{-- No plans configured by client_admin yet --}}
        <div class="plan-card" style="grid-column:span 2;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:40px 32px;min-height:220px">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--text-3)" stroke-width="1.4" style="margin-bottom:14px"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>
            <div style="font-size:15px;font-weight:600;color:var(--text-1);margin-bottom:6px">No plans available yet</div>
            <div style="font-size:13px;color:var(--text-3);max-width:320px;line-height:1.6">
                Your account administrator hasn't set up any subscription plans yet. Please contact them for more information.
            </div>
        </div>
    @endforelse

</div>

{{-- ── Industry Type Selection Modal ─────────────────────────────────────── --}}
<div id="industryModal" class="ind-modal-overlay">
    <div class="ind-modal">
        <h3>What's your industry?</h3>
        <p>We use industry benchmarks to put your financials in context — from margins to cash flow trends. This helps our AI provide more relevant insights tailored to your business.</p>
        <select id="industrySelect">
            <option value="">— Select your industry —</option>
            <option value="Automotive">Automotive</option>
            <option value="Technology">Technology</option>
            <option value="Healthcare">Healthcare</option>
            <option value="Manufacturing">Manufacturing</option>
            <option value="Finance">Finance</option>
            <option value="Retail">Retail</option>
            <option value="Construction">Construction</option>
            <option value="Transportation">Transportation</option>
            <option value="Energy">Energy</option>
            <option value="Agriculture">Agriculture</option>
            <option value="Education">Education</option>
            <option value="Real Estate">Real Estate</option>
            <option value="Entertainment">Entertainment</option>
            <option value="Consulting">Consulting</option>
            <option value="Legal Services">Legal Services</option>
            <option value="Other">Other</option>
        </select>
        <div class="ind-modal-actions">
            <button class="ind-btn-confirm" id="indBtnConfirm" onclick="saveIndustryAndProceed()">Continue</button>
            <button class="ind-btn-cancel" onclick="closeIndustryModal()">Cancel</button>
        </div>
    </div>
</div>

{{-- ── QB Sync Confirm Modal ──────────────────────────────────────────────── --}}
<div id="dashQbModal" class="qb-modal-overlay">
    <div class="qb-modal">
        <div style="margin:0 auto 16px;display:flex;align-items:center;justify-content:center;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 70 70" width="70" height="70">
                <circle cx="35" cy="35" r="35" fill="#2CA01C"/>
                <circle cx="25" cy="36" r="11" fill="none" stroke="white" stroke-width="5.5"/>
                <line x1="32.5" y1="43.5" x2="38" y2="52.5" stroke="white" stroke-width="5.5" stroke-linecap="round"/>
                <line x1="44" y1="20" x2="44" y2="43" stroke="white" stroke-width="5.5" stroke-linecap="round"/>
                <circle cx="45.5" cy="45" r="10.5" fill="none" stroke="white" stroke-width="5.5"/>
            </svg>
        </div>
        <h3>Sync your QuickBooks account?</h3>
        <p class="qb-modal-sub">This will securely import your latest financial data (read-only) to generate:</p>
        <ul>
            <li>Cash flow insights</li>
            <li>Profitability trends</li>
            <li>Key financial KPIs</li>
        </ul>
        <p class="qb-modal-timing">Usually takes 5–10 minutes.</p>
        <div class="qb-modal-actions">
            <button class="btn-modal-confirm" onclick="dashDoGenerate()">Generate Insights</button>
            <button class="btn-modal-cancel" onclick="closeDashQbModal()">Not now</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const CSRF             = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const SAVE_CTX_URL     = '{{ route("insights.save-business-context") }}';
    const GENERATE_URL     = '{{ route("insights.generate") }}';
    const INSIGHTS_URL     = '{{ route("insights.index") }}';
    const HAS_BUSINESS_CTX = {{ auth()->user()->business_context ? 'true' : 'false' }};
    const LS_KEY           = 'dash_processing_insight_id';

    // ── On load: show loader if server reports an in-progress job ──────────────
    const serverInProgress = {{ !empty($insightInProgress) ? 'true' : 'false' }};
    if (serverInProgress) {
        showGeneratingLoader();
        showProcessingBar('Insight generation is still in progress… Please wait.');
    } else {
        localStorage.removeItem(LS_KEY);
    }

    // ── Entry point: "Generate Insights" button click ────────────────────────
    window.dashTriggerGenerate = function () {
        if (!HAS_BUSINESS_CTX) {
            openIndustryModal();
        } else {
            openQbModal();
        }
    };

    // ── Industry modal ────────────────────────────────────────────────────────
    window.openIndustryModal = function () {
        document.getElementById('industryModal').classList.add('open');
    };

    window.closeIndustryModal = function () {
        document.getElementById('industryModal').classList.remove('open');
    };

    window.saveIndustryAndProceed = function () {
        const select = document.getElementById('industrySelect');
        const value  = select.value;
        if (!value) {
            select.style.borderColor = '#ef4444';
            return;
        }
        select.style.borderColor = '';

        const btn = document.getElementById('indBtnConfirm');
        btn.disabled    = true;
        btn.textContent = 'Saving…';

        fetch(SAVE_CTX_URL, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body:    JSON.stringify({ business_context: value }),
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled    = false;
            btn.textContent = 'Continue';
            if (data.status === 'success') {
                closeIndustryModal();
                openQbModal();
            } else {
                alert(data.message || 'Could not save industry. Please try again.');
            }
        })
        .catch(() => {
            btn.disabled    = false;
            btn.textContent = 'Continue';
            alert('Network error. Please try again.');
        });
    };

    // ── QB confirm modal ──────────────────────────────────────────────────────
    window.openQbModal = function () {
        document.getElementById('dashQbModal').classList.add('open');
    };

    window.closeDashQbModal = function () {
        document.getElementById('dashQbModal').classList.remove('open');
    };

    // ── Actually dispatch insight generation ──────────────────────────────────
    window.dashDoGenerate = function () {
        closeDashQbModal();
        showGeneratingLoader();
        showProcessingBar('Syncing QuickBooks data and generating AI insights… This may take a few minutes.');

        fetch(GENERATE_URL, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body:    JSON.stringify({}),
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                // Job ran synchronously — redirect to insights page to view the result
                localStorage.removeItem(LS_KEY);
                window.location.href = INSIGHTS_URL;
            } else {
                hideProcessingBar();
                resetDashBtn();
                Swal.fire({ icon: 'error', title: 'Error', text: data.message });
            }
        })
        .catch(() => {
            hideProcessingBar();
            resetDashBtn();
            Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong. Please try again.' });
        });
    };

    // ── Helpers ───────────────────────────────────────────────────────────────
    function showProcessingBar(msg) {
        const bar = document.getElementById('dbProcessingBar');
        const msgEl = document.getElementById('dbProcessingMsg');
        if (bar) bar.style.display = '';
        if (msgEl && msg) msgEl.textContent = msg;
    }

    function hideProcessingBar() {
        const bar = document.getElementById('dbProcessingBar');
        if (bar) bar.style.display = 'none';
    }

    function showGeneratingLoader() {
        const btn    = document.getElementById('dashGenerateBtn');
        const loader = document.getElementById('dashGeneratingLoader');
        if (btn)    btn.style.display    = 'none';
        if (loader) loader.style.display = 'flex';
    }

    function resetDashBtn() {
        const btn    = document.getElementById('dashGenerateBtn');
        const loader = document.getElementById('dashGeneratingLoader');
        if (btn)    { btn.style.display = ''; btn.disabled = false; btn.style.opacity = ''; }
        if (loader) loader.style.display = 'none';
    }
}());
</script>
@endpush
