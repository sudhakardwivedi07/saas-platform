<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['auth', 'super-admin']);
        $this->pageTitle = 'Email Templates';
    }

    public function index()
    {
        $this->templates = EmailTemplate::whereNull('company_id')->orderBy('slug')->get();
        return view('super-admin.email-templates.index', $this->data);
    }

    public function edit(string $slug)
    {
        $this->template = EmailTemplate::whereNull('company_id')->where('slug', $slug)->firstOrFail();
        return view('super-admin.email-templates.edit', $this->data);
    }

    public function update(Request $request, string $slug)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'body'    => 'required|string',
        ]);

        $template = EmailTemplate::whereNull('company_id')->where('slug', $slug)->firstOrFail();
        $template->update(['subject' => $request->subject, 'body' => $request->body]);

        return Reply::success('Template updated.');
    }
}
