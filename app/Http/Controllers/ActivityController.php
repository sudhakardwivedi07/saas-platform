<?php

namespace App\Http\Controllers;

class ActivityController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->pageTitle = 'Activity Log';
    }

    public function index()
    {
        abort_403(!auth()->user()->hasAnyRole(['client_admin', 'manager']));

        return view('activity.index', $this->data);
    }
}
