<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Jobs\SendEmailJob;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->pageTitle = 'Email Templates';
    }

    public function index()
    {
        $this->templates = EmailTemplate::where(function ($q) {
            $q->where('company_id', company()->id)->orWhereNull('company_id');
        })
        ->orderByRaw('company_id IS NULL DESC')
        ->get()
        ->groupBy('slug');

        return view('email-templates.index', $this->data);
    }

    public function create()
    {
        $this->pageTitle = 'New Email Template';
        return view('email-templates.create', $this->data);
    }

    public function store(Request $request)
    {
        abort_403(!auth()->user()->can('add_email_templates'));

        $request->validate([
            'slug'       => 'required|string|max:100|regex:/^[a-z0-9_]+$/|unique:email_templates,slug,NULL,id,company_id,' . company()->id,
            'name'       => 'required|string|max:255',
            'subject'    => 'required|string|max:500',
            'body'       => 'required|string',
            'from_name'  => 'nullable|string|max:255',
            'from_email' => 'nullable|email|max:255',
        ]);

        EmailTemplate::create([
            'company_id' => company()->id,
            'slug'       => $request->slug,
            'name'       => $request->name,
            'subject'    => $request->subject,
            'body'       => $request->body,
            'from_name'  => $request->from_name,
            'from_email' => $request->from_email,
            'is_active'  => true,
        ]);

        return Reply::successWithData('Email template created.', ['redirect' => route('email-templates.index')]);
    }

    public function edit(string $slug)
    {
        abort_403(!auth()->user()->can('edit_email_templates'));

        $this->template = EmailTemplate::resolveFor($slug, company()->id);
        $this->slug     = $slug;

        return view('email-templates.edit', $this->data);
    }

    public function update(Request $request, string $slug)
    {
        abort_403(!auth()->user()->can('edit_email_templates'));

        $request->validate([
            'subject' => 'required|string|max:500',
            'body'    => 'required|string',
        ]);

        EmailTemplate::updateOrCreate(
            ['company_id' => company()->id, 'slug' => $slug],
            [
                'name'       => $request->name ?? $slug,
                'subject'    => $request->subject,
                'body'       => $request->body,
                'from_name'  => $request->from_name,
                'from_email' => $request->from_email,
                'cc'         => $request->cc,
                'bcc'        => $request->bcc,
                'is_active'  => true,
            ]
        );

        return Reply::success('Email template saved.');
    }

    public function sendTest(Request $request, string $slug)
    {
        $request->validate(['to_email' => 'required|email']);

        $company = company();

        SendEmailJob::dispatch(
            toEmail:      $request->to_email,
            templateSlug: $slug,
            placeholders: [
                '{{name}}'      => 'Test User',
                '{{email}}'     => $request->to_email,
                '{{app_name}}' => $company->app_name ?? $company->company_name,
                '{{login_url}}' => tenant_url($company->subdomain, 'login'),
            ],
            companyId: $company->id
        )->onQueue('emails');

        return Reply::success("Test email dispatched to {$request->to_email}");
    }

    public function destroy(string $slug)
    {
        $template = EmailTemplate::where('company_id', company()->id)->where('slug', $slug)->firstOrFail();
        $template->delete();
        return back()->with('success', 'Custom template removed. Global default restored.');
    }
}
