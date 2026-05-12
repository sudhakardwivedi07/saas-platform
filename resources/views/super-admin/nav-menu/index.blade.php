@extends('layouts.superadmin.app')
@section('page-title', 'Menu Management')

@section('content')

<div class="ph">
    <div class="ph-left">
        <h1>Menu Management</h1>
        <p>Add, reorder, and control which sidebar items are visible per role across all tenant firms.</p>
    </div>
    <div class="ph-right" style="display:flex;gap:9px">
        <button onclick="openAddModal()" class="btn btn-ghost">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Menu Item
        </button>
        <button id="save-btn" onclick="saveVisibility()" class="btn btn-primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v14a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            Save Visibility
        </button>
    </div>
</div>

<div class="info-banner" style="margin-bottom:1.5rem">
    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;color:var(--blue)"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <span class="info-banner-txt">
        Drag rows to reorder. Toggles control sidebar visibility per role — they do <strong>not</strong> revoke route-level access. Use <strong>Roles &amp; Permissions</strong> for that.
        Grey cells mean that role has no assignment for this item.
    </span>
</div>

<div class="card" style="padding:0;overflow:hidden" id="nav-table-card">
    <table style="width:100%;border-collapse:collapse" id="nav-table">
        <thead>
            <tr style="background:#fafbfc;border-bottom:2px solid var(--border)">
                <th style="padding:12px 10px;width:32px"></th>{{-- drag handle --}}
                <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-2)">Menu Item</th>
                @foreach($roles as $role)
                <th style="padding:12px 16px;text-align:center;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-2);width:120px">
                    <span class="badge {{ $role === 'admin' ? 'b-blue' : ($role === 'manager' ? 'b-orange' : 'b-gray') }}" style="font-size:10.5px">
                        {{ ucfirst($role) }}
                    </span>
                </th>
                @endforeach
                <th style="padding:12px 16px;text-align:center;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-2);width:90px">Actions</th>
            </tr>
        </thead>
        <tbody id="nav-tbody">
            @foreach($navItems as $item)
            @php $isEven = $loop->even; @endphp
            <tr data-key="{{ $item->nav_key }}"
                style="{{ $item->is_active ? ($isEven ? 'background:#fafbfc' : 'background:#fff') : 'background:#fff8f0;opacity:.65' }};border-bottom:1px solid var(--border);transition:background .12s"
                class="nav-row">
                {{-- Drag handle --}}
                <td style="padding:12px 10px;text-align:center;cursor:grab;color:var(--text-3)" class="drag-handle" title="Drag to reorder">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                </td>
                {{-- Label + key --}}
                <td style="padding:12px 16px">
                    <div style="display:flex;align-items:center;gap:9px">
                        <span style="display:inline-flex;color:var(--text-3);flex-shrink:0">
                            {!! $item->icon_svg !!}
                        </span>
                        <div>
                            <div style="font-size:13.5px;font-weight:600;color:var(--text-1)">{{ $item->label }}</div>
                            <div style="font-size:11px;color:var(--text-3);font-family:monospace">{{ $item->nav_key }}</div>
                        </div>
                        @if(!$item->is_active)
                        <span class="badge b-amber" style="font-size:10px">inactive</span>
                        @endif
                    </div>
                </td>
                {{-- Role toggles --}}
                @foreach($roles as $role)
                @php $nir = $item->roles->firstWhere('role_name', $role); @endphp
                <td style="padding:12px 16px;text-align:center">
                    @if($nir)
                        <label class="nm-toggle {{ $nir->is_visible ? 'on' : '' }}" title="Toggle {{ $item->label }} for {{ ucfirst($role) }}">
                            <input type="checkbox"
                                   name="nav[{{ $item->nav_key }}][{{ $role }}]"
                                   value="1"
                                   {{ $nir->is_visible ? 'checked' : '' }}
                                   onchange="this.closest('.nm-toggle').classList.toggle('on', this.checked)">
                            <span class="nm-slider"></span>
                        </label>
                    @else
                        <span title="{{ ucfirst($role) }}s have no assignment for this item"
                              style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:20px;border-radius:10px;background:#f0f0f0;cursor:not-allowed">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#c0c0c0" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </span>
                    @endif
                </td>
                @endforeach
                {{-- Actions --}}
                <td style="padding:12px 16px;text-align:center">
                    <div style="display:flex;align-items:center;justify-content:center;gap:6px">
                        <button onclick="openEditModal('{{ $item->nav_key }}')"
                                class="btn btn-ghost btn-xs" title="Edit item">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </button>
                        <button onclick="toggleActive('{{ $item->nav_key }}', this)"
                                class="btn btn-xs {{ $item->is_active ? 'btn-ghost' : 'btn-primary' }}"
                                title="{{ $item->is_active ? 'Disable item' : 'Enable item' }}">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                @if($item->is_active)
                                <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>
                                @else
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                @endif
                            </svg>
                        </button>
                        <button onclick="deleteItem('{{ $item->nav_key }}', {{ json_encode($item->label) }})"
                                class="btn btn-danger btn-xs" title="Delete item">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div style="margin-top:16px;display:flex;justify-content:flex-end">
    <button id="save-btn-bottom" onclick="saveVisibility()" class="btn btn-primary">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v14a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
        Save Visibility
    </button>
