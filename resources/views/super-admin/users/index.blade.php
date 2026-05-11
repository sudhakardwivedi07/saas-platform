@extends('layouts.superadmin.app')
@section('title', 'Users')
@section('page-title', 'Users')

@section('content')
<div class="ph">
    <div>
        <h1>Users</h1>
        <p>Manage CFO Insights internal team members</p>
    </div>
    <div class="ph-actions">
        <a href="{{ route('superadmin.users.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
            Add User
        </a>
    </div>
</div>

<div class="tc">
    <div class="tc-head">
        <form method="GET" action="{{ route('superadmin.users.index') }}" class="toolbar">
            <div class="srch-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input name="search" value="{{ request('search') }}" placeholder="Search users..." class="srch-inp">
            </div>
            <select name="role" class="sel" onchange="this.form.submit()">
                <option value="">All Roles</option>
                @foreach($roles as $r)
                    <option value="{{ $r->name }}" {{ request('role') === $r->name ? 'selected' : '' }}>{{ $r->name }}</option>
                @endforeach
            </select>
            <select name="status" class="sel" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="active"   {{ request('status')==='active'   ? 'selected':'' }}>Active</option>
                <option value="inactive" {{ request('status')==='inactive' ? 'selected':'' }}>Inactive</option>
            </select>
        </form>
        <span class="badge b-gray">{{ $users->total() }} users</span>
    </div>
    <table>
        <thead>
            <tr>
                <th>USER</th>
                <th>EMAIL</th>
                <th>ROLE</th>
                <th>PERMISSIONS</th>
                <th>LAST LOGIN</th>
                <th>STATUS</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @php
            $avatarColors = ['#4f46e5','#f97316','#a855f7','#22c55e','#ef4444','#3b82f6','#ec4899'];
            $roleColorMap = [
                'super_admin'  => ['bg'=>'#eff6ff','text'=>'#2563eb'],
                'super admin'  => ['bg'=>'#eff6ff','text'=>'#2563eb'],
                'operations'   => ['bg'=>'#fff7ed','text'=>'#ea580c'],
                'support'      => ['bg'=>'#f5f3ff','text'=>'#7c3aed'],
                'finance'      => ['bg'=>'#f0fdf4','text'=>'#16a34a'],
                'admin'        => ['bg'=>'#fef3c7','text'=>'#d97706'],
            ];
            @endphp
            @forelse($users as $user)
            @php
            $avatarBg = $avatarColors[$user->id % count($avatarColors)];
            $initials = collect(explode(' ', $user->name))->map(fn($w) => strtoupper(substr($w,0,1)))->take(2)->join('');
            $firstRole = $user->roles->first();

            if ($user->is_superadmin) {
                $permsDisplay = 'Full access';
            } else {
                $modules = $user->roles->flatMap(fn($r) => $r->permissions)->pluck('module')->filter()->unique()->values();
                $permsDisplay = $modules->isNotEmpty() ? $modules->take(4)->join(', ') : '—';
            }
            @endphp
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:10px">
                        <div style="width:36px;height:36px;border-radius:8px;background:{{ $avatarBg }};display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:13px;flex-shrink:0">
                            {{ $initials }}
                        </div>
                        <div>
                            <div style="font-weight:600;font-size:13.5px">{{ $user->name }}</div>
                            @if($firstRole)
                            <div style="font-size:11.5px;color:var(--text-3)">{{ $firstRole->name }}</div>
                            @endif
                        </div>
                    </div>
                </td>
                <td style="color:var(--text-2);font-size:13px">{{ $user->email }}</td>
                <td>
                    @foreach($user->roles as $role)
                    @php
                        $rk = strtolower($role->name);
                        $rc = $roleColorMap[$rk] ?? ['bg'=>'#f3f4f6','text'=>'#374151'];
                    @endphp
                    <span class="badge" style="background:{{ $rc['bg'] }};color:{{ $rc['text'] }};font-weight:600">
                        {{ $role->name }}
                    </span>
                    @endforeach
                </td>
                <td style="color:var(--text-2);font-size:12.5px">{{ $permsDisplay }}</td>
                <td style="color:var(--text-3);font-size:12px">{{ $user->last_login?->diffForHumans() ?? 'Never' }}</td>
                <td>
                    <span class="badge {{ $user->status === 'active' ? 'b-green' : 'b-red' }}">
                        <span class="bdot" style="background:{{ $user->status === 'active' ? 'var(--green)' : 'var(--red)' }}"></span>
                        {{ ucfirst($user->status) }}
                    </span>
                </td>
                <td style="padding:11px 14px;border-bottom:1px solid var(--border);vertical-align:middle">
                    <div class="dm-wrap">
                        <button class="dot-menu" type="button" data-menu="user-{{ $user->id }}"
                                style="font-size:20px;letter-spacing:1px;font-weight:700;color:var(--text-3)">···</button>
                        <div class="dm-popup" id="user-{{ $user->id }}">
                            <a href="{{ route('superadmin.users.edit', $user) }}" class="dm-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit User
                            </a>
                            @unless($user->is_superadmin)
                            <div class="dm-div"></div>
                            <button type="button" class="dm-item dm-danger"
                                onclick="toggleUserStatus({{ $user->id }}, '{{ route('superadmin.users.toggle-status', $user->id) }}')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                {{ $user->status === 'active' ? 'Disable User' : 'Enable User' }}
                            </button>
                            @endunless
                        </div>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center;color:var(--text-3);padding:40px">No internal users found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($users->hasPages())
    <div style="padding:12px 16px;border-top:1px solid var(--border)">
        {{ $users->withQueryString()->links('components.pagination') }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
async function toggleUserStatus(id, url) {
    const res = await apiCall(url, 'POST', { _token: '{{ csrf_token() }}' });
    if (res.status === 'success') {
        showToast(res.message);
        setTimeout(() => location.reload(), 800);
    } else {
        showToast(res.message || 'Error', 'error');
    }
}

// Fixed-position action menu — prevents clipping inside table container
document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-menu]');
    if (!btn) return;

    e.stopImmediatePropagation();

    var id    = btn.dataset.menu;
    var popup = document.getElementById(id);
    if (!popup) return;

    var isOpen = popup.classList.contains('open');

    document.querySelectorAll('.dm-popup.open').forEach(function (p) {
        p.classList.remove('open');
        p.style.cssText = '';
    });

    if (!isOpen) {
        var rect = btn.getBoundingClientRect();
        popup.style.position = 'fixed';
        popup.style.top      = (rect.bottom + 4) + 'px';
        popup.style.right    = (window.innerWidth - rect.right) + 'px';
        popup.style.left     = 'auto';
        popup.classList.add('open');
    }
}, true);

window.addEventListener('scroll', function () {
    document.querySelectorAll('.dm-popup.open').forEach(function (p) {
        p.classList.remove('open');
        p.style.cssText = '';
    });
}, true);
</script>
@endpush
