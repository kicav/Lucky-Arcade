<?php

namespace App\Providers;

use App\Models\Announcement;
use App\Models\SupportMessage;
use App\Models\UserNotification;
use App\Services\LiveEventService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Application services are resolved through Laravel's container.
    }

    public function boot(): void
    {
        UserNotification::created(function (UserNotification $notification): void {
            app(LiveEventService::class)->publishForUser(
                $notification->user_id,
                'notification.created',
                [
                    'notification_id' => $notification->id,
                    'notification_type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'data' => $notification->data ?? [],
                ],
                topic: 'notification',
            );
        });

        SupportMessage::created(function (SupportMessage $message): void {
            $ticket = $message->ticket()->first();
            if (! $ticket) {
                return;
            }

            $payload = [
                'ticket_id' => $ticket->id,
                'message_id' => $message->id,
                'is_admin' => $message->is_admin,
                'author' => $message->user?->name ?? ($message->is_admin ? 'Support' : 'Player'),
                'excerpt' => mb_substr($message->body, 0, 180),
            ];

            app(LiveEventService::class)->publishForUser(
                $ticket->user_id,
                'support.message.created',
                $payload,
                topic: 'support',
            );

            if (! $message->is_admin) {
                app(LiveEventService::class)->publishAdmin(
                    'support.message.created',
                    $payload,
                    topic: 'support',
                );
            }
        });

        Announcement::saved(function (Announcement $announcement): void {
            app(LiveEventService::class)->publishPublic('announcement.changed', [
                'announcement_id' => $announcement->id,
                'title' => $announcement->title,
            ], topic: 'announcement', ttlSeconds: 900);
        });

        Announcement::deleted(function (Announcement $announcement): void {
            app(LiveEventService::class)->publishPublic('announcement.changed', [
                'announcement_id' => $announcement->id,
                'deleted' => true,
            ], topic: 'announcement', ttlSeconds: 900);
        });
    }
}
