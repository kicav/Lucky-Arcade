@extends('layouts.app')
@section('title', 'Daily missions')
@section('content')
<div class="page-head">
    <div><span class="eyebrow">DAILY MISSIONS</span><h1>Fresh goals every day</h1><p class="hint">Progress resets at midnight in the application timezone.</p></div>
    <div class="balance"><span>Balance</span><strong>{{ number_format($wallet->balance) }}</strong></div>
</div>
<div class="mission-grid">
@foreach($missions as $mission)
    @php($percent = min(100, (int) floor(($mission->progress / max(1, $mission->target)) * 100)))
    <article class="mission-card {{ $mission->claimed_at ? 'claimed' : ($mission->isComplete() ? 'complete' : '') }}">
        <div class="mission-copy">
            <span class="eyebrow">+{{ number_format($mission->reward) }} CREDITS</span>
            <h2>{{ $mission->title }}</h2>
            <p>{{ $mission->description }}</p>
            <div class="progress-track"><span style="width:{{ $percent }}%"></span></div>
            <small>{{ number_format($mission->progress) }} / {{ number_format($mission->target) }}</small>
        </div>
        <div class="mission-action">
            @if($mission->claimed_at)
                <span class="status-pill">Claimed</span>
            @elseif($mission->isComplete())
                <form method="post" action="{{ route('missions.claim', $mission) }}">@csrf<button class="button" type="submit">Claim reward</button></form>
            @else
                <span class="status-pill status-muted">In progress</span>
            @endif
        </div>
    </article>
@endforeach
</div>
@endsection
