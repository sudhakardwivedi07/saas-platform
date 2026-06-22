<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EmailTemplateController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['auth', 'super-admin']);
        $this->pageTitle = 'Email Templates';
    }

    private function globalScope(): \Illuminate\Database\Eloquent\Builder
    {
        return EmailTemplate::whereNull('company_id');
    }

    public function index()
    {
        $this->templates = $this->globalScope()->orderBy('name')->get();
        return view('super-admin.email-templates.index', $this->data);
    }

    public function create()
    {
        return view('super-admin.email-templates.create', $this->data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'subject'       => 'required|string|max:255',
            'body'          => 'required|string',
            'from_name'     => 'nullable|string|max:255',
            'from_email'    => 'nullable|email|max:255',
        ]);

        $slug = Str::slug($request->name, '_');

        if ($this->globalScope()->where('slug', $slug)->exists()) {
            $slug = $slug . '_' . time();
        }

        EmailTemplate::create([
            'company_id'    => null,
            'name'          => $request->name,
            'slug'          => $slug,
            'subject'       => $request->subject,
            'body'          => $request->body,
            'from_name'     => $request->from_name,
            'from_email'    => $request->from_email,
            'is_active'     => true,
        ]);

        return Reply::successWithData('Template created successfully.', [
            'redirect' => route('superadmin.email-templates.index'),
        ]);
    }

    public function edit(string $slug)
    {
        $this->template = $this->globalScope()->where('slug', $slug)->firstOrFail();
        return view('super-admin.email-templates.edit', $this->data);
    }

    public function update(Request $request, string $slug)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'subject'       => 'required|string|max:255',
            'body'          => 'required|string',
            'from_name'     => 'nullable|string|max:255',
            'from_email'    => 'nullable|email|max:255',
            'is_active'     => 'nullable|boolean',
        ]);

        $template = $this->globalScope()->where('slug', $slug)->firstOrFail();

        $newSlug = Str::slug($request->name, '_');
        if ($this->globalScope()->where('slug', $newSlug)->where('id', '!=', $template->id)->exists()) {
            $newSlug = $newSlug . '_' . time();
        }

        $template->update([
            'name'          => $request->name,
            'slug'          => $newSlug,
            'subject'       => $request->subject,
            'body'          => $request->body,
            'from_name'     => $request->from_name,
            'from_email'    => $request->from_email,
            'is_active'     => $request->boolean('is_active', true),
        ]);

        return Reply::successWithData('Template updated.', [
            'redirect' => route('superadmin.email-templates.index'),
        ]);
    }

    public function destroy(string $slug)
    {
        $template = $this->globalScope()->where('slug', $slug)->firstOrFail();
        $template->delete();

        return Reply::success('Template deleted.');
    }
}