</div>

{{-- ── Add Item Modal ─────────────────────────────────────────────────────── --}}
<div class="modal-overlay" id="add-modal">
    <div class="modal-box" style="width:660px;max-height:92vh;overflow-y:auto">
        <button class="modal-close" onclick="closeModal('add-modal')">×</button>
        <div class="modal-title">Add Menu Item</div>
        <div class="modal-sub">New items appear at the bottom of the sidebar and can be reordered.</div>

        {{-- Basic info --}}
        <div class="form-2col">
            <div class="mf-field">
                <label class="mf-label">Menu Key <span style="color:var(--red)">*</span></label>
                <input id="add-key" type="text" class="mf-inp" placeholder="e.g. reports" maxlength="50">
                <div style="font-size:11px;color:var(--text-3);margin-top:3px">Lowercase, numbers &amp; underscores only</div>
            </div>
            <div class="mf-field">
                <label class="mf-label">Display Label <span style="color:var(--red)">*</span></label>
                <input id="add-label" type="text" class="mf-inp" placeholder="e.g. Reports" maxlength="100">
            </div>
        </div>

        <div class="mf-field" style="margin-bottom:18px">
            <label class="mf-label">Icon SVG <span style="color:var(--text-3);font-weight:400">(optional — paste full &lt;svg&gt;…&lt;/svg&gt;)</span></label>
            <textarea id="add-icon" class="mf-textarea" style="min-height:52px;font-family:monospace;font-size:12px" placeholder='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor">…</svg>'></textarea>
        </div>

        {{-- Role assignments table --}}
        <div style="font-size:13px;font-weight:600;margin-bottom:8px;display:flex;align-items:center;gap:6px">
            Role Assignments
            <span style="color:var(--red)">*</span>
            <span style="font-size:11.5px;color:var(--text-3);font-weight:400">— check a role to include it, then fill its route</span>
        </div>

        <div style="border:1px solid var(--border);border-radius:10px;overflow:hidden;margin-bottom:6px">
            {{-- Table header --}}
            <div style="display:grid;grid-template-columns:36px 90px 1fr 1fr 1fr;gap:0;background:#fafbfc;border-bottom:1px solid var(--border);padding:8px 12px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-3)">
                <div></div>
                <div>Role</div>
                <div>Route Name <span style="color:var(--red)">*</span></div>
                <div>Active Pattern(s)</div>
                <div>Required Permission</div>
            </div>
            {{-- One row per role --}}
            @foreach(['admin','manager','client'] as $r)
            @php $badgeClass = $r === 'admin' ? 'b-blue' : ($r === 'manager' ? 'b-orange' : 'b-gray'); @endphp
            <div id="add-role-row-{{ $r }}"
                 style="display:grid;grid-template-columns:36px 90px 1fr 1fr 1fr;gap:0;align-items:center;padding:10px 12px;border-bottom:{{ $r !== 'client' ? '1px solid var(--border)' : 'none' }};transition:background .12s"
                 class="add-role-row-wrap">
                {{-- Enable checkbox --}}
                <div>
                    <input type="checkbox" id="add-role-{{ $r }}" class="role-chk"
                           style="width:16px;height:16px;cursor:pointer;accent-color:var(--blue)"
                           onchange="toggleRoleRow('{{ $r }}', this.checked)">
                </div>
                {{-- Role badge --}}
                <div>
                    <label for="add-role-{{ $r }}" style="cursor:pointer">
                        <span class="badge {{ $badgeClass }}" style="font-size:11.5px">{{ ucfirst($r) }}</span>
                    </label>
                </div>
                {{-- Route name --}}
                <div style="padding-right:8px">
                    <input type="text" id="add-route-{{ $r }}"
                           class="mf-inp role-field-{{ $r }}"
                           style="font-family:monospace;font-size:12px;background:#f9fafb;color:var(--text-3)"
                           placeholder="e.g. reports.index"
                           disabled>
                </div>
                {{-- Active pattern --}}
                <div style="padding-right:8px">
                    <input type="text" id="add-active-{{ $r }}"
                           class="mf-inp role-field-{{ $r }}"
                           style="font-family:monospace;font-size:12px;background:#f9fafb;color:var(--text-3)"
                           placeholder="reports.*"
                           disabled>
                </div>
                {{-- Permission --}}
                <div>
                    <input type="text" id="add-perm-{{ $r }}"
                           class="mf-inp role-field-{{ $r }}"
                           style="font-family:monospace;font-size:12px;background:#f9fafb;color:var(--text-3)"
                           placeholder="view_reports (opt.)"
                           disabled>
                </div>
            </div>
            @endforeach
        </div>
        <div style="font-size:11.5px;color:var(--text-3);margin-bottom:16px">
            <strong>Active Pattern(s)</strong> — comma-separated route wildcards that highlight this item as active, e.g. <code style="background:#f3f4f6;padding:1px 5px;border-radius:4px">reports.*,report-detail.*</code>
        </div>

        <div class="mf-foot" style="border-top:1px solid var(--border);padding-top:14px;margin-top:4px">
            <button class="btn btn-ghost" onclick="closeModal('add-modal')">Cancel</button>
            <button class="btn btn-primary" onclick="submitAddItem()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Item
            </button>
        </div>
    </div>
