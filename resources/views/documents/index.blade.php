@extends('layouts.app')
@section('title','Documents')
@section('page-title','Documents')

@section('content')
<div class="ph">
    <div class="ph-left">
        <h1>Documents</h1>
        <p>{{ $documents->total() }} document{{ $documents->total() !== 1 ? 's' : '' }}</p>
    </div>
    <div class="ph-actions">
        @can('upload_documents')
        <button onclick="document.getElementById('upload-modal').classList.add('open')" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Upload Document
        </button>
        @endcan
    </div>
</div>

<div class="tc" style="margin-bottom:14px">
    <div class="tc-head">
        <div class="tc-filters">
            <form method="GET" action="{{ route('documents.index') }}" id="doc-filter-form" style="display:flex;gap:8px;flex-wrap:wrap;flex:1">
                <div class="srch-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input name="search" value="{{ request('search') }}" placeholder="Search documents…" class="srch-inp">
                </div>
                <select name="type" class="sel">
                    <option value="">Select Type</option>
                    <option value="pdf"   {{ request('type')==='pdf'   ? 'selected':'' }}>PDF</option>
                    <option value="image" {{ request('type')==='image' ? 'selected':'' }}>Images</option>
                    <option value="word"  {{ request('type')==='word'  ? 'selected':'' }}>Word Docs</option>
                </select>
                <input type="hidden" name="date_from" id="doc-date-from" value="{{ request('date_from') }}">
                <input type="hidden" name="date_to"   id="doc-date-to"   value="{{ request('date_to') }}">
                <div class="dr-wrap" id="doc-dr">
                    <div class="dr-field" onclick="drOpen('doc-dr')">
                        <svg class="dr-cal-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span class="dr-text" id="doc-dr-text">Start Date – End Date</span>
                        <button class="dr-clear-btn" onclick="event.stopPropagation();drClear('doc-dr')" title="Clear date filter">&#x2715;</button>
                    </div>
                    <div class="dr-menu" id="doc-dr-menu">
                        <ul class="dr-list">
                            <li class="dr-opt" onclick="drPickPreset('doc-dr','today',this)">Today</li>
                            <li class="dr-opt" onclick="drPickPreset('doc-dr','last30',this)">Last 30 Days</li>
                            <li class="dr-opt" onclick="drPickPreset('doc-dr','thisMonth',this)">This Month</li>
                            <li class="dr-opt" onclick="drPickPreset('doc-dr','lastMonth',this)">Last Month</li>
                            <li class="dr-opt" onclick="drPickPreset('doc-dr','last90',this)">Last 90 Days</li>
                            <li class="dr-opt" onclick="drPickPreset('doc-dr','last6m',this)">Last 6 Months</li>
                            <li class="dr-opt" onclick="drPickPreset('doc-dr','last1y',this)">Last 1 Year</li>
                            <li class="dr-opt" onclick="drPickPreset('doc-dr','custom',this)">Custom Range</li>
                        </ul>
                        <div class="dr-custom-row" id="doc-dr-custom">
                            <input type="date" class="dr-date-inp" id="doc-dr-from" value="{{ request('date_from') }}">
                            <span style="color:var(--text-3);font-size:12px;flex-shrink:0">→</span>
                            <input type="date" class="dr-date-inp" id="doc-dr-to" value="{{ request('date_to') }}">
                            <button class="dr-apply-btn" onclick="drApplyCustom('doc-dr')">Apply</button>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-ghost btn-sm">Filter</button>
                <a href="{{ route('documents.index') }}" class="btn btn-danger btn-sm">Reset</a>
            </form>
        </div>
    </div>

    @forelse($documents as $doc)
    @php
        $isPdf   = str_contains($doc->mime_type ?? '', 'pdf');
        $isImg   = str_contains($doc->mime_type ?? '', 'image');
        $isWord  = str_contains($doc->mime_type ?? '', 'word');
        $iconBg  = $isPdf ? '#fee2e2' : ($isImg ? '#f3e8ff' : ($isWord ? '#dbeafe' : '#f1f5f9'));
        $iconClr = $isPdf ? 'var(--red)' : ($isImg ? 'var(--purple)' : ($isWord ? 'var(--blue)' : 'var(--text-3)'));
    @endphp
    <div style="display:flex;align-items:center;gap:14px;padding:12px 16px;border-bottom:1px solid var(--border)">
        <div style="width:38px;height:38px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:{{ $iconBg }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="{{ $iconClr }}" stroke-width="1.8" width="18" height="18">
                @if($isPdf)
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/>
                @elseif($isImg)
                    <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                @else
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                @endif
            </svg>
        </div>

        <div style="flex:1;min-width:0">
            <div style="font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $doc->name }}</div>
            <div style="font-size:11.5px;color:var(--text-3);margin-top:2px">
                {{ $doc->uploader?->name ?? '—' }} ·
                {{ format_bytes($doc->size ?? 0) }} ·
                {{ $doc->created_at->format(active_date_format()) }} ·
                v{{ $doc->versions()->max('version_number') ?? 1 }}
            </div>
        </div>

        <div style="display:flex;align-items:center;gap:6px">
            @can('download_documents')
            <a href="{{ route('documents.download', $doc) }}" class="btn btn-ghost btn-sm" title="Download">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            </a>
            @endcan
            <button onclick="openVersionModal({{ $doc->id }}, '{{ addslashes($doc->name) }}')" class="btn btn-ghost btn-sm" title="Upload new version">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/></svg>
            </button>
            @can('delete_documents')
            <form method="POST" action="{{ route('documents.destroy', $doc) }}" class="js-doc-delete-form">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--red)" title="Delete">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
                </button>
            </form>
            @endcan
        </div>
    </div>
    @empty
    <div style="text-align:center;padding:60px 24px;color:var(--text-3)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="40" height="40" style="display:block;margin:0 auto 12px;stroke:var(--text-3)"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
        <div style="font-size:14px;font-weight:500;margin-bottom:4px">No documents yet</div>
        <div style="font-size:13px">Upload your first document to get started</div>
    </div>
    @endforelse
