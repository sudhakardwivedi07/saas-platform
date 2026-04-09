@extends('layouts.app')

@section('title', 'My Companies')
@section('page-title', 'My Companies')

@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Companies You Manage</h2>
            <p class="text-sm text-gray-500 mt-1">Create and manage multiple companies from one account.</p>
        </div>
        @can('add_company')
        <a href="{{ route('my-companies.create') }}" class="btn btn-primary flex items-center gap-2" style="display:inline-flex">
            <span class="material-icons text-lg">add_business</span>
            Add New Company
        </a>
        @endcan
    </div>

    {{-- Companies Grid --}}
    @if($companies->isEmpty())
    <div class="card">
        <div class="card-body text-center py-16">
            <span class="material-icons text-6xl text-gray-300">business</span>
            <h3 class="mt-4 text-lg font-semibold text-gray-600">No companies yet</h3>
            <p class="text-gray-400 mt-1">You haven't created any additional companies.</p>
            @can('add_company')
            <a href="{{ route('my-companies.create') }}" class="btn btn-primary mt-5">
                <span class="material-icons text-lg mr-1">add_business</span>
                Create Your First Company
            </a>
            @endcan
        </div>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @foreach($companies as $co)
        @php
            $sub    = $co->subscription;
            $badge  = match($co->status) {
                'active'    => ['bg-green-100 text-green-700',  'Active'],
                'inactive'  => ['bg-yellow-100 text-yellow-700','Pending Payment'],
                'suspended' => ['bg-red-100 text-red-700',      'Suspended'],
                default     => ['bg-gray-100 text-gray-600',    ucfirst($co->status)],
            };
        @endphp
        <div class="card hover:shadow-md transition-shadow">
            <div class="card-body space-y-3">
                {{-- Company header --}}
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-white font-bold text-lg"
                             style="background: var(--primary)">
                            {{ strtoupper(substr($co->company_name, 0, 1)) }}
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800 leading-tight">{{ $co->company_name }}</h3>
                            <p class="text-xs text-gray-400">{{ $co->subdomain }}.{{ config('tenancy.root_domain') }}</p>
                        </div>
                    </div>
                    <span class="text-xs font-medium px-2 py-1 rounded-full {{ $badge[0] }}">
                        {{ $badge[1] }}
                    </span>
                </div>

                {{-- Package & Subscription --}}
                <div class="text-sm text-gray-600 space-y-1 border-t border-gray-100 pt-3">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Plan</span>
                        @php $planName = $co->clientPackage?->name ?? $co->package?->name ?? '—'; @endphp
                        <span class="font-medium">{{ $planName }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Subscription</span>
                        <span class="font-medium capitalize">{{ $sub?->status ?? 'None' }}</span>
                    </div>
                    @if($co->trial_ends_at && $co->trial_ends_at->isFuture())
                    <div class="flex justify-between">
                        <span class="text-gray-400">Trial ends</span>
                        <span class="font-medium text-amber-600">{{ $co->trial_ends_at->format('M d, Y') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-gray-400">Email</span>
                        <span class="font-medium">{{ $co->company_email }}</span>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex gap-2 pt-2 border-t border-gray-100">
                    @if($co->status === 'inactive')
                    <span class="text-xs text-yellow-600 flex items-center gap-1">
                        <span class="material-icons text-sm">hourglass_empty</span>
                        Awaiting payment activation
                    </span>
                    @else
                    <a href="{{ $co->login_url }}" target="_blank"
                       class="btn btn-outline btn-sm flex items-center gap-1 text-xs">
                        <span class="material-icons text-sm">open_in_new</span>
                        Open
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>
@endsection
