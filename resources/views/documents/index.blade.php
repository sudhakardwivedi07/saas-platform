@extends('layouts.app')
@section('title','Documents')
@section('page-title','Documents')

@section('content')

{{-- Header + Upload --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div>
        <p class="text-sm text-gray-500">{{ $documents->total() }} document{{ $documents->total() !== 1 ? 's' : '' }}</p>
    </div>
    @can('upload_documents')
    <button onclick="document.getElementById('upload-modal').classList.remove('hidden')"
            class="btn btn-primary">
        <span class="material-icons text-lg">upload_file</span> Upload Document
    </button>
    @endcan
</div>

{{-- Search --}}
<form method="GET" action="{{ route('documents.index') }}" class="flex flex-wrap gap-3 mb-5">
    <div class="relative flex-1 min-w-56">
        <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
        <input name="search" value="{{ request('search') }}" placeholder="Search documents…"
               class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 bg-white">
    </div>
    <select name="type" class="border border-gray-200 rounded-lg px-3 py-2.5 text-sm bg-white focus:outline-none focus:border-blue-500">
        <option value="">All Types</option>
        <option value="pdf" {{ request('type')==='pdf' ? 'selected':'' }}>PDF</option>
        <option value="image" {{ request('type')==='image' ? 'selected':'' }}>Images</option>
        <option value="word" {{ request('type')==='word' ? 'selected':'' }}>Word Docs</option>
    </select>
    <button type="submit" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium transition">Filter</button>
</form>

{{-- Documents Grid --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    @forelse($documents as $doc)
    <div class="flex items-center gap-4 px-5 py-4 border-b border-gray-50 last:border-0 hover:bg-gray-50 transition group">
        {{-- Icon --}}
        <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0
            {{ str_contains($doc->mime_type,'pdf')   ? 'bg-red-100'   :
               (str_contains($doc->mime_type,'image') ? 'bg-purple-100' :
               (str_contains($doc->mime_type,'word')  ? 'bg-blue-100'  : 'bg-gray-100')) }}">
            <span class="material-icons text-xl
                {{ str_contains($doc->mime_type,'pdf')   ? 'text-red-500'    :
                   (str_contains($doc->mime_type,'image') ? 'text-purple-500' :
                   (str_contains($doc->mime_type,'word')  ? 'text-blue-500'   : 'text-gray-400')) }}">
                {{ str_contains($doc->mime_type,'pdf') ? 'picture_as_pdf' :
                   (str_contains($doc->mime_type,'image') ? 'image' :
                   (str_contains($doc->mime_type,'word') ? 'article' : 'description')) }}
            </span>
        </div>

        {{-- Info --}}
        <div class="flex-1 min-w-0">
            <p class="font-medium text-gray-800 truncate">{{ $doc->name }}</p>
            <p class="text-xs text-gray-400 mt-0.5">
                {{ $doc->uploader?->name ?? '—' }} ·
                {{ format_bytes($doc->size ?? 0) }} ·
                {{ $doc->created_at->format('M d, Y') }} ·
                v{{ $doc->versions()->max('version_number') ?? 1 }}
            </p>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition">
            @can('download_documents')
            <a href="{{ route('documents.download', $doc) }}"
               class="p-2 rounded-lg hover:bg-blue-100 text-gray-400 hover:text-blue-600 transition" title="Download">
                <span class="material-icons text-lg">download</span>
            </a>
            @endcan

            <button onclick="openVersionModal({{ $doc->id }}, '{{ $doc->name }}')"
                    class="p-2 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition" title="Upload new version">
                <span class="material-icons text-lg">history</span>
            </button>

            @can('delete_documents')
            <form method="POST" action="{{ route('documents.destroy', $doc) }}"
                  onsubmit="return confirm('Delete this document?')">
                @csrf @method('DELETE')
                <button type="submit" class="p-2 rounded-lg hover:bg-red-100 text-gray-400 hover:text-red-600 transition" title="Delete">
                    <span class="material-icons text-lg">delete</span>
                </button>
            </form>
            @endcan
        </div>
    </div>
    @empty
    <div class="py-16 text-center text-gray-400">
        <span class="material-icons text-5xl block mb-3">folder_open</span>
        <p class="font-medium">No documents yet</p>
        <p class="text-sm mt-1">Upload your first document to get started</p>
    </div>
    @endforelse
</div>

{{-- Pagination --}}
<div class="mt-4">{{ $documents->withQueryString()->links() }}</div>


{{-- Upload Modal --}}
<div id="upload-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Upload Document</h3>
            <button onclick="document.getElementById('upload-modal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                <span class="material-icons">close</span>
            </button>
        </div>
        <div class="p-6">
            <form id="upload-form" enctype="multipart/form-data">
                @csrf
                <div class="form-group mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Document Name <span class="text-gray-400">(optional)</span></label>
                    <input type="text" name="name" placeholder="Leave blank to use filename"
                           class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500">
                </div>
                <div class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center hover:border-blue-400 transition cursor-pointer"
                     onclick="document.getElementById('file-input').click()"
                     ondragover="event.preventDefault()" ondrop="handleDrop(event)">
                    <span class="material-icons text-4xl text-gray-300 block mb-2">cloud_upload</span>
                    <p class="text-sm text-gray-500">Click to browse or drag & drop</p>
                    <p class="text-xs text-gray-400 mt-1">PDF, Word, Excel, Images up to 50MB</p>
                    <p id="file-name" class="text-sm font-medium text-blue-600 mt-2 hidden"></p>
                </div>
                <input type="file" id="file-input" name="file" class="hidden"
                       onchange="document.getElementById('file-name').textContent = this.files[0].name;
                                 document.getElementById('file-name').classList.remove('hidden')">

                <div id="upload-progress" class="hidden mt-4">
                    <div class="bg-gray-100 rounded-full h-2 overflow-hidden">
                        <div id="progress-bar" class="bg-blue-600 h-2 rounded-full transition-all" style="width:0%"></div>
                    </div>
                    <p id="progress-text" class="text-xs text-gray-500 mt-1 text-center">Uploading…</p>
                </div>
            </form>
        </div>
        <div class="px-6 pb-6 flex gap-3">
            <button onclick="submitUpload()" class="btn btn-primary flex-1 justify-center">
                <span class="material-icons text-lg">upload</span> Upload
            </button>
            <button onclick="document.getElementById('upload-modal').classList.add('hidden')"
                    class="btn btn-secondary">Cancel</button>
        </div>
    </div>
</div>

{{-- Version Modal --}}
<div id="version-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4">
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Upload New Version</h3>
            <button onclick="document.getElementById('version-modal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                <span class="material-icons">close</span>
            </button>
        </div>
        <div class="p-6">
            <p id="version-doc-name" class="text-sm text-gray-600 mb-4"></p>
            <form id="version-form" enctype="multipart/form-data">
                @csrf
                <input type="file" name="file" class="w-full text-sm mb-3" required>
                <textarea name="notes" placeholder="Version notes (optional)…" rows="2"
                          class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500"></textarea>
            </form>
        </div>
        <div class="px-6 pb-6 flex gap-3">
            <button onclick="submitVersion()" class="btn btn-primary flex-1 justify-center">Upload Version</button>
            <button onclick="document.getElementById('version-modal').classList.add('hidden')"
                    class="btn btn-secondary">Cancel</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let currentDocId = null;

function handleDrop(e) {
    e.preventDefault();
    const file = e.dataTransfer.files[0];
    if (file) {
        document.getElementById('file-input').files = e.dataTransfer.files;
        document.getElementById('file-name').textContent = file.name;
        document.getElementById('file-name').classList.remove('hidden');
    }
}

async function submitUpload() {
    const form = document.getElementById('upload-form');
    const fileInput = document.getElementById('file-input');
    if (!fileInput.files[0]) { showToast('Please select a file.', 'error'); return; }

    document.getElementById('upload-progress').classList.remove('hidden');
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
        document.getElementById('upload-progress').classList.add('hidden');
    };
    xhr.open('POST', '{{ route("documents.upload") }}');
    xhr.setRequestHeader('X-CSRF-TOKEN', csrf);
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.send(new FormData(form));
}

function openVersionModal(docId, docName) {
    currentDocId = docId;
    document.getElementById('version-doc-name').textContent = `Document: ${docName}`;
    document.getElementById('version-modal').classList.remove('hidden');
}

async function submitVersion() {
    if (!currentDocId) return;
    const form = document.getElementById('version-form');
    const r = await fetch(`/documents/${currentDocId}/version`, {
        method: 'POST',
        body: new FormData(form),
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
    });
    const json = await r.json();
    showToast(json.message, json.status === 'success' ? 'success' : 'error');
    if (json.status === 'success') {
        document.getElementById('version-modal').classList.add('hidden');
        setTimeout(() => window.location.reload(), 800);
    }
}

const csrf = document.querySelector('meta[name="csrf-token"]').content;
</script>
@endpush
