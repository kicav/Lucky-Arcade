@extends('layouts.app')
@section('title', 'Users')
@section('content')
<div class="page-head"><div><span class="eyebrow">ADMIN</span><h1>Players</h1></div></div>
<section class="panel">
<form method="get" class="filter-grid users-filter"><label>Search<input type="search" name="q" value="{{ request('q') }}" placeholder="Name or email"></label><div class="filter-action"><button class="button secondary" type="submit">Filter</button></div></form>
<div class="table-wrap"><table>
<thead><tr><th>Name</th><th>Email</th><th>Status</th><th>Balance</th><th>Plays</th><th>Created</th><th></th></tr></thead>
<tbody>@foreach($users as $user)<tr><td>{{ $user->name }}</td><td>{{ $user->email }}</td><td><span class="status-pill {{ $user->suspended_at ? 'status-danger' : '' }}">{{ $user->suspended_at ? 'Suspended' : 'Active' }}</span></td><td>{{ number_format($user->wallet?->balance ?? 0) }}</td><td>{{ $user->game_entries_count }}</td><td>{{ $user->created_at->format('Y-m-d') }}</td><td><a class="button secondary small" href="{{ route('admin.users.show', $user) }}">Manage</a></td></tr>@endforeach</tbody>
</table></div>{{ $users->links() }}</section>
@endsection