</div>

<div>{{ $documents->withQueryString()->links() }}</div>


{{-- Upload Modal --}}
<div id="upload-modal" class="modal-overlay">
    <div class="modal-box" style="width:480px">
        <button class="modal-close" onclick="document.getElementById('upload-modal').classList.remove('open')">×</button>
        <div class="modal-title">Upload Document</div>
        <div class="modal-sub">Select a file to upload to your document library</div>
        <form id="upload-form" enctype="multipart/form-data">
            @csrf
            <div class="mf-field">
                <label class="mf-label">Document Name <span style="color:var(--text-3);font-weight:400">(optional)</span></label>
                <input type="text" name="name" placeholder="Leave blank to use filename" class="mf-inp">
            </div>
            <div id="drop-zone" style="border:2px dashed var(--border);border-radius:10px;padding:32px;text-align:center;cursor:pointer;transition:border-color .14s"
                 onclick="document.getElementById('file-input').click()"
                 ondragover="event.preventDefault();this.style.borderColor='var(--blue)'"
                 ondragleave="this.style.borderColor='var(--border)'"
                 ondrop="handleDrop(event)">
                <svg viewBox="0 0 24 24" fill="none" stroke="var(--text-3)" stroke-width="1.5" width="36" height="36" style="display:block;margin:0 auto 10px"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                <div style="font-size:13px;color:var(--text-2)">Click to browse or drag &amp; drop</div>
                <div style="font-size:12px;color:var(--text-3);margin-top:4px">PDF, Word, Excel, Images up to 50MB</div>
                <div id="file-name" style="font-size:13px;color:var(--blue);font-weight:500;margin-top:8px;display:none"></div>
            </div>
            <input type="file" id="file-input" name="file" class="hidden" style="display:none"
                   onchange="document.getElementById('file-name').textContent=this.files[0].name;document.getElementById('file-name').style.display='block'">
            <div id="upload-progress" style="display:none;margin-top:12px">
                <div style="background:var(--bg);border-radius:999px;height:6px;overflow:hidden">
                    <div id="progress-bar" style="width:0%;background:var(--blue);height:6px;transition:width .2s"></div>
                </div>
                <div id="progress-text" style="font-size:12px;color:var(--text-3);text-align:center;margin-top:4px">Uploading…</div>
            </div>
        </form>
        <div class="mf-foot">
            <button onclick="document.getElementById('upload-modal').classList.remove('open')" class="btn btn-ghost">Cancel</button>
            <button onclick="submitUpload()" class="btn btn-primary">Upload</button>
        </div>
    </div>
</div>

