@extends('layouts.app')
@section('title', 'Notifications')
@section('content')
<div class="page-head">
    <div><span class="eyebrow">INBOX</span><h1>Notifications</h1></div>
    <form method="post" action="{{ route('notifications.read-all') }}">@csrf<button class="button secondary" type="submit">Mark all read</button></form>
</div>
<section class="notification-list">
@forelse($notifications as $notification)
    <article class="notification-item {{ $notification->read_at ? '' : 'unread' }}">
        <div>
            <span class="notification-dot"></span>
            <strong>{{ $notification->title }}</strong>
            <p>{{ $notification->message }}</p>
        </div>
        <time>{{ $notification->created_at->format('Y-m-d H:i') }}</time>
    </article>
@empty
    <section class="panel"><p class="empty-note">No notifications yet.</p></section>
@endforelse
</section>
<div class="pagination">{{ $notifications->links() }}</div>
@endsection
