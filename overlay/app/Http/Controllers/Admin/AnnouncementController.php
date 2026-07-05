<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        return view('admin.announcements.index', [
            'announcements' => Announcement::query()->with('creator')->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $announcement = Announcement::query()->create($data + ['created_by' => $request->user()->id]);
        $this->audit($request, $announcement, 'announcement.created', null, $announcement->toArray());

        return back()->with('success', 'Announcement created.');
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $before = $announcement->toArray();
        $announcement->update($this->validated($request));
        $this->audit($request, $announcement, 'announcement.updated', $before, $announcement->fresh()->toArray());

        return back()->with('success', 'Announcement updated.');
    }

    public function destroy(Request $request, Announcement $announcement): RedirectResponse
    {
        $before = $announcement->toArray();
        $id = $announcement->id;
        $announcement->delete();

        AuditLog::query()->create([
            'actor_id' => $request->user()->id,
            'action' => 'announcement.deleted',
            'subject_type' => Announcement::class,
            'subject_id' => $id,
            'before' => $before,
            'after' => null,
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'created_at' => now(),
        ]);

        return back()->with('success', 'Announcement deleted.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:1000'],
            'active' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
        ]);

        if (! empty($data['starts_at']) && ! empty($data['ends_at'])
            && Carbon::parse($data['ends_at'])->lessThanOrEqualTo(Carbon::parse($data['starts_at']))) {
            throw ValidationException::withMessages(['ends_at' => 'The end time must be after the start time.']);
        }

        return [
            'title' => $data['title'],
            'body' => $data['body'],
            'active' => $request->boolean('active'),
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
        ];
    }

    private function audit(Request $request, Announcement $announcement, string $action, ?array $before, ?array $after): void
    {
        AuditLog::query()->create([
            'actor_id' => $request->user()->id,
            'action' => $action,
            'subject_type' => Announcement::class,
            'subject_id' => $announcement->id,
            'before' => $before,
            'after' => $after,
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'created_at' => now(),
        ]);
    }
}
