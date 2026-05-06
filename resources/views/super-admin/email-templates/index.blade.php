@extends('layouts.superadmin.app')
@section('title', 'Email Templates')
@section('page-title', 'Email Templates')

@section('content')
<div class="ph">
    <div>
        <h1>Email Templates</h1>
        <p>System-wide templates used when no tenant-specific override exists</p>
    </div>
</div>

<div class="tc">
    <div class="tc-head">
        <div style="font-size:14px;font-weight:600">Global Email Templates</div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Template</th>
                <th>Subject</th>
                <th>Slug</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($templates as $tpl)
            <tr>
                <td style="font-weight:600">{{ $tpl->name }}</td>
                <td style="color:var(--text-2)">{{ $tpl->subject }}</td>
                <td><code style="font-size:11.5px;background:var(--bg);padding:2px 7px;border-radius:5px;color:var(--blue)">{{ $tpl->slug }}</code></td>
                <td>
                    <a href="{{ route('superadmin.email-templates.edit', $tpl->slug) }}" class="btn btn-ghost btn-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Edit
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align:center;color:var(--text-3);padding:40px">No templates found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
