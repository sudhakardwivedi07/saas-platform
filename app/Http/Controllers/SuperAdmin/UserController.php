<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['auth', 'super-admin']);
        $this->pageTitle = 'Users';
    }

    public function index(Request $request)
    {
        $this->users = User::withoutGlobalScopes()
            ->with(['company', 'roles'])
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%"))
            ->when($request->company_id, fn ($q) => $q->where('company_id', $request->company_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('created_at')
            ->paginate(20);

        $this->companies = Company::withoutGlobalScopes()->active()->orderBy('company_name')->get();

        return view('super-admin.users.index', $this->data);
    }

    public function create()
    {
        $this->companies = Company::withoutGlobalScopes()->active()->orderBy('company_name')->get();
        $this->roles     = Role::orderBy('name')->get();
        return view('super-admin.users.create', $this->data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|string|min:8',
            'company_id' => 'nullable|exists:companies,id',
            'role'       => 'required|string|exists:roles,name',
            'status'     => 'required|in:active,inactive',
        ]);

        $user = User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'password'          => Hash::make($request->password),
            'company_id'        => $request->company_id,
            'status'            => $request->status,
            'admin_approval'    => true,
            'email_verified_at' => now(),
        ]);

        $user->assignRole($request->role);

        return Reply::successWithData('User created successfully.', ['redirect' => route('superadmin.users.index')]);
    }

    public function edit(int $id)
    {
        $this->user      = User::withoutGlobalScopes()->with('roles')->findOrFail($id);
        $this->companies = Company::withoutGlobalScopes()->active()->orderBy('company_name')->get();
        $this->roles     = Role::orderBy('name')->get();
        return view('super-admin.users.edit', $this->data);
    }

    public function update(Request $request, int $id)
    {
        $user = User::withoutGlobalScopes()->findOrFail($id);

        $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email,' . $id,
            'company_id' => 'nullable|exists:companies,id',
            'role'       => 'required|string|exists:roles,name',
            'status'     => 'required|in:active,inactive',
        ]);

        $user->update([
            'name'       => $request->name,
            'email'      => $request->email,
            'company_id' => $request->company_id,
            'status'     => $request->status,
        ]);

        if ($request->password) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        $user->syncRoles([$request->role]);

        return Reply::success('User updated successfully.');
    }

    public function destroy(int $id)
    {
        $user = User::withoutGlobalScopes()->findOrFail($id);
        abort_403($user->is_superadmin);
        $user->delete();
        return Reply::success('User deleted.');
    }

    public function toggleStatus(int $id)
    {
        $user = User::withoutGlobalScopes()->findOrFail($id);
        abort_403($user->is_superadmin);
        $user->update(['status' => $user->status === 'active' ? 'inactive' : 'active']);
        return Reply::success('User status updated.');
    }
}
