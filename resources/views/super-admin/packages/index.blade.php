@extends('layouts.superadmin.app')
@section('title','Packages')
@section('page-title','Packages')

@section('content')
<div class="flex justify-end mb-5">
    <a href="{{ route('superadmin.packages.create') }}" class="btn btn-primary">
        <span class="material-icons text-lg">add</span> New Package
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
    @forelse($packages as $pkg)
    <div class="card relative overflow-hidden">
        @if($pkg->is_recommended)
        <div class="absolute top-3 right-3">
            <span class="badge badge-green text-xs">Recommended</span>
        </div>
        @endif
        <div class="card-body">
            <h3 class="text-lg font-bold text-slate-800 mb-1">{{ $pkg->name }}</h3>
            <p class="text-slate-500 text-sm mb-4">{{ $pkg->description }}</p>

            <div class="flex items-end gap-1 mb-1">
                <span class="text-3xl font-bold text-slate-800">${{ number_format($pkg->monthly_price, 0) }}</span>
                <span class="text-slate-400 text-sm mb-1">/mo</span>
            </div>
            <p class="text-xs text-slate-400 mb-4">${{ number_format($pkg->annual_price, 0) }}/yr billed annually</p>

            <div class="space-y-2 text-sm text-slate-600 mb-5">
                <div class="flex items-center gap-2">
                    <span class="material-icons text-base text-blue-500">group</span>
                    {{ $pkg->max_users ? $pkg->max_users . ' users' : 'Unlimited users' }}
                </div>
                <div class="flex items-center gap-2">
                    <span class="material-icons text-base text-blue-500">storage</span>
                    {{ $pkg->max_storage_gb ? $pkg->max_storage_gb . ' GB storage' : 'Unlimited storage' }}
                </div>
                <div class="flex items-center gap-2">
                    <span class="material-icons text-base text-blue-500">business</span>
                    {{ $pkg->companies_count }} active companies
                </div>
                @if($pkg->is_trial_allowed)
                <div class="flex items-center gap-2">
                    <span class="material-icons text-base text-amber-500">hourglass_empty</span>
                    {{ $pkg->trial_days }}-day free trial
                </div>
                @endif
            </div>

            <div class="flex gap-2 pt-4 border-t border-slate-100">
                <a href="{{ route('superadmin.packages.edit', $pkg) }}" class="btn btn-secondary flex-1 justify-center text-sm">
                    <span class="material-icons text-sm">edit</span> Edit
                </a>
                <form method="POST" action="{{ route('superadmin.packages.destroy', $pkg) }}"
                      onsubmit="return confirm('Delete this package?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger text-sm px-3">
                        <span class="material-icons text-sm">delete</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-3 text-center py-16 text-slate-400">
        <span class="material-icons text-5xl mb-3 block">inventory_2</span>
        No packages created yet.
        <a href="{{ route('superadmin.packages.create') }}" class="text-blue-600 block mt-2">Create your first package →</a>
    </div>
    @endforelse
</div>
@endsection
