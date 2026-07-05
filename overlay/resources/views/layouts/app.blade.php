<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Lucky Arcade')</title>
    <link rel="stylesheet" href="/css/app.css?v=10">
    <script src="/js/app.js?v=10" defer></script>
</head>
<body @auth data-live-feed-url="{{ route('live.feed') }}" data-live-poll-ms="{{ config('live.poll_ms', 4000) }}" @endauth>
<header class="topbar">
    <a class="brand" href="{{ route('home') }}">Lucky Arcade</a>
    <nav>
        <a href="{{ route('games.index') }}">Games</a>
        <a href="{{ route('leaderboard') }}">Leaderboard</a>
        @auth
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <a href="{{ route('fairness.show') }}">Fairness</a>
            <a href="{{ route('missions.index') }}">Missions</a>
            <a href="{{ route('league.index') }}">League</a>
            <a href="{{ route('promos.index') }}">Promos</a>
            <a href="{{ route('stats.index') }}">Stats</a>
            <a href="{{ route('achievements.index') }}">Achievements</a>
            <a href="{{ route('referrals.index') }}">Referrals</a>
            <a href="{{ route('notifications.index') }}">Notifications @php($unread = auth()->user()->userNotifications()->whereNull('read_at')->count()) <span id="notification-nav-badge" class="nav-badge {{ $unread ? '' : 'is-hidden' }}">{{ $unread }}</span></a>
            <a href="{{ route('support.index') }}">Support</a>
            <a href="{{ route('account.show') }}">Account</a>
            <a href="{{ route('security.show') }}">Security @if(auth()->user()->hasTwoFactorEnabled())<span class="nav-badge secure">2FA</span>@endif</a>
            @if(auth()->user()->is_admin)
                <a href="{{ route('admin.dashboard') }}">Admin</a>
            @endif
            <form method="post" action="{{ route('logout') }}" class="inline-form">@csrf<button class="link-button" type="submit">Logout</button></form>
        @else
            <a href="{{ route('login') }}">Login</a>
            <a href="{{ route('register') }}">Register</a>
        @endauth
    </nav>
</header>

@inject('announcementService', 'App\Services\AnnouncementService')
@php($siteAnnouncements = $announcementService->active())
<main class="container">
    @foreach($siteAnnouncements as $siteAnnouncement)
        <aside class="site-announcement"><div><strong>{{ $siteAnnouncement->title }}</strong><p>{{ $siteAnnouncement->body }}</p></div><span>Announcement</span></aside>
    @endforeach
    @if(session('success'))<div class="alert success">{{ session('success') }}</div>@endif
    @if(session('result'))<div class="alert result">{{ session('result') }}</div>@endif
    @if($errors->any())<div class="alert error"><strong>Please correct the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @yield('content')
</main>
<div id="live-toast-region" class="live-toast-region" aria-live="polite" aria-atomic="false"></div>
<footer class="footer">Virtual credits only — no deposits, withdrawals or cash value.</footer>
</body>
</html>
