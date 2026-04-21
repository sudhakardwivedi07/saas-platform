@extends('layouts.app')

@section('page-title', 'Client Detail')

@section('content')

{{-- Back Button --}}
<a href="{{ route('clients.index') }}" class="back-btn">
    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
    </svg>
    Back to Clients
</a>

{{-- Client Header Card --}}
<div class="cd-header-card" style="margin-bottom:1.5rem;">
    <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">

        {{-- Avatar --}}
        <div class="cd-avatar-ring">
            @php
                $initials = collect(explode(' ', $client->name))
                    ->map(fn($w) => strtoupper(substr($w, 0, 1)))
                    ->take(2)
                    ->implode('');
            @endphp
            <span style="font-size:1.25rem;font-weight:700;color:#fff;letter-spacing:0.03em;">{{ $initials }}</span>
        </div>

        {{-- Name & Email --}}
        <div style="flex:1;min-width:0;">
            <div class="cd-name">{{ $client->name }}</div>
            <div class="cd-email">{{ $client->email }}</div>
        </div>

        {{-- Quick Actions --}}
        <div style="display:flex;gap:8px;">
            <button class="btn btn-ghost btn-sm">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Email
            </button>
            <button class="btn btn-secondary btn-sm">Edit Client</button>
        </div>
    </div>

    {{-- Stat Chips --}}
    <div class="cd-stat-chips" style="margin-top:1.25rem;">

        <div class="cd-chip">
            <span class="cd-chip-val">{{ $client->company ? 1 : 0 }}</span>
            <span class="cd-chip-lbl">Companies</span>
        </div>

        <div class="cd-chip">
            @if($client->qb_connected ?? false)
                <span class="cd-chip-val" style="color:#16a34a;">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="display:inline-block;vertical-align:-2px;margin-right:3px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Connected
                </span>
            @else
                <span class="cd-chip-val" style="color:var(--text-3);">None</span>
            @endif
            <span class="cd-chip-lbl">QB Status</span>
        </div>

        <div class="cd-chip">
            <span class="cd-chip-val">$0</span>
            <span class="cd-chip-lbl">MRR</span>
        </div>

        <div class="cd-chip">
            @php
                $clientStatus = $client->status ?? 'active';
                $statusColor  = $clientStatus === 'active' ? '#16a34a' : '#dc2626';
            @endphp
            <span class="cd-chip-val">
                <span class="badge {{ $clientStatus === 'active' ? 'b-green' : 'b-red' }}">
                    {{ ucfirst($clientStatus) }}
                </span>
            </span>
            <span class="cd-chip-lbl">Status</span>
        </div>

    </div>
</div>

{{-- Tabs --}}
<div class="tab-bar" id="clientTabBar">
    <button class="tab-btn active" onclick="switchTab('overview', this)">Overview</button>
    <button class="tab-btn" onclick="switchTab('companies', this)">
        Companies
        <span style="margin-left:5px;background:var(--blue);color:#fff;font-size:0.65rem;font-weight:700;padding:1px 6px;border-radius:99px;">
            {{ $client->company ? 1 : 0 }}
        </span>
    </button>
    <button class="tab-btn" onclick="switchTab('activity', this)">Activity</button>
</div>

{{-- ==================== TAB: Overview ==================== --}}
<div class="tab-panel" id="tab-overview" style="display:block;">
    <div class="cd-section-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-top:1.25rem;">

        {{-- Notes --}}
        <div class="card">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
                <div class="card-title">Notes</div>
                <button class="btn btn-ghost btn-xs" id="saveNotesBtn" onclick="saveNotes()" style="display:none;">Save</button>
            </div>
            <textarea id="clientNotes"
                      style="width:100%;min-height:160px;border:1px solid var(--border);border-radius:8px;padding:10px 12px;font-size:0.875rem;color:var(--text-1);resize:vertical;line-height:1.6;outline:none;transition:border-color .15s;"
                      placeholder="Add notes about this client..."
                      oninput="document.getElementById('saveNotesBtn').style.display='inline-flex'">{{ $client->notes ?? '' }}</textarea>
        </div>

        {{-- Timeline --}}
        <div class="card">
            <div class="card-title" style="margin-bottom:1rem;">Timeline</div>
            <div style="position:relative;padding-left:20px;">

                {{-- Timeline line --}}
                <div style="position:absolute;left:7px;top:8px;bottom:8px;width:2px;background:var(--border);border-radius:2px;"></div>

                @php
                    $timelineItems = [
                        ['label' => 'Client account created',       'date' => $client->created_at, 'color' => 'var(--blue)'],
                        ['label' => 'First login recorded',         'date' => $client->last_login_at ?? null, 'color' => '#16a34a'],
                        ['label' => 'Profile last updated',         'date' => $client->updated_at,  'color' => 'var(--orange)'],
                    ];
                @endphp

                @foreach($timelineItems as $item)
                <div style="position:relative;margin-bottom:1.25rem;display:flex;align-items:flex-start;gap:10px;">
                    <div style="width:14px;height:14px;border-radius:50%;background:{{ $item['color'] }};flex-shrink:0;margin-top:2px;box-shadow:0 0 0 3px #fff,0 0 0 4px {{ $item['color'] }}44;"></div>
                    <div>
                        <div style="font-size:0.875rem;font-weight:500;color:var(--text-1);">{{ $item['label'] }}</div>
                        <div style="font-size:0.75rem;color:var(--text-3);margin-top:1px;">
                            {{ $item['date'] ? \Carbon\Carbon::parse($item['date'])->format('M j, Y \a\t g:i A') : 'Not yet' }}
                        </div>
                    </div>
                </div>
                @endforeach

                @if($client->company)
                <div style="position:relative;display:flex;align-items:flex-start;gap:10px;">
                    <div style="width:14px;height:14px;border-radius:50%;background:#7c3aed;flex-shrink:0;margin-top:2px;box-shadow:0 0 0 3px #fff,0 0 0 4px #7c3aed44;"></div>
                    <div>
                        <div style="font-size:0.875rem;font-weight:500;color:var(--text-1);">
                            1 company connected
                        </div>
                        <div style="font-size:0.75rem;color:var(--text-3);margin-top:1px;">Active</div>
                    </div>
                </div>
                @endif

            </div>
        </div>

    </div>
