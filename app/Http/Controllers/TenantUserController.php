<?php

namespace App\Http\Controllers;

use App\Mail\UserInvitationMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\Role;

class TenantUserController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->pageTitle = 'Users';
    }

    public function index()
    {
        abort_403(!auth()->user()->can('view_users'));

        $companyId = auth()->user()->company_id;

        $this->users = User::where('company_id', $companyId)
            ->with('roles')
            ->orderByDesc('created_at')
            ->get();

        $this->roles = Role::whereNotIn('name', ['super_admin'])
            ->get();

        return view('users.index', $this->data);
    }

    public function store(Request $request)
    {
        abort_403(!auth()->user()->can('create_users'));

        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:users,email',
            'role'      => 'required|string|exists:roles,name',
            'job_title' => 'nullable|string|max:100',
            'password'  => 'required|string|min:8',
        ]);

        $companyId = auth()->user()->company_id;

        // Enforce plan's Max Clients limit when adding a 'client'-role member
        if ($request->role === 'client') {
            $plan = auth()->user()->company?->activePlan();

            if ($plan && isset($plan->max_users) && (int) $plan->max_users > 0) {
                $currentCount = User::where('company_id', $companyId)
                    ->whereHas('roles', fn ($q) => $q->where('name', 'client'))
                    ->count();

                if ($currentCount >= (int) $plan->max_users) {
                    return redirect()->route('users.index')
                        ->with('error', 'Maximum limit exceeded. Please update your plan to add more clients.');
                }
            }
        }

        $companyName = auth()->user()->company?->company_name ?? config('app.name');
        $plainPassword = $request->password;

        $user = User::create([
            'company_id' => $companyId,
            'name'       => $request->name,
            'email'      => $request->email,
            'job_title'  => $request->job_title,
            'password'   => Hash::make($plainPassword),
            'status'     => 'active',
        ]);

        $user->assignRole($request->role);

        // Send invitation email with credentials
        try {
            Mail::to($user->email)->send(new UserInvitationMail(
                user: $user,
                plainPassword: $plainPassword,
                companyName: $companyName,
                loginUrl: route('login'),
            ));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('User invitation email failed: ' . $e->getMessage());
        }

        return redirect()->route('users.index')
            ->with('success', 'User "' . $user->name . '" added successfully.');
    }

    public function update(Request $request, $id)
    {
        abort_403(!auth()->user()->can('edit_users'));

        $user = User::where('company_id', auth()->user()->company_id)->findOrFail($id);

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email,' . $id,
            'role'     => 'required|string|exists:roles,name',
            'password' => 'nullable|string|min:8',
        ]);

        $user->name  = $request->name;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();
        $user->syncRoles([$request->role]);

        return redirect()->route('users.index')
            ->with('toast_success', 'User "' . $user->name . '" updated successfully.');
    }

    public function toggleStatus($id)
    {
        abort_403(!auth()->user()->can('edit_users'));

        $user = User::where('company_id', auth()->user()->company_id)->findOrFail($id);

        // Prevent disabling the owner (admin)
        if ($user->hasRole('admin')) {
            return response()->json([
                'status'  => 'warning',
                'message' => 'Owner account cannot be disabled.',
            ]);
        }

        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        $action = $user->status === 'active' ? 'enabled' : 'disabled';

        return response()->json([
            'status'     => 'success',
            'message'    => 'User "' . $user->name . '" ' . $action . ' successfully.',
            'newStatus'  => $user->status,
            'userId'     => $user->id,
        ]);
    }

    public function destroy($id)
    {
        abort_403(!auth()->user()->can('delete_users'));

        $user = User::where('company_id', auth()->user()->company_id)->findOrFail($id);

        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('toast_warning', 'You cannot remove yourself.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('toast_success', 'User "' . $user->name . '" removed successfully.');
    }
}
