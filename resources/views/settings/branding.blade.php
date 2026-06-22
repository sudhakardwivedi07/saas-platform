@extends('layouts.app')
@section('title','Branding')
@section('page-title','Branding & Appearance')

@section('content')
<form id="branding-form" data-ajax-form method="POST"
      action="{{ route('settings.branding.update') }}" enctype="multipart/form-data">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: Settings --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Logo & Images --}}
            <div class="card">
                <div class="card-header"><h3>Logo & Images</h3></div>
                <div class="card-body grid grid-cols-1 sm:grid-cols-2 gap-5">
                    @foreach([
                        ['logo',             'Primary Logo',          'Shown in the sidebar and emails. Recommended: 200×60px PNG'],
                        ['dark_logo',        'Dark Mode Logo',        'Used when dark mode is enabled. Same dimensions as primary logo.'],
                        ['favicon',          'Favicon',               '32×32px ICO or PNG'],
                        ['login_background', 'Login Background',     'Shown behind the login form. Recommended: 1920×1080px'],
                    ] as [$field, $label, $hint])
                    <div class="form-group">
                        <label class="form-label">{{ $label }}</label>
                        <div class="border-2 border-dashed border-gray-200 rounded-xl p-4 text-center hover:border-blue-400 transition cursor-pointer"
                             onclick="document.getElementById('{{ $field }}-input').click()">
                            @if($branding?->{$field})
                            <img src="{{ asset('storage/' . $branding->{$field}) }}"
                                 class="h-16 object-contain mx-auto mb-2 rounded" alt="{{ $label }}">
                            @else
                            <span class="material-icons text-3xl text-gray-300 block mb-2">image</span>
                            @endif
                            <p class="text-xs text-gray-400">{{ $hint }}</p>
                            <p class="text-xs text-blue-600 mt-1">Click to upload</p>
                        </div>
                        <input type="file" id="{{ $field }}-input" name="{{ $field }}" class="hidden"
                               accept="image/*" onchange="previewImage(this, '{{ $field }}-preview')">
                        <img id="{{ $field }}-preview" class="hidden h-12 mt-2 object-contain rounded">
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Colours --}}
            <div class="card">
                <div class="card-header"><h3>Brand Colours</h3></div>
                <div class="card-body">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        @foreach([
                            ['primary_color',   'Primary',   'Main brand colour — buttons, links, sidebar'],
                            ['secondary_color', 'Secondary', 'Supporting colour — headers, borders'],
                            ['accent_color',    'Accent',    'Highlight colour — hover states, badges'],
                        ] as [$field, $label, $hint])
                        <div class="form-group">
                            <label class="form-label">{{ $label }}</label>
                            <div class="flex items-center gap-3">
                                <div class="color-swatch">
                                    <input type="color" name="{{ $field }}"
                                           value="{{ $branding?->{$field} ?? '#2563eb' }}"
                                           data-preview="#{{ $field }}-preview-box"
                                           oninput="updateColorPreview(this)">
                                </div>
                                <input type="text" id="{{ $field }}-text" class="form-control font-mono text-sm"
                                       value="{{ $branding?->{$field} ?? '#2563eb' }}"
                                       maxlength="7" pattern="#[0-9a-fA-F]{6}"
                                       oninput="syncColor(this, '{{ $field }}')">
                            </div>
                            <p class="form-text">{{ $hint }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Typography --}}
            <div class="card">
                <div class="card-header"><h3>Typography</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Font Family</label>
                        <select name="font_family" class="form-control" onchange="updateFontPreview(this.value)">
                            @foreach(['Inter','Roboto','Open Sans','Lato','Poppins','Nunito','DM Sans','Outfit'] as $font)
                            <option value="{{ $font }}" {{ ($branding?->font_family ?? 'Inter') === $font ? 'selected' : '' }}>
                                {{ $font }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div id="font-preview" class="bg-gray-50 rounded-lg p-4 text-sm border border-gray-100">
                        <p class="text-lg font-semibold mb-1">The quick brown fox jumps</p>
                        <p class="text-gray-500">Your brand font will be applied across the entire platform including emails, dashboards, and all user interfaces.</p>
                    </div>
                </div>
            </div>

            {{-- Login Page Text --}}
            <div class="card">
                <div class="card-header"><h3>Login Page</h3></div>
                <div class="card-body grid grid-cols-1 gap-4">
                    <div class="form-group">
                        <label class="form-label">Heading</label>
                        <input type="text" name="login_heading" class="form-control"
                               value="{{ $branding?->login_heading ?? '' }}"
                               placeholder="Welcome back">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Subheading</label>
                        <input type="text" name="login_subheading" class="form-control"
                               value="{{ $branding?->login_subheading ?? '' }}"
                               placeholder="Sign in to your account to continue.">
                    </div>
                </div>
            </div>

            {{-- Custom CSS --}}
            <div class="card">
                <div class="card-header"><h3>Custom CSS</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Additional CSS</label>
                        <textarea name="custom_css" rows="8" class="form-control font-mono text-sm"
                                  placeholder="/* Override any platform styles here */
.sidebar-brand { border-radius: 0; }
.btn-primary   { border-radius: 999px; }">{{ $branding?->custom_css }}</textarea>
                        <p class="form-text">This CSS is injected on every page for your tenants. Keep it concise.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Live preview --}}
        <div class="space-y-5">
            <div class="card sticky top-4">
                <div class="card-header"><h3>Live Preview</h3></div>
                <div class="card-body p-0 overflow-hidden rounded-b-xl">
                    {{-- Mini sidebar preview --}}
                    <div id="preview-sidebar" class="flex flex-col" style="background:{{ $branding?->primary_color ?? '#2563eb' }};min-height:240px;padding:1rem">
                        <div class="flex items-center gap-2 mb-4">
                            @if($branding?->logo)
                            <img src="{{ asset('storage/' . $branding->logo) }}" class="h-6 object-contain">
                            @else
                            <div class="h-6 bg-white/30 rounded flex-1 max-w-24"></div>
                            @endif
                        </div>
                        @foreach(['Dashboard','Documents','QuickBooks','Billing','Settings'] as $navItem)
                        <div class="flex items-center gap-2 px-2 py-1.5 rounded-md mb-0.5 {{ $loop->first ? 'bg-white/20 text-white font-medium' : 'text-white/70' }}"
                             style="font-size:.8rem">
                            <div class="w-4 h-4 bg-current rounded-sm opacity-60"></div>
                            {{ $navItem }}
                        </div>
                        @endforeach
                    </div>

                    {{-- Preview buttons --}}
                    <div class="p-4 space-y-2">
                        <button id="preview-btn" class="btn btn-block text-sm"
                                style="background:{{ $branding?->primary_color ?? '#2563eb' }};color:#fff">
                            Primary Button
                        </button>
                        <p id="preview-link" class="text-sm text-center"
                           style="color:{{ $branding?->primary_color ?? '#2563eb' }}">
                            Primary link colour →
                        </p>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-block">
                        <span class="material-icons text-lg">save</span> Save Branding
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; preview.classList.remove('hidden'); };
        reader.readAsDataURL(input.files[0]);
    }
}

