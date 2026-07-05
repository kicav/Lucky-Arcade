<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SecurityEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Throwable;

class SystemController extends Controller
{
    public function index(): View
    {
        $databaseStatus = 'OK';
        try {
            DB::select('select 1');
        } catch (Throwable $exception) {
            $databaseStatus = 'ERROR: '.$exception->getMessage();
        }

        $databasePath = config('database.default') === 'sqlite'
            ? (string) config('database.connections.sqlite.database')
            : null;
        $backupDirectory = storage_path('app/backups');
        $backups = File::isDirectory($backupDirectory)
            ? collect(File::files($backupDirectory))->sortByDesc(fn ($file) => $file->getMTime())->take(10)
            : collect();

        return view('admin.system.index', [
            'databaseStatus' => $databaseStatus,
            'databasePath' => $databasePath,
            'databaseSize' => $databasePath && File::exists($databasePath) ? File::size($databasePath) : null,
            'storageWritable' => is_writable(storage_path()),
            'backups' => $backups,
            'securityEventCount24h' => SecurityEvent::query()->where('created_at', '>=', now()->subDay())->count(),
            'failedLoginCount24h' => SecurityEvent::query()->where('event', 'login.failed')->where('created_at', '>=', now()->subDay())->count(),
        ]);
    }

    public function backup(Request $request): RedirectResponse
    {
        $exitCode = Artisan::call('arcade:backup', ['--keep' => 10]);
        $output = trim(Artisan::output());

        AuditLog::query()->create([
            'actor_id' => $request->user()->id,
            'action' => 'system.backup_requested',
            'subject_type' => null,
            'subject_id' => null,
            'before' => null,
            'after' => ['exit_code' => $exitCode, 'output' => mb_substr($output, 0, 1000)],
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'created_at' => now(),
        ]);

        if ($exitCode !== 0) {
            return back()->withErrors(['backup' => $output ?: 'Backup command failed.']);
        }

        return back()->with('success', $output ?: 'Backup created.');
    }
}
