<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\UserNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function index(Request $request): View
    {
        $query = SupportTicket::query()->with('user')->withCount('messages')->latest('updated_at');
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return view('admin.support.index', ['tickets' => $query->paginate(20)->withQueryString()]);
    }

    public function show(SupportTicket $ticket): View
    {
        return view('admin.support.show', ['ticket' => $ticket->load(['user', 'messages.user'])]);
    }

    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $data = $request->validate(['message' => ['required', 'string', 'min:2', 'max:4000']]);
        SupportMessage::query()->create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'is_admin' => true,
            'body' => $data['message'],
        ]);
        $ticket->update(['status' => 'pending', 'last_replied_at' => now(), 'closed_at' => null]);

        UserNotification::query()->create([
            'user_id' => $ticket->user_id,
            'type' => 'support_reply',
            'title' => 'Support replied to your ticket',
            'message' => "A support agent replied to ticket #{$ticket->id}: {$ticket->subject}",
            'data' => ['ticket_id' => $ticket->id],
        ]);
        $this->audit($request, $ticket, 'support_ticket.replied');

        return back()->with('success', 'Reply sent to player.');
    }

    public function status(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:open,pending,closed']]);
        $ticket->update([
            'status' => $data['status'],
            'closed_at' => $data['status'] === 'closed' ? now() : null,
        ]);

        UserNotification::query()->create([
            'user_id' => $ticket->user_id,
            'type' => 'support_status',
            'title' => 'Support ticket status updated',
            'message' => "Ticket #{$ticket->id} is now {$data['status']}.",
            'data' => ['ticket_id' => $ticket->id, 'status' => $data['status']],
        ]);
        $this->audit($request, $ticket, 'support_ticket.status_updated');

        return back()->with('success', 'Ticket status updated.');
    }

    private function audit(Request $request, SupportTicket $ticket, string $action): void
    {
        AuditLog::query()->create([
            'actor_id' => $request->user()->id,
            'action' => $action,
            'subject_type' => SupportTicket::class,
            'subject_id' => $ticket->id,
            'before' => null,
            'after' => ['status' => $ticket->status],
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'created_at' => now(),
        ]);
    }
}
