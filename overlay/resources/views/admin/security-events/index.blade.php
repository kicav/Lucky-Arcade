@extends('layouts.app')
@section('title', 'Security events')
@section('content')
<div class="page-head"><div><span class="eyebrow">ADMIN · SECURITY</span><h1>Security events</h1></div></div>
<section class="panel">
<form method="get" class="filter-grid security-filter">
    <label>User email<input type="search" name="email" value="{{ request('email') }}"></label>
    <label>Event contains<input type="search" name="event" value="{{ request('event') }}" placeholder="login.failed"></label>
    <label>Exact IP<input type="search" name="ip" value="{{ request('ip') }}"></label>
    <div class="filter-action"><button class="button secondary" type="submit">Filter</button></div>
</form>
<div class="table-wrap"><table>
<thead><tr><th>Time</th><th>User</th><th>Event</th><th>IP</th><th>Metadata</th></tr></thead>
<tbody>
@forelse($events as $event)
<tr><td>{{ $event->created_at->format('Y-m-d H:i:s') }}</td><td>{{ $event->user?->email ?: 'Unknown' }}</td><td><code>{{ $event->event }}</code></td><td>{{ $event->ip_address ?: '—' }}</td><td><code>{{ $event->metadata ? json_encode($event->metadata) : '—' }}</code></td></tr>
@empty<tr><td colspan="5">No security events match the filters.</td></tr>@endforelse
</tbody></table></div>
{{ $events->links() }}
</section>
@endsection