</div>

{{-- ── Edit Item Modal ────────────────────────────────────────────────────── --}}
<div class="modal-overlay" id="edit-modal">
    <div class="modal-box" style="width:660px;max-height:92vh;overflow-y:auto">
        <button class="modal-close" onclick="closeModal('edit-modal')">×</button>
        <div class="modal-title">Edit Menu Item</div>
        <div class="modal-sub">Update the label, icon, and per-role route configuration.</div>

        <input type="hidden" id="edit-key">

        {{-- Basic info --}}
        <div class="form-2col">
            <div class="mf-field">
                <label class="mf-label">Menu Key</label>
                <input id="edit-key-display" type="text" class="mf-inp"
                       style="background:#f9fafb;color:var(--text-3);font-family:monospace;font-size:12px" disabled>
                <div style="font-size:11px;color:var(--text-3);margin-top:3px">Key cannot be changed after creation</div>
            </div>
            <div class="mf-field">
                <label class="mf-label">Display Label <span style="color:var(--red)">*</span></label>
                <input id="edit-label" type="text" class="mf-inp" maxlength="100">
            </div>
        </div>

        <div class="mf-field" style="margin-bottom:18px">
            <label class="mf-label">Icon SVG <span style="color:var(--text-3);font-weight:400">(optional)</span></label>
            <textarea id="edit-icon" class="mf-textarea" style="min-height:52px;font-family:monospace;font-size:12px"></textarea>
        </div>

        {{-- Role assignments table --}}
        <div style="font-size:13px;font-weight:600;margin-bottom:8px;display:flex;align-items:center;gap:6px">
            Role Assignments
            <span style="font-size:11.5px;color:var(--text-3);font-weight:400">— uncheck a role to remove it from that sidebar</span>
        </div>

        <div style="border:1px solid var(--border);border-radius:10px;overflow:hidden;margin-bottom:6px">
            {{-- Table header --}}
            <div style="display:grid;grid-template-columns:36px 90px 1fr 1fr 1fr;gap:0;background:#fafbfc;border-bottom:1px solid var(--border);padding:8px 12px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-3)">
                <div></div>
                <div>Role</div>
                <div>Route Name <span style="color:var(--red)">*</span></div>
                <div>Active Pattern(s)</div>
                <div>Required Permission</div>
            </div>
            {{-- One row per role --}}
            @foreach(['admin','manager','client'] as $r)
            @php $badgeClass = $r === 'admin' ? 'b-blue' : ($r === 'manager' ? 'b-orange' : 'b-gray'); @endphp
            <div id="edit-role-row-{{ $r }}"
                 style="display:grid;grid-template-columns:36px 90px 1fr 1fr 1fr;gap:0;align-items:center;padding:10px 12px;border-bottom:{{ $r !== 'client' ? '1px solid var(--border)' : 'none' }};transition:background .12s">
                {{-- Enable checkbox --}}
                <div>
                    <input type="checkbox" id="edit-role-{{ $r }}"
                           style="width:16px;height:16px;cursor:pointer;accent-color:var(--blue)"
                           onchange="toggleEditRoleRow('{{ $r }}', this.checked)">
                </div>
                {{-- Role badge --}}
                <div>
                    <label for="edit-role-{{ $r }}" style="cursor:pointer">
                        <span class="badge {{ $badgeClass }}" style="font-size:11.5px">{{ ucfirst($r) }}</span>
                    </label>
                </div>
                {{-- Route name --}}
                <div style="padding-right:8px">
                    <input type="text" id="edit-route-{{ $r }}"
                           class="mf-inp edit-role-field-{{ $r }}"
                           style="font-family:monospace;font-size:12px;background:#f9fafb;color:var(--text-3)"
                           placeholder="e.g. reports.index" disabled>
                </div>
                {{-- Active pattern --}}
                <div style="padding-right:8px">
                    <input type="text" id="edit-active-{{ $r }}"
                           class="mf-inp edit-role-field-{{ $r }}"
                           style="font-family:monospace;font-size:12px;background:#f9fafb;color:var(--text-3)"
                           placeholder="reports.*" disabled>
                </div>
                {{-- Permission --}}
                <div>
                    <input type="text" id="edit-perm-{{ $r }}"
                           class="mf-inp edit-role-field-{{ $r }}"
                           style="font-family:monospace;font-size:12px;background:#f9fafb;color:var(--text-3)"
                           placeholder="view_reports (opt.)" disabled>
                </div>
            </div>
            @endforeach
        </div>
        <div style="font-size:11.5px;color:var(--text-3);margin-bottom:16px">
            <strong>Active Pattern(s)</strong> — comma-separated route wildcards, e.g. <code style="background:#f3f4f6;padding:1px 5px;border-radius:4px">reports.*,report-detail.*</code>
        </div>

        <div class="mf-foot" style="border-top:1px solid var(--border);padding-top:14px;margin-top:4px">
            <button class="btn btn-ghost" onclick="closeModal('edit-modal')">Cancel</button>
            <button class="btn btn-primary" onclick="submitEditItem()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v14a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save Changes
            </button>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
