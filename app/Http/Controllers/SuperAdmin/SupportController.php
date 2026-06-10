<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\AccountBaseController;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SupportController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['auth', 'super-admin']);
        $this->pageTitle = 'Support';
    }

    public function index()
    {
        $dateFmt = active_date_format();

        $this->tickets = SupportTicket::with(['company', 'user'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($t) => [
                'raw_id'      => $t->id,
                'id'          => 'TK-' . str_pad($t->id, 4, '0', STR_PAD_LEFT),
                'firm'        => $t->company?->company_name ?? 'Unknown Firm',
                'subject'     => $t->subject,
                'priority'    => $t->priority ?? 'low',
                'status'      => $t->status ?? 'open',
                'raised_on'   => $t->created_at->format($dateFmt),
                'raised_time' => $t->created_at->format('g:i A'),
                'date_sort'   => $t->created_at->timestamp,
                'date_iso'    => $t->created_at->format('Y-m-d'),
                'assigned_to' => $t->assigned_to,
            ]);

        $this->adminUsers = User::where('is_superadmin', true)->get();

        return view('super-admin.support.index', $this->data);
    }

    public function show(SupportTicket $ticket)
    {
        $ticket->load('company');

        $attachments = [];
        if ($ticket->attachment) {
            $path = $ticket->attachment;
            $name = basename($path);
            $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $size = null;
            if (Storage::disk('public')->exists($path)) {
                $bytes = Storage::disk('public')->size($path);
                $size  = $bytes < 1024 * 1024
                    ? round($bytes / 1024, 1) . ' KB'
                    : round($bytes / (1024 * 1024), 1) . ' MB';
            }
            $attachments[] = [
                'name' => $name,
                'url'  => asset('storage/' . $path),
                'ext'  => $ext,
                'size' => $size,
            ];
        }

        $this->ticket      = $ticket;
        $this->ticketId    = 'TK-' . str_pad($ticket->id, 4, '0', STR_PAD_LEFT);
        $this->attachments = $attachments;

        return view('super-admin.support.show', $this->data);
    }

    public function updateStatus(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,on_hold,sla_breach,resolved,closed',
            'note'   => 'nullable|string|max:1000',
        ]);

        $ticket->status = $request->status;

        if ($request->filled('note')) {
            $entry = '[' . now()->format('M j, Y g:i A') . '] Status → ' . $request->status . ': ' . $request->note;
            $ticket->notes = $ticket->notes ? $ticket->notes . "\n\n" . $entry : $entry;
        }

        $ticket->save();

        return response()->json([
            'success' => true,
            'status'  => $ticket->status,
        ]);
    }

    public function assign(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'assigned_to' => 'required|string|max:255',
        ]);

        $ticket->update(['assigned_to' => $request->assigned_to]);

        return response()->json([
            'success'     => true,
            'assigned_to' => $ticket->assigned_to,
        ]);
    }

    public function saveNotes(Request $request, SupportTicket $ticket)
    {
        $request->validate(['notes' => 'nullable|string']);

        $ticket->update(['notes' => $request->notes]);

        return response()->json(['success' => true]);
    }
}
