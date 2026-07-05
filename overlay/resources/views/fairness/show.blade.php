@extends('layouts.app')
@section('title', 'Provably fair')
@section('content')
<span class="eyebrow">TRANSPARENCY</span><h1>Provably fair seeds</h1>
<p>The hash is visible before play. Rotate the seed to reveal the old server seed and independently recompute historical results.</p>

<section class="panel">
    <h2>Active seed</h2>
    <dl class="details">
        <dt>Server seed hash</dt><dd><code>{{ $activeSeed->server_seed_hash }}</code></dd>
        <dt>Client seed</dt><dd><code>{{ $activeSeed->client_seed }}</code></dd>
        <dt>Next nonce</dt><dd>{{ $activeSeed->nonce }}</dd>
    </dl>
    <form method="post" action="{{ route('fairness.rotate') }}" class="stack compact">
        @csrf
        <label>Optional new client seed<input type="text" name="client_seed" minlength="8" maxlength="64"></label>
        <button class="button secondary" type="submit">Reveal old seed and rotate</button>
    </form>
</section>

<section class="panel">
    <h2>Revealed seeds</h2>
    <div class="table-wrap"><table>
        <thead><tr><th>Hash</th><th>Revealed server seed</th><th>Final nonce</th><th>Revealed</th></tr></thead>
        <tbody>
        @forelse($oldSeeds as $seed)
            <tr><td><code>{{ $seed->server_seed_hash }}</code></td><td><code>{{ $seed->revealed_server_seed }}</code></td><td>{{ $seed->nonce }}</td><td>{{ optional($seed->revealed_at)->format('Y-m-d H:i') }}</td></tr>
        @empty
            <tr><td colspan="4">No seed has been rotated yet.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</section>
@endsection