/* Toggle switch */
.nm-toggle{position:relative;display:inline-block;width:42px;height:24px;cursor:pointer}
.nm-toggle input{opacity:0;width:0;height:0;position:absolute}
.nm-slider{position:absolute;inset:0;background:#d1d5db;border-radius:12px;transition:background .2s}
.nm-slider::after{content:'';position:absolute;width:18px;height:18px;left:3px;top:3px;background:#fff;border-radius:50%;box-shadow:0 1px 3px rgba(0,0,0,.2);transition:transform .2s}
.nm-toggle.on .nm-slider{background:var(--blue)}
.nm-toggle.on .nm-slider::after{transform:translateX(18px)}
.nm-toggle:hover .nm-slider{opacity:.9}
.nav-row:hover{background:#f0f7ff !important}
.nav-row.drag-over{border-top:2px solid var(--blue) !important}
.drag-handle:active{cursor:grabbing}
/* Inline SVG in table cells */
#nav-tbody svg{width:15px;height:15px;stroke-width:1.8}
</style>
@endpush

@php
    $__navItemsData = [];
    foreach ($navItems as $__item) {
        $__roles = [];
        foreach ($__item->roles as $__r) {
            $__roles[$__r->role_name] = [
                'route'      => $__r->route_name,
                'active'     => $__r->active_route_pattern,
                'permission' => $__r->required_permission ?? '',
            ];
        }
        $__navItemsData[$__item->nav_key] = [
            'label'    => $__item->label,
            'icon_svg' => $__item->icon_svg,
            'roles'    => $__roles,
        ];
    }
@endphp
@push('scripts')
<script>
// ── Per-item data map (safe JSON, not injected into onclick attrs) ─────────────
const NAV_ITEMS_DATA = @json($__navItemsData);

const NAV_ROUTES = {
    update:     '{{ route("superadmin.nav-menu.update") }}',
    store:      '{{ route("superadmin.nav-menu.store") }}',
    reorder:    '{{ route("superadmin.nav-menu.reorder") }}',
    updateItem: (key) => `/superadmin/nav-menu/items/${key}`,
    toggle:     (key) => `/superadmin/nav-menu/items/${key}/toggle`,
    destroy:    (key) => `/superadmin/nav-menu/items/${key}`,
};
const _token = '{{ csrf_token() }}';

// ── Modals ────────────────────────────────────────────────────────────────────
function openAddModal() {
    document.getElementById('add-key').value   = '';
    document.getElementById('add-label').value = '';
    document.getElementById('add-icon').value  = '';
    ['admin','manager','client'].forEach(r => {
        document.getElementById('add-role-' + r).checked = false;
        toggleRoleRow(r, false);
        ['route','active','perm'].forEach(f => {
            const el = document.getElementById('add-' + f + '-' + r);
            if (el) el.value = '';
        });
    });
    document.getElementById('add-modal').classList.add('open');
    document.getElementById('add-key').focus();
}
function openEditModal(key) {
    const data  = NAV_ITEMS_DATA[key];
    if (!data) return;

    // Basic fields
    document.getElementById('edit-key').value         = key;
    document.getElementById('edit-key-display').value = key;
    document.getElementById('edit-label').value       = data.label;
    document.getElementById('edit-icon').value        = data.icon_svg;

    // Populate role rows
    ['admin','manager','client'].forEach(r => {
        const cfg     = data.roles[r] || null;
        const checked = !!cfg;

        document.getElementById('edit-role-' + r).checked = checked;
        document.getElementById('edit-route-' + r).value  = cfg ? cfg.route      : '';
        document.getElementById('edit-active-' + r).value = cfg ? cfg.active     : '';
        document.getElementById('edit-perm-' + r).value   = cfg ? cfg.permission : '';
        toggleEditRoleRow(r, checked);
    });

    document.getElementById('edit-modal').classList.add('open');
    document.getElementById('edit-label').focus();
}
function closeModal(id) {
    document.getElementById(id).classList.remove('open');
}
document.querySelectorAll('.modal-overlay').forEach(o => o.addEventListener('click', function(e) {
    if (e.target === this) closeModal(this.id);
}));
function toggleRoleRow(role, enabled) {
    const row    = document.getElementById('add-role-row-' + role);
    const fields = document.querySelectorAll('.role-field-' + role);
    if (row) row.style.background = enabled ? '#f0f7ff' : '';
    fields.forEach(f => {
        f.disabled = !enabled;
        f.style.background = enabled ? '#fff' : '#f9fafb';
        f.style.color      = enabled ? 'var(--text-1)' : 'var(--text-3)';
    });
}
function toggleEditRoleRow(role, enabled) {
    const row    = document.getElementById('edit-role-row-' + role);
    const fields = document.querySelectorAll('.edit-role-field-' + role);
    if (row) row.style.background = enabled ? '#f0f7ff' : '';
    fields.forEach(f => {
        f.disabled = !enabled;
        f.style.background = enabled ? '#fff' : '#f9fafb';
        f.style.color      = enabled ? 'var(--text-1)' : 'var(--text-3)';
    });
}

// ── Save visibility toggles ───────────────────────────────────────────────────
function saveVisibility() {
    const btns = ['save-btn','save-btn-bottom'].map(id => document.getElementById(id)).filter(Boolean);
    btns.forEach(b => { b.disabled = true; b.innerHTML = '<span style="opacity:.7">Saving…</span>'; });

    const data = new FormData();
    data.append('_token', _token);
    document.querySelectorAll('input[type="checkbox"][name^="nav["]').forEach(cb => {
        data.append(cb.name, cb.checked ? '1' : '0');
    });

    fetch(NAV_ROUTES.update, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: data })
        .then(r => r.json())
        .then(res => {
            restoreSaveBtns(btns);
            if (res.status === 'success') {
                Swal.fire({ icon: 'success', title: 'Saved!', text: res.message, timer: 2500, timerProgressBar: true, showConfirmButton: false });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Something went wrong.' });
            }
        })
        .catch(() => { restoreSaveBtns(btns); Swal.fire({ icon: 'error', title: 'Request failed', text: 'Could not reach the server.' }); });
}
function restoreSaveBtns(btns) {
    const svg = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v14a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>';
    btns.forEach(b => { b.disabled = false; b.innerHTML = svg + ' Save Visibility'; });
}

// ── Add item ──────────────────────────────────────────────────────────────────
function submitAddItem() {
    const key   = document.getElementById('add-key').value.trim();
    const label = document.getElementById('add-label').value.trim();
    const icon  = document.getElementById('add-icon').value.trim();

    if (!key || !label) {
        Swal.fire({ icon: 'warning', title: 'Missing fields', text: 'Menu Key and Display Label are required.' });
        return;
    }
    if (!/^[a-z0-9_]+$/.test(key)) {
        Swal.fire({ icon: 'warning', title: 'Invalid key', text: 'Key must be lowercase letters, numbers, and underscores only.' });
        return;
    }

    const roles = [];
    let missingRoute = null;
    ['admin','manager','client'].forEach(r => {
        if (!document.getElementById('add-role-' + r).checked) return;
        const route = document.getElementById('add-route-' + r).value.trim();
        if (!route) { missingRoute = r; return; }
        roles.push({
            role_name:            r,
            route_name:           route,
            active_route_pattern: document.getElementById('add-active-' + r).value.trim(),
            required_permission:  document.getElementById('add-perm-' + r).value.trim() || null,
        });
    });

    if (missingRoute) {
        Swal.fire({ icon: 'warning', title: 'Route name required', text: `Please enter a Route Name for the ${missingRoute} role.` });
        document.getElementById('add-route-' + missingRoute).focus();
        return;
    }
    if (roles.length === 0) {
        Swal.fire({ icon: 'warning', title: 'No roles selected', text: 'Check at least one role and fill its Route Name.' });
        return;
    }

    fetch(NAV_ROUTES.store, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _token, 'Accept': 'application/json' },
        body: JSON.stringify({ nav_key: key, label, icon_svg: icon || null, roles }),
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            closeModal('add-modal');
            Swal.fire({ icon: 'success', title: 'Created!', text: res.message, timer: 2000, showConfirmButton: false })
                .then(() => window.location.reload());
        } else {
            const msg = res.errors
                ? Object.values(res.errors).flat().join('\n')
                : (res.message || 'Something went wrong.');
            Swal.fire({ icon: 'error', title: 'Error', text: msg });
        }
    })
    .catch(() => Swal.fire({ icon: 'error', title: 'Request failed', text: 'Could not reach the server.' }));
}

