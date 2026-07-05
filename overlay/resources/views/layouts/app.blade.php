<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Lucky Arcade')</title>
    <link rel="stylesheet" href="/css/app.css?v=4">
    <script src="/js/app.js?v=4" defer></script>
</head>
<body>
<header class="topbar">
    <a class="brand" href="{{ route('home') }}">Lucky Arcade</a>
    <nav>
        <a href="{{ route('games.index') }}">Games</a>
        <a href="{{ route('leaderboard') }}">Leaderboard</a>
        @auth
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <a href="{{ route('fairness.show') }}">Fairness</a>
            <a href="{{ route('notifications.index') }}">Notifications @php($unread = auth()->user()->userNotifications()->whereNull('read_at')->count()) @if($unread)<span class="nav-badge">{{ $unread }}</span>@endif</a>
            <a href="{{ route('account.show') }}">Account</a>
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

<main class="container">
    @if(session('success'))<div class="alert success">{{ session('success') }}</div>@endif
    @if(session('result'))<div class="alert result">{{ session('result') }}</div>@endif
    @if($errors->any())<div class="alert error"><strong>Please correct the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @yield('content')
</main>
<footer class="footer">Virtual credits only — no deposits, withdrawals or cash value.</footer>
</body>
</html>
