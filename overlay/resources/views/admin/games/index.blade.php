@extends('layouts.app')
@section('title', 'Manage games')
@section('content')
<span class="eyebrow">ADMIN</span><h1>Game settings</h1>
<p>Administrators can configure availability and limits, but cannot edit outcomes.</p>
<div class="grid two">
@foreach($games as $game)
<section class="panel">
    <h2>{{ $game->name }}</h2>
    <form method="post" action="{{ route('admin.games.update', $game) }}" class="stack">
        @csrf @method('PUT')
        <label class="check"><input type="checkbox" name="enabled" value="1" @checked($game->enabled)> Enabled</label>
        <label>Minimum stake<input type="number" name="min_bet" min="1" value="{{ $game->min_bet }}" required></label>
        <label>Maximum stake<input type="number" name="max_bet" min="1" value="{{ $game->max_bet }}" required></label>
        <button class="button" type="submit">Save settings</button>
    </form>
</section>
@endforeach
</div>
@endsection
