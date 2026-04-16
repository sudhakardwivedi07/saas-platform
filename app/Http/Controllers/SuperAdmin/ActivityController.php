<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\AccountBaseController;

class ActivityController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['auth', 'super-admin']);
        $this->pageTitle = 'Activity Log';
    }

    public function index()
    {
        return view('super-admin.activity.index', $this->data);
    }
}