</div>

{{-- ==================== TAB: Companies ==================== --}}
<div class="tab-panel" id="tab-companies" style="display:none;margin-top:1.25rem;">
    <div class="card">
        <div style="margin-bottom:1.25rem;">
            <div class="card-title">Companies</div>
            <div class="card-sub">All companies associated with {{ $client->name }}.</div>
        </div>

        @if(!$client->company)
            <div style="text-align:center;padding:2.5rem 1rem;">
                <div style="width:48px;height:48px;border-radius:50%;background:#f0f4f8;display:flex;align-items:center;justify-content:center;margin:0 auto 0.75rem auto;">
                    <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="var(--text-3)" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <p style="font-size:0.9rem;font-weight:600;color:var(--text-1);margin:0 0 4px 0;">No companies linked</p>
                <p style="font-size:0.8125rem;color:var(--text-2);margin:0;">This client has no companies connected yet.</p>
            </div>
        @else
        @php $company = $client->company; $isQB = $company->quickbooksConnections?->where('is_active', true)->isNotEmpty(); @endphp
            <div class="tc">
                <table style="width:100%;border-collapse:collapse">
                    <thead>
                        <tr>
                            <th>Company Name</th>
                            <th>QB Connected</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div style="font-weight:600;color:var(--text-1);font-size:0.875rem;">{{ $company->company_name }}</div>
                                @if($company->company_email)
                                    <div style="font-size:0.75rem;color:var(--text-2);">{{ $company->company_email }}</div>
                                @endif
                            </td>
                            <td>
                                @if($isQB)
                                    <span class="badge b-green">Connected</span>
                                @else
                                    <span class="badge b-gray">Not connected</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ ($company->status ?? 'active') === 'active' ? 'b-green' : 'b-gray' }}">
                                    {{ ucfirst($company->status ?? 'active') }}
                                </span>
                            </td>
                            <td style="font-size:0.8125rem;color:var(--text-2);">
                                {{ $company->created_at?->format('M j, Y') ?? '—' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- ==================== TAB: Activity ==================== --}}
<div class="tab-panel" id="tab-activity" style="display:none;margin-top:1.25rem;">
    <div class="card">
        <div class="card-title" style="margin-bottom:0.5rem;">Activity</div>
        <div class="card-sub" style="margin-bottom:1.5rem;">Recent actions taken by or on behalf of this client.</div>
        <div style="text-align:center;padding:2.5rem 1rem;">
            <div style="width:48px;height:48px;border-radius:50%;background:#f0f4f8;display:flex;align-items:center;justify-content:center;margin:0 auto 0.75rem auto;">
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="var(--text-3)" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p style="font-size:0.9rem;font-weight:600;color:var(--text-1);margin:0 0 4px 0;">No activity recorded</p>
            <p style="font-size:0.8125rem;color:var(--text-2);margin:0;">Activity for this client will appear here once events are logged.</p>
        </div>
    </div>
</div>

<script>
function switchTab(tabName, btn) {
    document.querySelectorAll('.tab-panel').forEach(function(p) { p.style.display = 'none'; });
    document.querySelectorAll('#clientTabBar .tab-btn').forEach(function(b) { b.classList.remove('active'); });
    var panel = document.getElementById('tab-' + tabName);
    if (panel) panel.style.display = 'block';
    btn.classList.add('active');
}
function saveNotes() {
    var notes = document.getElementById('clientNotes').value;
    fetch('/clients/{{ $client->id }}/notes', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ notes: notes })
    }).then(function(r) {
        if (r.ok) {
            document.getElementById('saveNotesBtn').style.display = 'none';
        }
    });
}
</script>

@endsection
