<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->pageTitle = 'My Profile';
    }

    public function index()
    {
        $this->profileUser = auth()->user();
        return view('profile.index', $this->data);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name'   => 'required|string|max:255',
            'mobile' => 'nullable|string|max:50',
            'locale' => 'nullable|string|max:10',
            'image'  => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $path       = $request->file('image')->store('avatars/' . $user->id, 'public');
            $user->image = $path;
        }

        $user->fill($request->only('name', 'mobile', 'locale'));
        $user->dark_theme = $request->boolean('dark_theme');
        $user->save();

        return Reply::success('Profile updated.');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return Reply::error('Current password is incorrect.');
        }

        $user->update(['password' => Hash::make($request->password)]);

        return Reply::success('Password changed successfully.');
    }
}
