@extends('layouts.app')
@section('title', 'Achievements')
@section('content')
<div class="page-head"><div><span class="eyebrow">PLAYER PROGRESSION</span><h1>Achievements</h1></div><div class="balance"><span>Unlocked</span><strong>{{ $unlocked->count() }}/{{ count($catalog) }}</strong></div></div>
<div class="achievement-grid">
@foreach($catalog as $code => $achievement)
    @php($record = $unlocked->get($code))
    @php($current = min($achievement['target'], $progress[$achievement['metric']] ?? 0))
    @php($percent = (int) floor(($current / max(1, $achievement['target'])) * 100))
    <article class="achievement-card {{ $record ? 'unlocked' : '' }}">
        <div class="achievement-icon">{{ $record ? '✓' : '◇' }}</div>
        <div class="achievement-copy"><span class="eyebrow">{{ $record ? 'UNLOCKED' : 'IN PROGRESS' }}</span><h2>{{ $achievement['title'] }}</h2><p>{{ $achievement['description'] }}</p><div class="progress-track"><span style="width: {{ $percent }}%"></span></div><small>{{ number_format($current) }} / {{ number_format($achievement['target']) }}</small></div>
        <div class="achievement-reward"><strong>+{{ number_format($achievement['reward']) }}</strong><span>credits</span>@if($record)<time>{{ $record->unlocked_at->format('Y-m-d') }}</time>@endif</div>
    </article>
@endforeach
</div>
@endsection
