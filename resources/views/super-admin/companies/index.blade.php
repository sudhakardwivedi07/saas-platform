@extends('layouts.superadmin.app')
@section('title', 'Companies')
@section('page-title', 'Companies')

@section('content')

{{-- Search / Filters --}}
<form method="GET" action="{{ route('superadmin.companies.index') }}" class="flex flex-wrap gap-3 mb-5">
    <div class="relative flex-1 min-w-56">
        <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
        <input name="search" value="{{ request('search') }}" placeholder="Search companies…"
               class="form-control pl-10 py-2.5">
    </div>
    <select name="status" class="form-control w-44 py-2.5">
        <option value="">All Statuses</option>
        <option value="active"    {{ request('status')==='active'    ? 'selected' : '' }}>Active</option>
        <option value="inactive"  {{ request('status')==='inactive'  ? 'selected' : '' }}>Inactive</option>
        <option value="suspended" {{ request('status')==='suspended' ? 'selected' : '' }}>Suspended</option>
    </select>
    <button type="submit" class="btn btn-primary">Filter</button>
    @if(request()->hasAny(['search','status']))
        <a href="{{ route('superadmin.companies.index') }}" class="btn btn-secondary">Clear</a>
    @endif
</form>

<div class="card">
    <div class="card-header">
        <h3>All Companies <span class="text-slate-400 font-normal">({{ $companies->total() }})</span></h3>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Company</th>
                    <th>Subdomain</th>
                    <th>Package</th>
                    <th>Subscription</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($companies as $company)
                <tr>
                    <td class="text-slate-400">{{ $company->id }}</td>
                    <td>
                        <p class="font-semibold text-slate-800">{{ $company->company_name }}</p>
                        <p class="text-xs text-slate-400">{{ $company->company_email }}</p>
                    </td>
                    <td>
                        <a href="{{ tenant_url($company->subdomain) }}" target="_blank"
                           class="text-blue-600 hover:underline font-mono text-xs">
                            {{ $company->subdomain }}.{{ config('tenancy.root_domain') }}
                        </a>
                    </td>
                    <td>
                        @if($company->package?->name)
                            {{ $company->package->name }}
                        @else
                            <span class="text-slate-300">—</span>
                        @endif
                    </td>
                    <td>
                        @if($company->subscription)
                            <span class="badge {{ $company->subscription->status === 'active' ? 'badge-green' :
                                ($company->subscription->status === 'trialing' ? 'badge-yellow' : 'badge-red') }}">
                                {{ ucfirst($company->subscription->status) }}
                            </span>
                        @else
                            <span class="badge badge-gray">None</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $company->status === 'active' ? 'badge-green' :
                            ($company->status === 'suspended' ? 'badge-red' : 'badge-yellow') }}">
                            {{ ucfirst($company->status) }}
                        </span>
                    </td>
                    <td class="text-slate-400 text-xs">{{ $company->created_at->format('M d, Y') }}</td>
                    <td>
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('superadmin.companies.show', $company) }}"
                               class="btn btn-secondary text-xs py-1 px-2">
                                <span class="material-icons text-sm">visibility</span>
                            </a>
                            <a href="{{ route('superadmin.companies.edit', $company) }}"
                               class="btn btn-secondary text-xs py-1 px-2">
                                <span class="material-icons text-sm">edit</span>
                            </a>
                            <form method="POST" action="{{ route('superadmin.companies.impersonate', $company) }}">
                                @csrf
                                <button class="btn btn-primary text-xs py-1 px-2" title="Login as admin">
                                    <span class="material-icons text-sm">login</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-12 text-slate-400">
                        <span class="material-icons text-4xl block mb-2">business_center</span>
                        No companies found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-4 border-t border-slate-100">
        {{ $companies->withQueryString()->links('components.pagination') }}
    </div>
</div>

@endsection
