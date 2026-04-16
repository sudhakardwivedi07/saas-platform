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
        return view('activity.index', $this->data);
    }
}
