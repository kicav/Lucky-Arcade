@extends('layouts.app')
@section('title', 'Support ticket #'.$ticket->id)
@section('content')
<div class="page-head"><div><span class="eyebrow">SUPPORT TICKET #{{ $ticket->id }}</span><h1>{{ $ticket->subject }}</h1><p class="hint">{{ ucfirst($ticket->category) }} · {{ ucfirst($ticket->priority) }} priority</p></div><span class="status-pill status-{{ $ticket->status }}">{{ ucfirst($ticket->status) }}</span></div>
<section class="ticket-thread">@foreach($ticket->messages as $message)<article class="ticket-message {{ $message->is_admin ? 'from-admin' : 'from-player' }}"><div class="section-head"><strong>{{ $message->is_admin ? 'Lucky Arcade Support' : ($message->user?->name ?? 'Player') }}</strong><time>{{ $message->created_at->format('Y-m-d H:i') }}</time></div><p>{{ $message->body }}</p></article>@endforeach</section>
@if(!$ticket->isClosed())<section class="panel"><form method="post" action="{{ route('support.reply', $ticket) }}" class="stack">@csrf<label>Reply<textarea name="message" rows="5" required></textarea></label><div class="dashboard-actions"><button class="button" type="submit">Send reply</button></div></form><form method="post" action="{{ route('support.close', $ticket) }}">@csrf<button class="button secondary" type="submit">Close ticket</button></form></section>@else<div class="alert result">This ticket is closed.</div>@endif
<a class="text-link" href="{{ route('support.index') }}">← Back to tickets</a>
@endsection
