<?php

namespace App\Http\Controllers;

use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function index(Request $request): View
    {
        return view('support.index', [
            'tickets' => SupportTicket::query()
                ->where('user_id', $request->user()->id)
                ->withCount('messages')
                ->latest('updated_at')
                ->paginate(15),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:160'],
            'category' => ['required', 'in:general,account,game,fairness,technical'],
            'priority' => ['required', 'in:low,normal,high'],
            'message' => ['required', 'string', 'min:5', 'max:4000'],
        ]);

        $ticket = DB::transaction(function () use ($request, $data): SupportTicket {
            $ticket = SupportTicket::query()->create([
                'user_id' => $request->user()->id,
                'subject' => $data['subject'],
                'category' => $data['category'],
                'priority' => $data['priority'],
                'status' => 'open',
                'last_replied_at' => now(),
            ]);

            $ticket->messages()->create([
                'user_id' => $request->user()->id,
                'is_admin' => false,
                'body' => $data['message'],
            ]);

            return $ticket;
        });

        return redirect()->route('support.show', $ticket)->with('success', 'Support ticket created.');
    }

    public function show(Request $request, SupportTicket $ticket): View
    {
        $this->authorizeTicket($request, $ticket);

        return view('support.show', ['ticket' => $ticket->load(['messages.user'])]);
    }

    public function messages(Request $request, SupportTicket $ticket): JsonResponse
    {
        $this->authorizeTicket($request, $ticket);
        $after = max(0, (int) $request->integer('after', 0));
        $messages = $ticket->messages()->with('user')->where('id', '>', $after)->limit(100)->get();

        return response()->json([
            'messages' => $messages->map(fn (SupportMessage $message): array => $this->messagePayload($message))->values(),
            'ticket_status' => $ticket->fresh()->status,
            'next_after' => (int) ($messages->last()?->id ?? $after),
        ]);
    }

    public function reply(Request $request, SupportTicket $ticket): JsonResponse|RedirectResponse
    {
        $this->authorizeTicket($request, $ticket);
        if ($ticket->isClosed()) {
            throw ValidationException::withMessages(['message' => 'This support ticket is closed.']);
        }

        $data = $request->validate(['message' => ['required', 'string', 'min:2', 'max:4000']]);
        $message = SupportMessage::query()->create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'is_admin' => false,
            'body' => $data['message'],
        ]);
        $ticket->update(['status' => 'open', 'last_replied_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->messagePayload($message->load('user')),
                'ticket_status' => 'open',
            ], 201);
        }

        return back()->with('success', 'Reply sent.');
    }

    public function close(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $this->authorizeTicket($request, $ticket);
        $ticket->update(['status' => 'closed', 'closed_at' => now()]);

        return back()->with('success', 'Ticket closed.');
    }

    /** @return array<string, mixed> */
    private function messagePayload(SupportMessage $message): array
    {
        return [
            'id' => $message->id,
            'is_admin' => $message->is_admin,
            'author' => $message->is_admin ? 'Lucky Arcade Support' : ($message->user?->name ?? 'Player'),
            'body' => $message->body,
            'created_at' => $message->created_at?->toIso8601String(),
            'created_label' => $message->created_at?->format('Y-m-d H:i'),
        ];
    }

    private function authorizeTicket(Request $request, SupportTicket $ticket): void
    {
        abort_unless($ticket->user_id === $request->user()->id, 404);
    }
}
