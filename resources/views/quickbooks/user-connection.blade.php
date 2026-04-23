@extends('layouts.app')
@section('title', 'QuickBooks Integration')
@section('page-title', 'QuickBooks Integration')

@section('content')

<div class="ph">
    <div class="ph-left">
        <h1>QuickBooks Integration</h1>
        <p>Connect your QuickBooks account to sync your financial data</p>
    </div>
</div>

@php
    $conn         = $connection ?? null;
    $tokenActive  = $conn && !$conn->isAccessTokenExpired();
    $daysLeft     = $conn && $conn->refresh_token_expires_at
                        ? max(0, (int) now()->diffInDays($conn->refresh_token_expires_at, false))
                        : null;
@endphp

<div style="max-width:700px">

    @if($conn && $conn->is_active)
    {{-- ── Connected State ─────────────────────────────────────────── --}}
    <div class="acc-card">
        <div style="font-size:15px;font-weight:600;margin-bottom:16px;padding-bottom:14px;border-bottom:1px solid var(--border)">
            Connection Status
        </div>

        {{-- Status badge --}}
        <div style="margin-bottom:20px">
            <span style="display:inline-flex;align-items:center;gap:7px;background:#166534;color:#fff;padding:6px 16px;border-radius:8px;font-size:13.5px;font-weight:600">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg>
                Connected
            </span>
        </div>

        {{-- Details grid --}}
        <div style="display:flex;flex-direction:column;gap:14px;margin-bottom:22px">

            {{-- Company Name --}}
            @if($conn->qb_company_name)
            <div>
                <div style="font-size:11.5px;color:var(--text-3);margin-bottom:3px">Company Name</div>
                <div style="font-size:14.5px;font-weight:500;color:var(--text-1)">{{ $conn->qb_company_name }}</div>
            </div>
            @endif

            {{-- Realm ID --}}
            <div>
                <div style="font-size:11.5px;color:var(--text-3);margin-bottom:3px">Realm ID</div>
                <div style="font-size:14px;color:var(--text-1);font-family:monospace">{{ $conn->realm_id }}</div>
            </div>

            {{-- Connected Since --}}
            <div>
                <div style="font-size:11.5px;color:var(--text-3);margin-bottom:3px">Connected Since</div>
                <div style="font-size:14px;color:var(--text-1)">{{ $conn->created_at?->format('M d, Y h:i A') ?? '—' }}</div>
            </div>

            {{-- Access Token Expiry --}}
            <div>
                <div style="font-size:11.5px;color:var(--text-3);margin-bottom:4px">Access Token Expires</div>
                <div style="display:flex;align-items:center;gap:9px;flex-wrap:wrap">
                    <span style="font-size:14px;color:var(--text-1)" id="token-expires-at">
                        {{ $conn->access_token_expires_at?->format('M d, Y h:i A') ?? '—' }}
                    </span>
                    @if($tokenActive)
                    <span style="background:#166534;color:#fff;border-radius:5px;padding:2px 9px;font-size:11.5px;font-weight:600">Active</span>
                    @else
                    <span style="background:#991b1b;color:#fff;border-radius:5px;padding:2px 9px;font-size:11.5px;font-weight:600">Expired</span>
                    @endif
                </div>
                <div style="font-size:11.5px;color:var(--text-3);margin-top:3px">Auto-refreshes when needed</div>
            </div>

            {{-- Refresh Token (Authorization) Expiry --}}
            @if($conn->refresh_token_expires_at)
            <div>
                <div style="font-size:11.5px;color:var(--text-3);margin-bottom:4px">Authorization Valid Until</div>
                <div style="display:flex;align-items:center;gap:9px;flex-wrap:wrap">
                    <span style="font-size:14px;color:var(--text-1)">{{ $conn->refresh_token_expires_at->format('M d, Y h:i A') }}</span>
                    @if($daysLeft !== null)
                    <span style="background:{{ $daysLeft > 14 ? '#166534' : '#92400e' }};color:#fff;border-radius:5px;padding:2px 9px;font-size:11.5px;font-weight:600">
                        {{ $daysLeft }} {{ $daysLeft === 1 ? 'day' : 'days' }} remaining
                    </span>
                    @endif
                </div>
                <div style="font-size:11.5px;color:var(--text-3);margin-top:3px">Reconnect required after this date</div>
            </div>
            @endif

        </div>

        {{-- Action buttons --}}
        <div style="display:flex;gap:10px;flex-wrap:wrap">
            <button onclick="testConnection(this)" class="btn btn-secondary" style="min-width:150px;justify-content:center">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M5 12.55a11 11 0 0114.08 0"/><path d="M1.42 9a16 16 0 0121.16 0"/><path d="M8.53 16.11a6 6 0 016.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>
                Test Connection
            </button>
            <button onclick="refreshToken(this)" style="min-width:150px;justify-content:center;display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;border:none;background:#0e9f8e;color:#fff;font-family:inherit;white-space:nowrap;transition:background .14s" onmouseover="this.style.background='#0b867a'" onmouseout="this.style.background='#0e9f8e'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/></svg>
                Refresh Token
            </button>
            <button onclick="disconnectQB(this)" class="btn btn-danger" style="min-width:120px;justify-content:center">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><line x1="2" y1="2" x2="22" y2="22"/><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
                Disconnect
            </button>
        </div>
    </div>

    @else
    {{-- ── Not Connected State ─────────────────────────────────────── --}}
    <div class="acc-card">
        <div style="font-size:15px;font-weight:600;margin-bottom:16px;padding-bottom:14px;border-bottom:1px solid var(--border)">
            Connection Status
        </div>

        <div style="display:flex;flex-direction:column;align-items:center;text-align:center;padding:28px 0 16px">
            <div style="width:64px;height:64px;border-radius:14px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;margin-bottom:16px">
                <svg viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.8" width="32" height="32">
                    <line x1="2" y1="2" x2="22" y2="22"/>
                    <path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/>
                    <path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/>
                </svg>
            </div>
            <div style="font-size:14.5px;color:var(--text-2);margin-bottom:22px">QuickBooks is not connected yet.</div>
            <a href="{{ route('quickbooks.user-connect') }}"
               style="display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:11px 32px;background:var(--orange);color:#fff;border-radius:8px;font-size:14px;font-weight:600;text-decoration:none;transition:background .14s;min-width:240px"
               onmouseover="this.style.background='var(--orange-dark)'" onmouseout="this.style.background='var(--orange)'">
                Connect QuickBooks
            </a>
        </div>
    </div>
    @endif

