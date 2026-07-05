<?php

namespace App\Services;

use App\Models\Announcement;
use Illuminate\Support\Collection;

final class AnnouncementService
{
    /** @return Collection<int, Announcement> */
    public function active(): Collection
    {
        return Announcement::query()->currentlyVisible()->latest()->limit(3)->get();
    }
}
