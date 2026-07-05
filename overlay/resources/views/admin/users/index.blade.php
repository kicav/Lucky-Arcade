@extends('layouts.app')
@section('title', 'Users')
@section('content')
<span class="eyebrow">ADMIN</span><h1>Players</h1>
<section class="panel"><div class="table-wrap"><table>
<thead><tr><th>Name</th><th>Email</th><th>Balance</th><th>Plays</th><th>Created</th></tr></thead>
<tbody>@foreach($users as $user)<tr><td>{{ $user->name }}</td><td>{{ $user->email }}</td><td>{{ number_format($user->wallet?->balance ?? 0) }}</td><td>{{ $user->game_entries_count }}</td><td>{{ $user->created_at->format('Y-m-d') }}</td></tr>@endforeach</tbody>
</table></div>{{ $users->links() }}</section>
@endsection