function updateColorPreview(input) {
    const val  = input.value;
    const field = input.name;

    // Update text input
    document.getElementById(`${field}-text`).value = val;

    // Update live preview
    if (field === 'primary_color') {
        document.getElementById('preview-sidebar').style.background = val;
        document.getElementById('preview-btn').style.background = val;
        document.getElementById('preview-link').style.color = val;
    }
}

function syncColor(textInput, field) {
    const val = textInput.value;
    if (/^#[0-9a-fA-F]{6}$/.test(val)) {
        document.querySelector(`[name="${field}"]`).value = val;
        updateColorPreview({ name: field, value: val });
    }
}

function updateFontPreview(font) {
    const link = document.querySelector(`link[href*="fonts.googleapis"]`);
    document.getElementById('font-preview').style.fontFamily = `'${font}', sans-serif`;
}

document.getElementById('branding-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = e.target.querySelector('[type=submit]');
    btn.disabled = true; btn.innerHTML = '<span class="material-icons spin">refresh</span> Saving…';
    const json = await fetch(e.target.action, {
        method:'POST',
        body: new FormData(e.target),
        headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}
    }).then(r=>r.json());
    handleReply(json);
    btn.disabled = false; btn.innerHTML = '<span class="material-icons text-lg">save</span> Save Branding';
});
</script>
@endpush