</div>

@endsection

@push('scripts')
<script>
const QB_ROUTES = {
    testConnection : '{{ route('quickbooks.user-test-connection') }}',
    refreshToken   : '{{ route('quickbooks.user-refresh-token') }}',
    disconnect     : '{{ route('quickbooks.user-disconnect') }}',
};
const CSRF = document.querySelector('meta[name=csrf-token]').content;

async function testConnection(btn) {
    btn.disabled = true;
    const orig = btn.innerHTML;
    btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" style="animation:spin 1s linear infinite"><polyline points="23 4 23 10 17 10"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10"/></svg> Testing…';

    const res = await fetch(QB_ROUTES.testConnection, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
    }).then(r => r.json()).catch(() => ({ status: 'fail', message: 'Request failed.' }));

    showToast(res.message, res.status === 'success' ? 'success' : 'error');
    btn.disabled = false;
    btn.innerHTML = orig;
}

async function refreshToken(btn) {
    btn.disabled = true;
    const orig = btn.innerHTML;
    btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" style="animation:spin 1s linear infinite"><polyline points="23 4 23 10 17 10"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10"/></svg> Refreshing…';

    const res = await fetch(QB_ROUTES.refreshToken, {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
    }).then(r => r.json()).catch(() => ({ status: 'fail', message: 'Request failed.' }));

    showToast(res.message, res.status === 'success' ? 'success' : 'error');
    if (res.status === 'success') {
        const el = document.getElementById('token-expires-at');
        if (el && res.access_token_expires_at) el.textContent = res.access_token_expires_at;
    }
    btn.disabled = false;
    btn.innerHTML = orig;
}

async function disconnectQB(btn) {
    if (!confirm('Disconnect QuickBooks? You can reconnect at any time.')) return;
    btn.disabled = true;
    btn.textContent = 'Disconnecting…';

    const res = await fetch(QB_ROUTES.disconnect, {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
    }).then(r => r.json()).catch(() => ({ status: 'fail', message: 'Request failed.' }));

    showToast(res.message, res.status === 'success' ? 'success' : 'error');
    if (res.status === 'success') setTimeout(() => location.reload(), 900);
    else { btn.disabled = false; btn.textContent = 'Disconnect'; }
}
</script>
<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>
@endpush
