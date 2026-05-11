{{-- billing/success.blade.php --}}
@extends('layouts.app')
@section('title','Payment Successful')
@section('page-title','Payment Successful')
@section('content')
<div class="max-w-lg mx-auto text-center py-12">
    <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6"
         style="background:#dcfce7">
        <span class="material-icons text-5xl text-green-600">check_circle</span>
    </div>
    <h2 class="text-2xl font-bold text-gray-800 mb-2">Subscription Activated</h2>
    <p class="text-gray-500 mb-8">Your subscription is now active. You can continue to the dashboard or manage your billing anytime.</p>
    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">Go to Dashboard →</a>
</div>
@endsection
