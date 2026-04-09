@extends('layouts.superadmin.app')
@section('title', 'Email Templates')
@section('page-title', 'Email Templates')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Global Email Templates</h3>
        <p class="text-sm text-slate-400">System-wide templates used when no tenant-specific override exists.</p>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Template</th>
                    <th>Subject</th>
                    <th>Slug</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($templates as $tpl)
                <tr>
                    <td class="font-semibold text-slate-800">{{ ucwords(str_replace('_',' ',$tpl->slug)) }}</td>
                    <td class="text-slate-600">{{ $tpl->subject }}</td>
                    <td><code class="text-xs bg-slate-100 px-2 py-0.5 rounded">{{ $tpl->slug }}</code></td>
                    <td>
                        <a href="{{ route('superadmin.email-templates.edit', $tpl->slug) }}" class="btn btn-secondary text-xs py-1 px-2">
                            <span class="material-icons text-sm">edit</span> Edit
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-12 text-slate-400">No templates found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