// ── Edit item ─────────────────────────────────────────────────────────────────
function submitEditItem() {
    const key   = document.getElementById('edit-key').value;
    const label = document.getElementById('edit-label').value.trim();
    const icon  = document.getElementById('edit-icon').value.trim();
    if (!label) { Swal.fire({ icon: 'warning', title: 'Label required' }); return; }

    // Collect role assignments (same logic as Add)
    const roles = [];
    let missingRoute = null;
    ['admin','manager','client'].forEach(r => {
        const checked = document.getElementById('edit-role-' + r).checked;
        if (!checked) {
            roles.push({ role_name: r, enabled: false }); // signal removal
            return;
        }
        const route = document.getElementById('edit-route-' + r).value.trim();
        if (!route) { missingRoute = r; return; }
        roles.push({
            role_name:            r,
            enabled:              true,
            route_name:           route,
            active_route_pattern: document.getElementById('edit-active-' + r).value.trim(),
            required_permission:  document.getElementById('edit-perm-' + r).value.trim() || null,
        });
    });

    if (missingRoute) {
        Swal.fire({ icon: 'warning', title: 'Route name required', text: `Please enter a Route Name for the ${missingRoute} role.` });
        document.getElementById('edit-route-' + missingRoute).focus();
        return;
    }
    const enabledRoles = roles.filter(r => r.enabled);
    if (enabledRoles.length === 0) {
        Swal.fire({ icon: 'warning', title: 'No roles selected', text: 'At least one role must be assigned.' });
        return;
    }

    fetch(NAV_ROUTES.updateItem(key), {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _token, 'Accept': 'application/json' },
        body: JSON.stringify({ label, icon_svg: icon || null, roles }),
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            closeModal('edit-modal');
            Swal.fire({ icon: 'success', title: 'Updated!', timer: 1800, showConfirmButton: false })
                .then(() => window.location.reload());
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: res.message });
        }
    });
}

