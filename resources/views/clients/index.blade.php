@extends('layouts.app')
@section('page-title', 'Clients')

@section('content')

{{-- Page Header --}}
<div class="ph">
    <div class="ph-left">
        <h1>Clients</h1>
        <p>Manage your clients and their companies</p>
    </div>
    <div class="ph-actions">
        <button class="btn btn-primary" onclick="document.getElementById('add-client-modal').classList.add('open')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Client
        </button>
    </div>
</div>

{{-- Tab Bar --}}
<div class="tab-bar">
    <button class="tab-btn active" data-tab="tab-clients">
        Clients
        <span class="tab-badge">{{ $clients->count() }}</span>
    </button>
    <button class="tab-btn" data-tab="tab-companies">
        Companies
        <span class="tab-badge">{{ $companies->count() }}</span>
    </button>
</div>

{{-- Tab: Clients --}}
<div class="tab-panel active" id="tab-clients">
    <div class="tc">
        <div class="tc-head">
            <div class="tc-filters">
                <div class="srch-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" class="srch-inp" placeholder="Search clients…" id="client-search">
                </div>
                <select class="sel" id="client-status-filter">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>
        </div>
        <div style="overflow-x:auto">
            <table>
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Companies</th>
                        <th>QB Status</th>
                        <th>Plan</th>
                        <th>MRR</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th style="width:48px"></th>
                    </tr>
                </thead>
                <tbody id="clients-tbody">
                    @forelse($clients as $client)
                    <tr data-name="{{ strtolower($client->name) }}" data-email="{{ strtolower($client->email) }}" data-status="{{ $client->status ?? 'active' }}">
                        <td>
                            <div style="display:flex;align-items:center;gap:10px">
                                <div style="width:32px;height:32px;border-radius:8px;background:var(--orange);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;flex-shrink:0;letter-spacing:-.3px">
                                    {{ strtoupper(substr($client->name, 0, 1)) }}{{ strtoupper(substr(strstr($client->name, ' ') ?: ' ', 1, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight:500;font-size:13px;color:var(--text-1)">{{ $client->name }}</div>
                                    <div style="font-size:11.5px;color:var(--text-3)">{{ $client->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span style="font-weight:600;color:var(--text-1)">{{ $client->companies->count() }}</span>
                            <span style="color:var(--text-3);font-size:12px"> co.</span>
                        </td>
                        <td>
                            @php $qbCount = $client->companies->filter(fn($c) => $c->qb_connected ?? false)->count(); @endphp
                            @if($qbCount > 0)
                                <span class="badge b-green"><span class="badge-dot" style="background:var(--green)"></span>Connected</span>
                            @else
                                <span class="badge b-gray"><span class="badge-dot" style="background:var(--text-3)"></span>Not Connected</span>
                            @endif
                        </td>
                        <td>
                            @php $firstCompany = $client->companies->first(); @endphp
                            @if($firstCompany)
                                <span style="font-size:12.5px;color:var(--text-2)">
                                    {{ $firstCompany->clientPackage?->name ?? $firstCompany->package?->name ?? '—' }}
                                </span>
                            @else
                                <span style="color:var(--text-3)">—</span>
                            @endif
                        </td>
                        <td style="font-weight:600;color:var(--text-1)">$0</td>
                        <td>
                            @php $status = $client->status ?? 'active'; @endphp
                            @if($status === 'active')
                                <span class="badge b-green"><span class="badge-dot" style="background:var(--green)"></span>Active</span>
                            @elseif($status === 'suspended')
                                <span class="badge b-red"><span class="badge-dot" style="background:var(--red)"></span>Suspended</span>
                            @else
                                <span class="badge b-gray"><span class="badge-dot" style="background:var(--text-3)"></span>Inactive</span>
                            @endif
                        </td>
                        <td style="font-size:12.5px;color:var(--text-2);white-space:nowrap">
                            {{ $client->created_at->format('M j, Y') }}
                        </td>
                        <td>
                            <div class="dm-wrap">
                                <button class="dot-menu" data-menu="dm-client-{{ $client->id }}" title="Actions">•••</button>
                                <div class="dm-popup" id="dm-client-{{ $client->id }}">
                                    <a href="{{ route('clients.show', $client) }}" class="dm-item">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        View Client
                                    </a>
                                    <a href="{{ route('clients.edit', $client) }}" class="dm-item">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        Edit
                                    </a>
                                    <div class="dm-divider"></div>
                                    <button class="dm-item dm-danger" onclick="confirmDeleteClient({{ $client->id }}, '{{ addslashes($client->name) }}')">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div style="text-align:center;padding:48px 20px">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--text-3)" stroke-width="1.4" style="margin:0 auto 12px;display:block"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                                <div style="font-size:14px;font-weight:500;color:var(--text-2)">No clients yet</div>
                                <div style="font-size:12.5px;color:var(--text-3);margin-top:4px">Add your first client to get started.</div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Tab: Companies --}}
<div class="tab-panel" id="tab-companies">
    <div class="tc">
        <div class="tc-head">
            <div class="tc-filters">
                <div class="srch-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" class="srch-inp" placeholder="Search companies…" id="company-search">
                </div>
            </div>
        </div>
        <div style="overflow-x:auto">
            <table>
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Client</th>
                        <th>Plan</th>
                        <th>Users</th>
                        <th>QB</th>
                        <th>Health</th>
                        <th>Status</th>
                        <th style="width:48px"></th>
                    </tr>
                </thead>
                <tbody id="companies-tbody">
                    @forelse($companies as $company)
                    @php
                        $owner = $company->users()->whereHas('roles', fn($q) => $q->where('name', 'ClientAdmin'))->first()
                            ?? $company->users()->first();
                        $planName = $company->clientPackage?->name ?? $company->package?->name ?? '—';
                        $userCount = $company->users()->count();
                        $compStatus = $company->status ?? 'active';
                        $initials = strtoupper(substr($company->company_name, 0, 1))
                                  . strtoupper(substr(strstr($company->company_name, ' ') ?: '  ', 1, 1));
                    @endphp
                    <tr data-name="{{ strtolower($company->company_name) }}" data-subdomain="{{ strtolower($company->subdomain ?? '') }}">
                        <td>
                            <div style="display:flex;align-items:center;gap:10px">
                                <div style="width:32px;height:32px;border-radius:8px;background:var(--blue);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;flex-shrink:0">
                                    {{ $initials }}
                                </div>
                                <div>
                                    <div style="font-weight:500;font-size:13px;color:var(--text-1)">{{ $company->company_name }}</div>
                                    @if($company->subdomain)
                                    <div style="font-size:11.5px;color:var(--text-3)">{{ $company->subdomain }}.{{ config('app.domain', 'saas.app') }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td style="font-size:13px;color:var(--text-2)">
                            {{ $owner?->name ?? '—' }}
                        </td>
                        <td>
                            <span style="font-size:12.5px;color:var(--text-2)">{{ $planName }}</span>
                        </td>
                        <td style="font-weight:500;color:var(--text-1)">{{ $userCount }}</td>
                        <td>
                            @if($company->qb_connected ?? false)
                                <span class="badge b-green" style="font-size:11px">Connected</span>
                            @else
                                <span class="badge b-gray" style="font-size:11px">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge b-green" style="font-size:11px">Good</span>
                        </td>
                        <td>
                            @if($compStatus === 'active')
                                <span class="badge b-green"><span class="badge-dot" style="background:var(--green)"></span>Active</span>
                            @elseif($compStatus === 'suspended')
                                <span class="badge b-red"><span class="badge-dot" style="background:var(--red)"></span>Suspended</span>
                            @elseif($compStatus === 'trialing')
                                <span class="badge b-amber"><span class="badge-dot" style="background:var(--amber)"></span>Trial</span>
                            @else
                                <span class="badge b-gray"><span class="badge-dot" style="background:var(--text-3)"></span>Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="dm-wrap">
                                <button class="dot-menu" data-menu="dm-co-{{ $company->id }}" title="Actions">•••</button>
                                <div class="dm-popup" id="dm-co-{{ $company->id }}">
                                    <a href="{{ route('companies.show', $company) }}" class="dm-item">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        View
                                    </a>
                                    <button class="dm-item" disabled style="opacity:.45;cursor:not-allowed" title="Not available for client admins">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                                        Login As
                                    </button>
                                    <a href="{{ route('companies.edit', $company) }}" class="dm-item">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        Edit
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div style="text-align:center;padding:48px 20px">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--text-3)" stroke-width="1.4" style="margin:0 auto 12px;display:block"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>
                                <div style="font-size:14px;font-weight:500;color:var(--text-2)">No companies yet</div>
                                <div style="font-size:12.5px;color:var(--text-3);margin-top:4px">Companies will appear here once clients are added.</div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Client Modal --}}
<div class="modal-overlay" id="add-client-modal">
    <div class="modal-box">
        <button class="modal-close" onclick="document.getElementById('add-client-modal').classList.remove('open')">&times;</button>
        <div class="modal-title">Add New Client</div>
        <div class="modal-sub">Create a new client account and their primary company.</div>

        <form method="POST" action="{{ route('clients.store') }}">
            @csrf
            <div class="mf-field">
                <label class="mf-label">Full Name</label>
                <input type="text" name="name" class="mf-inp" placeholder="Jane Smith" required>
            </div>
            <div class="mf-field">
                <label class="mf-label">Email Address</label>
                <input type="email" name="email" class="mf-inp" placeholder="jane@example.com" required>
            </div>
            <div class="mf-field">
                <label class="mf-label">Password</label>
                <input type="password" name="password" class="mf-inp" placeholder="Min. 8 characters" required>
            </div>
            <div class="mf-field">
                <label class="mf-label">Company Name</label>
                <input type="text" name="company_name" class="mf-inp" placeholder="Acme Inc.">
            </div>
            <div class="mf-field">
                <label class="mf-label">Role</label>
                <select name="role" class="mf-sel">
                    <option value="ClientAdmin">Client Admin</option>
                    <option value="ClientUser">Client User</option>
                </select>
            </div>
            <div class="mf-foot">
                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('add-client-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Add Client</button>
            </div>
        </form>
    </div>
</div>

{{-- Delete confirmation modal --}}
<div class="modal-overlay" id="delete-client-modal">
    <div class="modal-box" style="max-width:400px">
        <button class="modal-close" onclick="document.getElementById('delete-client-modal').classList.remove('open')">&times;</button>
        <div class="modal-title">Delete Client</div>
        <div class="modal-sub" id="delete-client-msg">Are you sure you want to delete this client?</div>
        <form method="POST" id="delete-client-form">
            @csrf
            @method('DELETE')
            <div class="mf-foot">
                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('delete-client-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-sm" style="background:var(--red);color:#fff">Delete</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function confirmDeleteClient(id, name) {
    document.getElementById('delete-client-msg').textContent = 'Delete "' + name + '"? This action cannot be undone.';
    document.getElementById('delete-client-form').action = '/clients/' + id;
    document.getElementById('delete-client-modal').classList.add('open');
}

// Client search + filter
document.getElementById('client-search')?.addEventListener('input', filterClients);
document.getElementById('client-status-filter')?.addEventListener('change', filterClients);

function filterClients() {
    const q = document.getElementById('client-search').value.toLowerCase();
    const s = document.getElementById('client-status-filter').value.toLowerCase();
    document.querySelectorAll('#clients-tbody tr[data-name]').forEach(row => {
        const name = row.dataset.name || '';
        const email = row.dataset.email || '';
        const status = row.dataset.status || '';
        const matchQ = !q || name.includes(q) || email.includes(q);
        const matchS = !s || status === s;
        row.style.display = matchQ && matchS ? '' : 'none';
    });
}

// Company search
document.getElementById('company-search')?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#companies-tbody tr[data-name]').forEach(row => {
        const name = row.dataset.name || '';
        const sub = row.dataset.subdomain || '';
        row.style.display = !q || name.includes(q) || sub.includes(q) ? '' : 'none';
    });
});
</script>
@endpush
