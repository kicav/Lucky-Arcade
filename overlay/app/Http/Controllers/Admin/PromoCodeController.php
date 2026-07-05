<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PromoCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PromoCodeController extends Controller
{
    public function index(): View
    {
        return view('admin.promos.index', [
            'promos' => PromoCode::query()->with('creator')->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $promo = PromoCode::query()->create($data + ['created_by' => $request->user()->id]);
        $this->audit($request, $promo, 'promo_code.created', null, $promo->toArray());

        return back()->with('success', 'Promo code created.');
    }

    public function update(Request $request, PromoCode $promo): RedirectResponse
    {
        $before = $promo->toArray();
        $promo->update($this->validated($request, $promo));
        $this->audit($request, $promo, 'promo_code.updated', $before, $promo->fresh()->toArray());

        return back()->with('success', 'Promo code updated.');
    }

    public function toggle(Request $request, PromoCode $promo): RedirectResponse
    {
        $before = $promo->toArray();
        $promo->update(['active' => ! $promo->active]);
        $this->audit($request, $promo, 'promo_code.toggled', $before, $promo->fresh()->toArray());

        return back()->with('success', $promo->active ? 'Promo code activated.' : 'Promo code deactivated.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?PromoCode $promo = null): array
    {
        $request->merge(['code' => mb_strtoupper(trim((string) $request->input('code')))]);

        $data = $request->validate([
            'code' => ['required', 'string', 'max:40', Rule::unique('promo_codes', 'code')->ignore($promo?->id)],
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'credits' => ['required', 'integer', 'min:1', 'max:100000'],
            'max_redemptions' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'active' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
        ]);

        if (! empty($data['starts_at']) && ! empty($data['ends_at'])
            && Carbon::parse($data['ends_at'])->lessThanOrEqualTo(Carbon::parse($data['starts_at']))) {
            throw ValidationException::withMessages(['ends_at' => 'The end time must be after the start time.']);
        }

        if ($promo && ! empty($data['max_redemptions'])
            && (int) $data['max_redemptions'] < $promo->redemptions_count) {
            throw ValidationException::withMessages([
                'max_redemptions' => 'Maximum redemptions cannot be lower than the current redemption count.',
            ]);
        }

        return [
            'code' => mb_strtoupper(trim($data['code'])),
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'credits' => (int) $data['credits'],
            'max_redemptions' => $data['max_redemptions'] ?? null,
            'active' => $request->boolean('active'),
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
        ];
    }

    private function audit(Request $request, PromoCode $promo, string $action, ?array $before, ?array $after): void
    {
        AuditLog::query()->create([
            'actor_id' => $request->user()->id,
            'action' => $action,
            'subject_type' => PromoCode::class,
            'subject_id' => $promo->id,
            'before' => $before,
            'after' => $after,
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'created_at' => now(),
        ]);
    }
}
