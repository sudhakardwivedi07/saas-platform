<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class FirmEmailTemplateController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
    }

    private function firmScope(): \Illuminate\Database\Eloquent\Builder
    {
        return EmailTemplate::where('firm_id', company()->id)
                            ->whereNotNull('firm_id')
                            ->whereNull('company_id');
    }

    public function show(int $id)
    {
        $template = $this->firmScope()->findOrFail($id);

        return response()->json([
            'status'   => 'success',
            'template' => [
                'id'            => $template->id,
                'name'          => $template->name,
                'slug'          => $template->slug,
                'trigger_event' => $template->trigger_event ?? '',
                'subject'       => $template->subject,
                'body'          => $template->body,
                'is_active'     => (bool) $template->is_active,
            ],
        ]);
    }

    public function store(Request $request)
    {
        abort_403(!auth()->user()->can('add_email_templates'));

        $request->validate([
            'name'          => 'required|string|max:255',
            'trigger_event' => 'nullable|string|max:255',
            'subject'       => 'required|string|max:500',
            'body'          => 'required|string',
            'is_active'     => 'nullable|boolean',
        ]);

        $firmId = company()->id;
        $slug   = \Str::slug($request->name, '_');
        $base   = $slug; $i = 1;
        while (EmailTemplate::where('firm_id', $firmId)->where('slug', $slug)->exists()) {
            $slug = $base . '_' . $i++;
        }

        $template = EmailTemplate::create([
            'firm_id'       => $firmId,
            'name'          => $request->name,
            'slug'          => $slug,
            'trigger_event' => $request->trigger_event,
            'subject'       => $request->subject,
            'body'          => $request->body,
            'is_active'     => $request->boolean('is_active', true),
        ]);

        return Reply::successWithData('Email template created.', [
            'template' => [
                'id'            => $template->id,
                'name'          => $template->name,
                'slug'          => $template->slug,
                'trigger_event' => $template->trigger_event ?? '',
                'subject'       => $template->subject,
                'is_active'     => (bool) $template->is_active,
            ],
        ]);
    }

    public function update(Request $request, int $id)
    {
        abort_403(!auth()->user()->can('edit_email_templates'));

        $template = $this->firmScope()->findOrFail($id);

        $request->validate([
            'name'          => 'required|string|max:255',
            'trigger_event' => 'nullable|string|max:255',
            'subject'       => 'required|string|max:500',
            'body'          => 'required|string',
            'is_active'     => 'nullable|boolean',
        ]);

        $template->update([
            'name'          => $request->name,
            'trigger_event' => $request->trigger_event,
            'subject'       => $request->subject,
            'body'          => $request->body,
            'is_active'     => $request->boolean('is_active', true),
        ]);

        return Reply::successWithData('Email template updated.', [
            'template' => [
                'id'            => $template->id,
                'name'          => $template->name,
                'slug'          => $template->slug,
                'trigger_event' => $template->trigger_event ?? '',
                'subject'       => $template->subject,
                'is_active'     => (bool) $template->is_active,
            ],
        ]);
    }

    public function destroy(int $id)
    {
        abort_403(!auth()->user()->can('edit_email_templates'));

        $this->firmScope()->findOrFail($id)->delete();

        return Reply::success('Email template deleted.');
    }
}
