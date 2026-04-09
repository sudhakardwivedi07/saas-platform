@extends('layouts.app')
@section('title','SMS Settings')
@section('page-title','SMS / Messaging Settings')

@section('content')
<div class="max-w-2xl">
<form id="sms-form" data-ajax-form method="POST" action="{{ route('settings.sms.update') }}">
    @csrf
    <div class="card">
        <div class="card-header"><h3>SMS Provider Configuration</h3></div>
        <div class="card-body space-y-5">

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-800 flex gap-3">
                <span class="material-icons text-lg">info</span>
                <div>
                    SMS is used for two-factor authentication and event notifications.
                    Configure your Twilio account credentials below.
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Provider</label>
                <select name="provider" class="form-control">
                    <option value="twilio"  {{ $smsSetting?->provider === 'twilio'  ? 'selected' : '' }}>Twilio</option>
                    <option value="vonage"  {{ $smsSetting?->provider === 'vonage'  ? 'selected' : '' }}>Vonage (Nexmo)</option>
                    <option value="aws_sns" {{ $smsSetting?->provider === 'aws_sns' ? 'selected' : '' }}>AWS SNS</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Account SID</label>
                    <input type="text" name="account_sid" class="form-control font-mono text-sm"
                           placeholder="ACxxxxxxxxxxxxxxxxxxxx" autocomplete="off"
                           value="{{ $smsSetting?->getRawOriginal('account_sid') ? '••••••••' : '' }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Auth Token</label>
                    <input type="password" name="auth_token" class="form-control font-mono text-sm"
                           placeholder="Leave blank to keep current" autocomplete="new-password">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">From Number / Sender ID</label>
                <input type="text" name="from_number" class="form-control"
                       placeholder="+15551234567" value="{{ $smsSetting?->from_number }}">
                <p class="form-text">Must be a verified number in your Twilio account.</p>
            </div>

            <label class="form-check">
                <input type="checkbox" name="is_active" value="1"
                       {{ $smsSetting?->is_active ? 'checked' : '' }}>
                <span class="text-sm text-gray-700">Enable SMS messaging</span>
            </label>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn btn-primary">
                    <span class="material-icons text-lg">save</span> Save Settings
                </button>
            </div>
        </div>
    </div>
</form>
</div>
@endsection
