@extends('layouts.app')
@section('title', 'System health')
@section('content')
<div class="page-head">
    <div><span class="eyebrow">OPERATIONS</span><h1>System health</h1></div>
    <form method="post" action="{{ route('admin.system.backup') }}">@csrf<button class="button" type="submit">Create SQLite backup</button></form>
</div>
<div class="grid stats six">
    <div class="stat"><span>PHP</span><strong>{{ PHP_VERSION }}</strong></div>
    <div class="stat"><span>Laravel</span><strong>{{ app()->version() }}</strong></div>
    <div class="stat"><span>Database</span><strong class="{{ $databaseStatus === 'OK' ? 'positive' : 'negative' }}">{{ $databaseStatus === 'OK' ? 'OK' : 'Error' }}</strong></div>
    <div class="stat"><span>Storage</span><strong class="{{ $storageWritable ? 'positive' : 'negative' }}">{{ $storageWritable ? 'Writable' : 'Blocked' }}</strong></div>
    <div class="stat"><span>Security events 24h</span><strong>{{ number_format($securityEventCount24h) }}</strong></div>
    <div class="stat"><span>Failed logins 24h</span><strong class="{{ $failedLoginCount24h ? 'negative' : 'positive' }}">{{ number_format($failedLoginCount24h) }}</strong></div>
</div>
<section class="panel">
    <h2>Runtime configuration</h2>
    <dl class="details">
        <dt>Environment</dt><dd>{{ app()->environment() }}</dd>
        <dt>Database driver</dt><dd>{{ config('database.default') }}</dd>
        <dt>Database path</dt><dd><code>{{ $databasePath ?: 'Managed database connection' }}</code></dd>
        <dt>Database size</dt><dd>{{ $databaseSize !== null ? number_format($databaseSize / 1024, 1).' KB' : '—' }}</dd>
        <dt>Cache store</dt><dd>{{ config('cache.default') }}</dd>
        <dt>Queue connection</dt><dd>{{ config('queue.default') }}</dd>
        <dt>Application URL</dt><dd><code>{{ config('app.url') }}</code></dd>
    </dl>
</section>
<section class="panel">
    <h2>Recent backups</h2>
    <div class="table-wrap"><table><thead><tr><th>File</th><th>Size</th><th>Created</th></tr></thead><tbody>
    @forelse($backups as $backup)
        <tr><td><code>{{ $backup->getFilename() }}</code></td><td>{{ number_format($backup->getSize() / 1024, 1) }} KB</td><td>{{ date('Y-m-d H:i:s', $backup->getMTime()) }}</td></tr>
    @empty
        <tr><td colspan="3">No operational backups yet.</td></tr>
    @endforelse
    </tbody></table></div>
</section>
@endsection
