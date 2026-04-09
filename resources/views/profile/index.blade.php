@extends('layouts.app')
@section('title','My Profile')
@section('page-title','My Profile')

@section('content')
<div class="max-w-2xl space-y-5">

    {{-- Profile Info --}}
    <div class="card">
        <div class="card-header"><h3>Profile Information</h3></div>
        <div class="card-body">
            <form id="profile-form" data-ajax-form method="POST"
                  action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf

                <div class="flex items-center gap-5 mb-6">
                    <div class="relative">
                        <img id="avatar-preview"
                             src="{{ $profileUser->image_url }}"
                             class="w-20 h-20 rounded-full object-cover border-4 border-white shadow-md" alt="Avatar">
                        <button type="button"
                                onclick="document.getElementById('avatar-input').click()"
                                class="absolute -bottom-1 -right-1 w-8 h-8 rounded-full flex items-center justify-center shadow text-white text-sm"
                                style="background:var(--primary,#2563eb)">
                            <span class="material-icons text-sm">photo_camera</span>
                        </button>
                        <input type="file" id="avatar-input" name="image" class="hidden" accept="image/*"
                               onchange="previewAvatar(this)">
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">{{ $profileUser->name }}</p>
                        <p class="text-sm text-gray-400">{{ $profileUser->email }}</p>
                        <p class="text-xs text-gray-400 mt-1">
                            Member since {{ $profileUser->created_at->format('F Y') }} ·
                            Last login: {{ $profileUser->last_login?->diffForHumans() ?? 'Unknown' }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group col-span-2">
                        <label class="form-label">Full Name <span>*</span></label>
                        <input type="text" name="name" class="form-control"
                               value="{{ old('name', $profileUser->name) }}" required>
                    </div>

                    <div class="form-group col-span-2">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" value="{{ $profileUser->email }}"
                               readonly style="opacity:.6" title="Email cannot be changed here">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Mobile Number</label>
                        <input type="tel" name="mobile" class="form-control"
                               value="{{ old('mobile', $profileUser->mobile) }}" placeholder="+1 555 000 0000">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Language</label>
                        <select name="locale" class="form-control">
                            <option value="en" {{ $profileUser->locale === 'en' ? 'selected' : '' }}>English</option>
                            <option value="es" {{ $profileUser->locale === 'es' ? 'selected' : '' }}>Español</option>
                            <option value="fr" {{ $profileUser->locale === 'fr' ? 'selected' : '' }}>Français</option>
                            <option value="de" {{ $profileUser->locale === 'de' ? 'selected' : '' }}>Deutsch</option>
                        </select>
                    </div>
                </div>

                <label class="form-check mb-4">
                    <input type="checkbox" name="dark_theme" value="1" {{ $profileUser->dark_theme ? 'checked' : '' }}>
                    <span class="text-sm text-gray-700">Enable dark mode</span>
                </label>

                <button type="submit" class="btn btn-primary">
                    <span class="material-icons text-lg">save</span> Save Changes
                </button>
            </form>
        </div>
    </div>

    {{-- Change Password --}}
    <div class="card">
        <div class="card-header"><h3>Change Password</h3></div>
        <div class="card-body">
            <form id="password-form" data-ajax-form method="POST" action="{{ route('profile.password') }}">
                @csrf
                <div class="space-y-4">
                    <div class="form-group">
                        <label class="form-label">Current Password <span>*</span></label>
                        <input type="password" name="current_password" class="form-control" required autocomplete="current-password">
                    </div>
                    <div class="form-group">
                        <label class="form-label">New Password <span>*</span></label>
                        <input type="password" name="password" id="new-pw" class="form-control" required minlength="8" autocomplete="new-password">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm New Password <span>*</span></label>
                        <input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
                    </div>

                    {{-- Password strength indicator --}}
                    <div>
                        <div class="progress mb-1"><div id="pw-strength-bar" class="progress-bar" style="width:0%;background:#ef4444"></div></div>
                        <p id="pw-strength-text" class="text-xs text-gray-400">Enter a password</p>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <span class="material-icons text-lg">lock</span> Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Account Info --}}
    <div class="card">
        <div class="card-header"><h3>Account Information</h3></div>
        <div class="card-body">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-400 text-xs uppercase tracking-wider mb-1">Account Type</p>
                    <p class="font-medium">{{ $profileUser->is_superadmin ? 'Super Administrator' : 'Tenant User' }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-xs uppercase tracking-wider mb-1">Roles</p>
                    <div class="flex flex-wrap gap-1">
                        @foreach($profileUser->getRoleNames() as $role)
                        <span class="badge badge-blue text-xs">{{ Str::title(str_replace('_',' ',$role)) }}</span>
                        @endforeach
                    </div>
                </div>
                @if(!$profileUser->is_superadmin && $profileUser->company)
                <div>
                    <p class="text-gray-400 text-xs uppercase tracking-wider mb-1">Company</p>
                    <p class="font-medium">{{ $profileUser->company->company_name }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-xs uppercase tracking-wider mb-1">Subdomain</p>
                    <p class="font-mono text-xs">{{ $profileUser->company->subdomain }}.{{ config('tenancy.root_domain') }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById('avatar-preview').src = e.target.result;
        reader.readAsDataURL(input.files[0]);
    }
}

// Password strength meter
document.getElementById('new-pw').addEventListener('input', function() {
    const pw  = this.value;
    let score = 0;
    if (pw.length >= 8)      score++;
    if (/[A-Z]/.test(pw))    score++;
    if (/[0-9]/.test(pw))    score++;
    if (/[^A-Za-z0-9]/.test(pw)) score++;

    const bar   = document.getElementById('pw-strength-bar');
    const text  = document.getElementById('pw-strength-text');
    const data  = [
        { w:'0%',   color:'#ef4444', label:'Enter a password' },
        { w:'25%',  color:'#ef4444', label:'Weak' },
        { w:'50%',  color:'#f59e0b', label:'Fair' },
        { w:'75%',  color:'#3b82f6', label:'Good' },
        { w:'100%', color:'#22c55e', label:'Strong' },
    ];
    bar.style.width       = data[score].w;
    bar.style.background  = data[score].color;
    text.textContent      = data[score].label;
    text.style.color      = data[score].color;
});
</script>
@endpush
