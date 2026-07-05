@extends('layouts.app')
@section('title', 'Announcements')
@section('content')
<div class="page-head"><div><span class="eyebrow">ADMINISTRATION</span><h1>Announcements</h1></div><a class="button secondary" href="{{ route('admin.dashboard') }}">Back to admin</a></div>
<section class="panel"><h2>Create announcement</h2>
<form method="post" action="{{ route('admin.announcements.store') }}" class="stack">@csrf
<label>Title<input name="title" maxlength="120" required></label>
<label>Message<textarea name="body" rows="4" maxlength="1000" required></textarea></label>
<div class="grid two"><label>Starts at (optional)<input type="datetime-local" name="starts_at"></label><label>Ends at (optional)<input type="datetime-local" name="ends_at"></label></div>
<label class="check"><input type="checkbox" name="active" value="1" checked> Active</label>
<button class="button" type="submit">Publish announcement</button>
</form></section>
<div class="announcement-admin-list">
@forelse($announcements as $announcement)
<section class="panel">
<form method="post" action="{{ route('admin.announcements.update', $announcement) }}" class="stack">@csrf @method('put')
<div class="section-head"><h2>#{{ $announcement->id }} · {{ $announcement->title }}</h2><span class="status-pill {{ $announcement->active ? '' : 'status-muted' }}">{{ $announcement->active ? 'Active' : 'Inactive' }}</span></div>
<label>Title<input name="title" value="{{ $announcement->title }}" maxlength="120" required></label>
<label>Message<textarea name="body" rows="3" maxlength="1000" required>{{ $announcement->body }}</textarea></label>
<div class="grid two"><label>Starts at<input type="datetime-local" name="starts_at" value="{{ $announcement->starts_at?->format('Y-m-d\\TH:i') }}"></label><label>Ends at<input type="datetime-local" name="ends_at" value="{{ $announcement->ends_at?->format('Y-m-d\\TH:i') }}"></label></div>
<label class="check"><input type="checkbox" name="active" value="1" @checked($announcement->active)> Active</label>
<button class="button" type="submit">Save</button>
</form>
<form method="post" action="{{ route('admin.announcements.destroy', $announcement) }}" class="delete-row" onsubmit="return confirm('Delete this announcement?')">@csrf @method('delete')<button class="button danger" type="submit">Delete</button></form>
<small class="hint">Created by {{ $announcement->creator?->email ?? 'system' }} · {{ $announcement->created_at->format('Y-m-d H:i') }}</small>
</section>
@empty<div class="panel">No announcements yet.</div>@endforelse
</div>
@endsection
