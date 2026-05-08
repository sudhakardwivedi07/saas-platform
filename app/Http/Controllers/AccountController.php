<?php

namespace App\Http\Controllers;

use App\Models\ClientPackage;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->pageTitle = 'My Account';
    }

    public function index()
    {
        if (!auth()->user()->hasAnyRole(['admin', 'manager'])) {
            $company  = company();
            $parentId = $company->created_by;

            $plans = $parentId
                ? ClientPackage::where('company_id', $parentId)
                    ->where('is_active', true)
                    ->orderBy('sort')
                    ->orderBy('monthly_price')
                    ->get()
                : collect();

            $this->plans        = $plans;
            $this->currentPlan  = $company->activePlan();
            $this->subscription = $company->subscription;

            $this->data['myTickets'] = SupportTicket::where('user_id', auth()->id())
                ->with('user', 'company')
                ->orderByDesc('created_at')
                ->get();

            return view('account.user', $this->data);
        }

        return view('account.index', $this->data);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update(['name' => $request->name, 'email' => $request->email]);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password changed successfully.');
    }
}
