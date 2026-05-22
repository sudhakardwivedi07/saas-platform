<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\SupportTicket;
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
        $userId    = auth()->id();
        $companyId = company()?->id ?? auth()->user()->company_id;

        $subCompanyIds = Company::where('created_by', $companyId)->pluck('id')->toArray();

        // Tickets submitted by this admin user
        $this->myTickets = SupportTicket::where('user_id', $userId)
            ->with('user', 'company')
            ->orderByDesc('created_at')
            ->get();

        // Tickets from client companies managed by this firm
        $this->clientTickets = SupportTicket::whereIn('company_id', $subCompanyIds)
            ->with('user', 'company')
            ->orderByDesc('created_at')
            ->get();

        return view('support.index', $this->data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject'    => 'required|string|max:255',
            'priority'   => 'required|in:low,medium,high',
            'message'    => 'required|string',
            'attachment' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf,zip',
        ]);

        $companyId = company()?->id ?? auth()->user()->company_id;

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('support-attachments', 'public');
        }

        SupportTicket::create([
            'company_id' => $companyId,
            'user_id'    => auth()->id(),
            'subject'    => $request->subject,
            'priority'   => $request->priority,
            'message'    => $request->message,
            'attachment' => $attachmentPath,
            'status'     => 'open',
        ]);

        return back()->with('ticket_success', true);
    }

    public function show(SupportTicket $ticket)
    {
        $this->authorizeTicket($ticket);

        $attachmentName = null;
        $attachmentUrl  = null;
        if ($ticket->attachment) {
            $attachmentName = basename($ticket->attachment);
            $attachmentUrl  = asset('storage/' . $ticket->attachment);
        }

        return response()->json([
            'id'              => $ticket->id,
            'ticket_id'       => 'TK-' . str_pad($ticket->id, 4, '0', STR_PAD_LEFT),
            'subject'         => $ticket->subject,
            'priority'        => $ticket->priority,
            'status'          => $ticket->status,
            'message'         => $ticket->message,
            'notes'           => $ticket->notes ?? '',
            'attachment_name' => $attachmentName,
            'attachment_url'  => $attachmentUrl,
            'created_at'      => $ticket->created_at->format('M j, Y g:i A'),
            'submitted_by'    => $ticket->user?->name ?? 'Unknown',
            'company_name'    => $ticket->company?->company_name ?? '',
            'update_url'      => route('support.update', $ticket->id),
            'notes_url'       => route('support.notes', $ticket->id),
        ]);
    }

    public function update(Request $request, SupportTicket $ticket)
    {
        $this->authorizeTicket($ticket);

        $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
            'note'   => 'nullable|string|max:1000',
        ]);

        $ticket->status = $request->status;

        if ($request->filled('note')) {
            $entry = '[' . now()->format('M j, Y g:i A') . '] Status → ' . $request->status . ': ' . $request->note;
            $ticket->notes = $ticket->notes ? $ticket->notes . "\n\n" . $entry : $entry;
        }

        $ticket->save();

        return response()->json([
            'success'   => true,
            'status'    => $ticket->status,
            'ticket_id' => 'TK-' . str_pad($ticket->id, 4, '0', STR_PAD_LEFT),
        ]);
    }

    public function updateNotes(Request $request, SupportTicket $ticket)
    {
        $this->authorizeTicket($ticket);

        $request->validate(['notes' => 'nullable|string']);

        $ticket->update(['notes' => $request->notes]);

        return response()->json(['success' => true]);
    }

    private function authorizeTicket(SupportTicket $ticket): void
    {
        $companyId     = company()?->id ?? auth()->user()->company_id;
        $subCompanyIds = Company::where('created_by', $companyId)->pluck('id')->toArray();

        $allowed = $ticket->company_id === $companyId
            || $ticket->user_id === auth()->id()
            || in_array($ticket->company_id, $subCompanyIds);

        abort_if(!$allowed, 403);
    }
}
