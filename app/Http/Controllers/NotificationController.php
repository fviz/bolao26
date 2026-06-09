<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdminBroadcastNotificationRequest;
use App\Jobs\BroadcastAdminNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        $notifications = $request->user()
            ->notifications()
            ->paginate(15)
            ->through(fn (DatabaseNotification $notification): array => [
                'id' => $notification->id,
                'type' => $notification->data['type'] ?? $notification->type,
                'title' => $notification->data['title'] ?? 'Notificação',
                'body' => $notification->data['body'] ?? '',
                'url' => $notification->data['url'] ?? null,
                'readAt' => $notification->read_at?->toIso8601String(),
                'createdAt' => $notification->created_at?->toIso8601String(),
            ])
            ->withQueryString();

        return Inertia::render('Notifications', [
            'notifications' => $notifications,
        ]);
    }

    public function markAsRead(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        abort_unless(
            $notification->notifiable_type === $request->user()::class
            && (int) $notification->notifiable_id === $request->user()->id,
            404,
        );

        $notification->markAsRead();

        return back();
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $request->user()
            ->unreadNotifications()
            ->update(['read_at' => now()]);

        return back();
    }

    public function storeBroadcast(StoreAdminBroadcastNotificationRequest $request): RedirectResponse
    {
        BroadcastAdminNotification::dispatch(
            $request->string('title')->toString(),
            $request->string('body')->toString(),
            $request->filled('url') ? $request->string('url')->toString() : null,
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Notificação enviada para todos os usuários.')]);

        return back();
    }
}
