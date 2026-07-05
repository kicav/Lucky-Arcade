@extends('layouts.app')
@section('title', 'System operations')
@section('content')
<div class="page-head">
    <div><span class="eyebrow">PRODUCTION OPERATIONS</span><h1>System health & jobs</h1></div>
    <a class="button secondary" href="{{ route('admin.dashboard') }}">Admin overview</a>
</div>
<div class="grid stats six">
    <div class="stat"><span>PHP</span><strong>{{ PHP_VERSION }}</strong></div>
    <div class="stat"><span>Laravel</span><strong>{{ app()->version() }}</strong></div>
    <div class="stat"><span>Database</span><strong class="{{ $databaseStatus === 'OK' ? 'positive' : 'negative' }}">{{ $databaseStatus === 'OK' ? 'OK' : 'Error' }}</strong></div>
    <div class="stat"><span>Pending jobs</span><strong class="{{ ($pendingJobs ?? 0) > 100 ? 'negative' : '' }}">{{ $pendingJobs === null ? '—' : number_format($pendingJobs) }}</strong></div>
    <div class="stat"><span>Failed jobs</span><strong class="{{ ($failedJobs ?? 0) > 0 ? 'negative' : 'positive' }}">{{ $failedJobs === null ? '—' : number_format($failedJobs) }}</strong></div>
    <div class="stat"><span>Latest metric</span><strong>{{ $latestMetricAt ? \Illuminate\Support\Carbon::parse($latestMetricAt)->diffForHumans() : 'Not built' }}</strong></div>
</div>

<section class="panel">
    <div class="page-head compact"><div><h2>Operational actions</h2><p>All actions are audited. Reconciliation never changes balances unless the CLI is run with <code>--fix</code>.</p></div></div>
    <div class="action-grid">
        <form method="post" action="{{ route('admin.system.backup') }}">@csrf<button class="button" type="submit">Create database backup</button></form>
        <form method="post" action="{{ route('admin.system.reconcile') }}">@csrf<button class="button secondary" type="submit">Reconcile wallets</button></form>
        <form method="post" action="{{ route('admin.system.metrics') }}">@csrf<button class="button secondary" type="submit">Refresh 30-day metrics</button></form>
        <form method="post" action="{{ route('admin.system.prune') }}">@csrf<button class="button danger" type="submit">Prune old operational data</button></form>
    </div>
</section>

<section class="panel">
    <h2>Production readiness</h2>
    <div class="table-wrap"><table><thead><tr><th>Check</th><th>Status</th><th>Details</th></tr></thead><tbody>
    @foreach($readinessChecks as $check)
        <tr><td>{{ $check['label'] }}</td><td><span class="status-pill {{ $check['status'] }}">{{ strtoupper($check['status']) }}</span></td><td>{{ $check['message'] }}</td></tr>
    @endforeach
    </tbody></table></div>
</section>

<div class="grid two">
<section class="panel">
    <h2>Runtime configuration</h2>
    <dl class="details">
        <dt>Environment</dt><dd>{{ app()->environment() }}</dd>
        <dt>Database driver</dt><dd>{{ config('database.default') }}</dd>
        <dt>Database path</dt><dd><code>{{ $databasePath ?: 'Managed database connection' }}</code></dd>
        <dt>Database size</dt><dd>{{ $databaseSize !== null ? number_format($databaseSize / 1024, 1).' KB' : '—' }}</dd>
        <dt>Storage</dt><dd class="{{ $storageWritable ? 'positive' : 'negative' }}">{{ $storageWritable ? 'Writable' : 'Blocked' }}</dd>
        <dt>Cache store</dt><dd>{{ config('cache.default') }}</dd>
        <dt>Queue connection</dt><dd>{{ config('queue.default') }}</dd>
        <dt>Application URL</dt><dd><code>{{ config('app.url') }}</code></dd>
        <dt>Security events · 24h</dt><dd>{{ number_format($securityEventCount24h) }}</dd>
        <dt>Failed logins · 24h</dt><dd>{{ number_format($failedLoginCount24h) }}</dd>
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
</div>

<section class="panel">
    <h2>Recent scheduled and manual runs</h2>
    <div class="table-wrap"><table><thead><tr><th>Task</th><th>Status</th><th>Started</th><th>Duration</th><th>Details</th></tr></thead><tbody>
    @forelse($operationRuns as $run)
        <tr>
            <td><code>{{ $run->task }}</code></td>
            <td><span class="status-pill {{ $run->status === 'success' ? 'ok' : ($run->status === 'failed' ? 'error' : 'warning') }}">{{ strtoupper($run->status) }}</span></td>
            <td>{{ $run->started_at?->format('Y-m-d H:i:s') }}</td>
            <td>{{ $run->duration_ms !== null ? number_format($run->duration_ms).' ms' : '—' }}</td>
            <td>{{ $run->error_message ?: json_encode($run->details, JSON_UNESCAPED_SLASHES) }}</td>
        </tr>
    @empty
        <tr><td colspan="5">No operation runs recorded yet.</td></tr>
    @endforelse
    </tbody></table></div>
</section>
@endsection
