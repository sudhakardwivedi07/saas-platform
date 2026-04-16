<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

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
        $companyId = auth()->user()->company_id;

        $this->users = User::where('company_id', $companyId)
            ->with('roles')
            ->orderByDesc('created_at')
            ->get();

        return view('users.index', $this->data);
    }

    public function store(Request $request)
    {
        return back()->with('info', 'User invitation coming soon.');
    }

    public function destroy($id)
    {
        return back()->with('success', 'User removed.');
    }
}