// ── Toggle active / inactive ──────────────────────────────────────────────────
function toggleActive(navKey, btn) {
    fetch(NAV_ROUTES.toggle(navKey), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _token, 'Accept': 'application/json' },
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            Swal.fire({ icon: 'success', title: res.message, timer: 1800, showConfirmButton: false })
                .then(() => window.location.reload());
        }
    });
}

// ── Delete item ───────────────────────────────────────────────────────────────
function deleteItem(navKey, label) {
    Swal.fire({
        icon: 'warning',
        title: 'Delete "' + label + '"?',
        text: 'This will remove the item from all role sidebars permanently.',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete',
        confirmButtonColor: '#ef4444',
        cancelButtonText: 'Cancel',
    }).then(result => {
        if (!result.isConfirmed) return;
        fetch(NAV_ROUTES.destroy(navKey), {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _token, 'Accept': 'application/json' },
        })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                Swal.fire({ icon: 'success', title: 'Deleted!', timer: 1800, showConfirmButton: false })
                    .then(() => window.location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: res.message });
            }
        });
    });
}

// ── Drag-to-reorder ───────────────────────────────────────────────────────────
(function () {
    const tbody = document.getElementById('nav-tbody');
    let dragging = null;

    tbody.querySelectorAll('tr[data-key]').forEach(row => {
        const handle = row.querySelector('.drag-handle');
        handle.setAttribute('draggable', true);

        handle.addEventListener('dragstart', () => { dragging = row; row.style.opacity = '.4'; });
        handle.addEventListener('dragend',   () => { dragging = null; row.style.opacity = ''; });

        row.addEventListener('dragover', e => { e.preventDefault(); if (dragging && dragging !== row) row.classList.add('drag-over'); });
        row.addEventListener('dragleave', () => row.classList.remove('drag-over'));
        row.addEventListener('drop', e => {
            e.preventDefault();
            row.classList.remove('drag-over');
            if (!dragging || dragging === row) return;
            tbody.insertBefore(dragging, row);
            persistOrder();
        });
    });

    function persistOrder() {
        const order = Array.from(tbody.querySelectorAll('tr[data-key]')).map(r => r.dataset.key);
        fetch(NAV_ROUTES.reorder, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _token, 'Accept': 'application/json' },
            body: JSON.stringify({ order }),
        })
        .then(r => r.json())
        .then(res => {
            if (res.status !== 'success') Swal.fire({ icon: 'error', title: 'Reorder failed' });
        });
    }
}());
</script>
@endpush
