@extends('layouts.app')
@section('page-title', 'Dashboard')

@section('content')

<div class="ph">
    <div class="ph-left">
        <h1>Welcome, {{ auth()->user()->name }}</h1>
        <p>Here's a quick overview of your account</p>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;margin-bottom:24px">

    @if(auth()->user()->can('view_documents'))
    <a href="{{ route('documents.index') }}" style="text-decoration:none">
        <div class="acc-card" style="border-left:4px solid var(--blue);cursor:pointer;transition:box-shadow .15s" onmouseover="this.style.boxShadow='0 4px 18px rgba(0,0,0,.1)'" onmouseout="this.style.boxShadow=''">
            <div style="display:flex;align-items:center;gap:12px">
                <div style="width:40px;height:40px;border-radius:10px;background:var(--blue-pale);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                <div>
                    <div style="font-size:13px;font-weight:600;color:var(--text-1)">Documents</div>
                    <div style="font-size:12px;color:var(--text-3);margin-top:2px">View &amp; download files</div>
                </div>
            </div>
        </div>
    </a>
    @endif

    @if(auth()->user()->can('view_quickbooks'))
    <a href="{{ route('quickbooks.index') }}" style="text-decoration:none">
        <div class="acc-card" style="border-left:4px solid var(--green);cursor:pointer;transition:box-shadow .15s" onmouseover="this.style.boxShadow='0 4px 18px rgba(0,0,0,.1)'" onmouseout="this.style.boxShadow=''">
            <div style="display:flex;align-items:center;gap:12px">
                <div style="width:40px;height:40px;border-radius:10px;background:var(--green-light);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2"><path d="M5 12.55a11 11 0 0114.08 0"/><path d="M1.42 9a16 16 0 0121.16 0"/><path d="M8.53 16.11a6 6 0 016.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>
                </div>
                <div>
                    <div style="font-size:13px;font-weight:600;color:var(--text-1)">QuickBooks</div>
                    <div style="font-size:12px;color:var(--text-3);margin-top:2px">View QB connections</div>
                </div>
            </div>
        </div>
    </a>
    @endif

    <a href="{{ route('support.index') }}" style="text-decoration:none">
        <div class="acc-card" style="border-left:4px solid var(--orange);cursor:pointer;transition:box-shadow .15s" onmouseover="this.style.boxShadow='0 4px 18px rgba(0,0,0,.1)'" onmouseout="this.style.boxShadow=''">
            <div style="display:flex;align-items:center;gap:12px">
                <div style="width:40px;height:40px;border-radius:10px;background:var(--orange-light);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--orange)" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                </div>
                <div>
                    <div style="font-size:13px;font-weight:600;color:var(--text-1)">Support</div>
                    <div style="font-size:12px;color:var(--text-3);margin-top:2px">Get help &amp; raise tickets</div>
                </div>
            </div>
        </div>
    </a>

    <a href="{{ route('account.index') }}" style="text-decoration:none">
        <div class="acc-card" style="border-left:4px solid var(--purple);cursor:pointer;transition:box-shadow .15s" onmouseover="this.style.boxShadow='0 4px 18px rgba(0,0,0,.1)'" onmouseout="this.style.boxShadow=''">
            <div style="display:flex;align-items:center;gap:12px">
                <div style="width:40px;height:40px;border-radius:10px;background:var(--purple-light);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--purple)" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <div>
                    <div style="font-size:13px;font-weight:600;color:var(--text-1)">My Account</div>
                    <div style="font-size:12px;color:var(--text-3);margin-top:2px">Profile &amp; password</div>
                </div>
            </div>
        </div>
    </a>

</div>

{{-- Account Info Card --}}
<div class="acc-card">
    <div class="acc-section-title">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="2" style="display:inline-block;vertical-align:middle;margin-right:6px"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Account Details
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;margin-top:14px">
        <div>
            <div style="font-size:11.5px;color:var(--text-3);text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px">Name</div>
            <div style="font-size:13.5px;color:var(--text-1);font-weight:500">{{ auth()->user()->name }}</div>
        </div>
        <div>
            <div style="font-size:11.5px;color:var(--text-3);text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px">Email</div>
            <div style="font-size:13.5px;color:var(--text-1);font-weight:500">{{ auth()->user()->email }}</div>
        </div>
        <div>
            <div style="font-size:11.5px;color:var(--text-3);text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px">Role</div>
            <div><span class="badge b-blue" style="font-size:12px">{{ ucwords(str_replace('_', ' ', auth()->user()->getRoleNames()->first() ?? 'user')) }}</span></div>
        </div>
        <div>
            <div style="font-size:11.5px;color:var(--text-3);text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px">Company</div>
            <div style="font-size:13.5px;color:var(--text-1);font-weight:500">{{ auth()->user()->company?->company_name ?? '—' }}</div>
        </div>
        <div>
            <div style="font-size:11.5px;color:var(--text-3);text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px">Member Since</div>
            <div style="font-size:13.5px;color:var(--text-1);font-weight:500">{{ auth()->user()->created_at->format('M j, Y') }}</div>
        </div>
        <div>
            <div style="font-size:11.5px;color:var(--text-3);text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px">Status</div>
            <div>
                @php $st = auth()->user()->status ?? 'active'; @endphp
                <span class="badge {{ $st === 'active' ? 'b-green' : 'b-gray' }}">
                    <span class="badge-dot" style="background:{{ $st === 'active' ? 'var(--green)' : 'var(--text-3)' }}"></span>
                    {{ ucfirst($st) }}
                </span>
            </div>
        </div>
    </div>
</div>

@endsection