{{-- Version Modal --}}
<div id="version-modal" class="modal-overlay">
    <div class="modal-box" style="width:420px">
        <button class="modal-close" onclick="document.getElementById('version-modal').classList.remove('open')">×</button>
        <div class="modal-title">Upload New Version</div>
        <div id="version-doc-name" class="modal-sub"></div>
        <form id="version-form" enctype="multipart/form-data">
            @csrf
            <div class="mf-field">
                <label class="mf-label">File *</label>
                <input type="file" name="file" required style="font-size:13px;width:100%;margin-bottom:8px">
            </div>
            <div class="mf-field">
                <label class="mf-label">Version Notes</label>
                <textarea name="notes" placeholder="What changed in this version…" class="mf-textarea" rows="2"></textarea>
            </div>
        </form>
        <div class="mf-foot">
            <button onclick="document.getElementById('version-modal').classList.remove('open')" class="btn btn-ghost">Cancel</button>
            <button onclick="submitVersion()" class="btn btn-primary">Upload Version</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Document date-range filter (server-side) ─────────────────────────────────
_drCbs['doc-dr'] = function() {
    var from = document.getElementById('doc-dr-from') ? document.getElementById('doc-dr-from').value : '';
    var to   = document.getElementById('doc-dr-to')   ? document.getElementById('doc-dr-to').value   : '';
    var fromHidden = document.getElementById('doc-date-from');
    var toHidden   = document.getElementById('doc-date-to');
    if (fromHidden) fromHidden.value = from;
    if (toHidden)   toHidden.value   = to;
    var form = document.getElementById('doc-filter-form');
    if (form) form.submit();
};

// ── Pre-populate DR picker label if date params are in URL ───────────────────
(function() {
    var from = document.getElementById('doc-date-from') ? document.getElementById('doc-date-from').value : '';
    var to   = document.getElementById('doc-date-to')   ? document.getElementById('doc-date-to').value   : '';
    if (from || to) {
        var label = (from && to) ? (from + ' → ' + to) : (from ? 'From ' + from : 'Until ' + to);
        var textEl = document.getElementById('doc-dr-text');
        if (textEl) textEl.textContent = label;
        var wrap = document.getElementById('doc-dr');
        if (wrap) wrap.classList.add('has-val');
    }
})();

let currentDocId = null;

document.addEventListener('submit', function (e) {
    const form = e.target;
    if (!form || !form.classList || !form.classList.contains('js-doc-delete-form')) return;
    e.preventDefault();
    Swal.fire({
        title: 'Delete document?',
        text: 'This cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        reverseButtons: true,
    }).then(function (result) {
        if (!result.isConfirmed) return;
        form.submit();
    });
});

function handleDrop(e) {
    e.preventDefault();
    e.currentTarget.style.borderColor = 'var(--border)';
    const file = e.dataTransfer.files[0];
    if (file) {
        document.getElementById('file-input').files = e.dataTransfer.files;
        document.getElementById('file-name').textContent = file.name;
        document.getElementById('file-name').style.display = 'block';
    }
}

async function submitUpload() {
    const fileInput = document.getElementById('file-input');
    if (!fileInput.files[0]) { showToast('Please select a file.', 'error'); return; }
    document.getElementById('upload-progress').style.display = 'block';
    const bar  = document.getElementById('progress-bar');
    const text = document.getElementById('progress-text');

    const xhr = new XMLHttpRequest();
    xhr.upload.onprogress = (e) => {
        if (e.lengthComputable) {
            const pct = Math.round(e.loaded / e.total * 100);
            bar.style.width = pct + '%';
            text.textContent = `Uploading… ${pct}%`;
        }
    };
    xhr.onload = () => {
        const json = JSON.parse(xhr.responseText);
        if (json.status === 'success') {
            showToast('Document uploaded!', 'success');
            setTimeout(() => window.location.reload(), 800);
        } else {
            showToast(json.message || 'Upload failed.', 'error');
        }
        document.getElementById('upload-progress').style.display = 'none';
    };
    xhr.open('POST', '{{ route("documents.upload") }}');
    xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name=csrf-token]').content);
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.send(new FormData(document.getElementById('upload-form')));
}

function openVersionModal(docId, docName) {
    currentDocId = docId;
    document.getElementById('version-doc-name').textContent = `Document: ${docName}`;
    document.getElementById('version-modal').classList.add('open');
}

async function submitVersion() {
    if (!currentDocId) return;
    const r = await fetch('{{ route("documents.version", ":id") }}'.replace(':id', currentDocId), {
        method: 'POST',
        body: new FormData(document.getElementById('version-form')),
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
    });
    const json = await r.json();
    showToast(json.message, json.status === 'success' ? 'success' : 'error');
    if (json.status === 'success') {
        document.getElementById('version-modal').classList.remove('open');
        setTimeout(() => window.location.reload(), 800);
    }
}
</script>
@endpush
