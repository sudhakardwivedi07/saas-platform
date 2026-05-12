@extends('layouts.superadmin.app')
@section('page-title', 'Menu Visibility')

@section('content')

<div class="ph">
    <div class="ph-left">
        <h1>Menu Visibility</h1>
        <p>Control which sidebar menu items are visible to each role across all tenant firms.</p>
    </div>
    <div class="ph-right">
        <button id="save-btn" onclick="saveSettings()" class="btn btn-primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v14a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            Save Changes
        </button>
    </div>
</div>

<div class="info-banner" style="margin-bottom:1.5rem">
    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;color:var(--blue)"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <span class="info-banner-txt">
        Toggling a menu item off hides it from the sidebar for that role. It does <strong>not</strong> revoke route-level access — use <strong>Roles &amp; Permissions</strong> for that.
        Grey cells indicate that role cannot access this item regardless of this setting.
    </span>
</div>

<div class="card" style="padding:0;overflow:hidden">
    <table style="width:100%;border-collapse:collapse">
        <thead>
            <tr style="background:#fafbfc;border-bottom:2px solid var(--border)">
                <th style="padding:14px 20px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-2);width:40%">
                    Menu Item
                </th>
                @foreach($roles as $role)
                <th style="padding:14px 20px;text-align:center;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-2);width:20%">
                    <div style="display:flex;align-items:center;justify-content:center;gap:6px">
                        <span class="badge {{ $role === 'admin' ? 'b-blue' : ($role === 'manager' ? 'b-orange' : 'b-gray') }}" style="font-size:10.5px">
                            {{ ucfirst($role) }}
                        </span>
                    </div>
                </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($navItems as $key => $item)
            @php $isEven = $loop->even; @endphp
            <tr style="{{ $isEven ? 'background:#fafbfc' : 'background:#fff' }};border-bottom:1px solid var(--border);transition:background .12s" class="nav-row">
                <td style="padding:15px 20px">
                    <div style="font-size:13.5px;font-weight:600;color:var(--text-1)">{{ $item['label'] }}</div>
                    <div style="font-size:11.5px;color:var(--text-3);margin-top:2px;font-family:monospace">{{ $key }}</div>
                </td>
                @foreach($roles as $role)
                @php $canAccess = in_array($role, $item['roles']); @endphp
                <td style="padding:15px 20px;text-align:center">
                    @if($canAccess)
                        <label class="nm-toggle {{ $current[$key][$role] ? 'on' : '' }}" title="Toggle {{ $item['label'] }} for {{ ucfirst($role) }}">
                            <input type="checkbox"
                                   name="nav[{{ $key }}][{{ $role }}]"
                                   value="1"
                                   {{ $current[$key][$role] ? 'checked' : '' }}
                                   onchange="this.closest('.nm-toggle').classList.toggle('on', this.checked)">
                            <span class="nm-slider"></span>
                        </label>
                    @else
                        <span title="{{ ucfirst($role) }}s cannot access this item" style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:20px;border-radius:10px;background:#f0f0f0;cursor:not-allowed" >
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#c0c0c0" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </span>
                    @endif
                </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div style="margin-top:20px;display:flex;justify-content:flex-end">
    <button id="save-btn-bottom" onclick="saveSettings()" class="btn btn-primary">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v14a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
        Save Changes
    </button>
</div>

@endsection

@push('styles')
<style>
/* Toggle switch */
.nm-toggle {
    position: relative;
    display: inline-block;
    width: 42px;
    height: 24px;
    cursor: pointer;
}
.nm-toggle input {
    opacity: 0;
    width: 0;
    height: 0;
    position: absolute;
}
.nm-slider {
    position: absolute;
    inset: 0;
    background: #d1d5db;
    border-radius: 12px;
    transition: background .2s;
}
.nm-slider::after {
    content: '';
    position: absolute;
    width: 18px;
    height: 18px;
    left: 3px;
    top: 3px;
    background: #fff;
    border-radius: 50%;
    box-shadow: 0 1px 3px rgba(0,0,0,.2);
    transition: transform .2s;
}
.nm-toggle.on .nm-slider {
    background: var(--blue);
}
.nm-toggle.on .nm-slider::after {
    transform: translateX(18px);
}
.nm-toggle:hover .nm-slider {
    opacity: .9;
}

.nav-row:hover {
    background: #f0f7ff !important;
}
</style>
@endpush

@push('scripts')
<script>
function saveSettings() {
    var btns = [document.getElementById('save-btn'), document.getElementById('save-btn-bottom')];
    btns.forEach(function(b) { if(b) { b.disabled = true; b.innerHTML = '<span style="opacity:.7">Saving…</span>'; } });

    // Collect all checkbox states
    var data = new FormData();
    data.append('_token', '{{ csrf_token() }}');

    // For every possible nav × role combination send the value
    var checkboxes = document.querySelectorAll('input[type="checkbox"][name^="nav["]');
    checkboxes.forEach(function(cb) {
        data.append(cb.name, cb.checked ? '1' : '0');
    });

    fetch('{{ route("superadmin.nav-menu.update") }}', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: data,
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        btns.forEach(function(b) {
            if(b) {
                b.disabled = false;
                b.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v14a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Save Changes';
            }
        });
        if (res.status === 'success') {
            Swal.fire({ icon: 'success', title: 'Saved!', text: res.message, timer: 2500, timerProgressBar: true, showConfirmButton: false });
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Something went wrong.' });
        }
    })
    .catch(function() {
        btns.forEach(function(b) {
            if(b) { b.disabled = false; b.innerHTML = 'Save Changes'; }
        });
        Swal.fire({ icon: 'error', title: 'Request failed', text: 'Could not reach the server.' });
    });
}
</script>
@endpush
