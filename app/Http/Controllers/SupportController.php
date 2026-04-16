<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SupportController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->pageTitle = 'Support';
    }

    public function index()
    {
        return view('support.index', $this->data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject'  => 'required|string|max:255',
            'priority' => 'required|in:low,medium,high',
            'message'  => 'required|string',
        ]);

        return back()->with('success', 'Support ticket submitted. Our team will respond shortly.');
    }
}
