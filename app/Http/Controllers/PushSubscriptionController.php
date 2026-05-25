<?php

namespace App\Http\Controllers;

use App\Notifications\TestBrowserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class PushSubscriptionController extends Controller
{
    public function vapidKey(): JsonResponse
    {
        return response()->json([
            'publicKey' => config('webpush.vapid.public_key'),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string', 'max:500'],
            'keys.p256dh' => ['required', 'string'],
            'keys.auth' => ['required', 'string'],
            'contentEncoding' => ['nullable', 'string'],
        ]);

        $request->user()->updatePushSubscription(
            $validated['endpoint'],
            $validated['keys']['p256dh'],
            $validated['keys']['auth'],
            $validated['contentEncoding'] ?? 'aesgcm',
        );

        $request->user()
            ->notificationPreference()
            ->updateOrCreate([], ['browser_notifications_enabled' => true]);

        return response()->json(['subscribed' => true]);
    }

    public function sendTest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string', 'max:500'],
        ]);

        abort_unless(
            filled(config('webpush.vapid.public_key')) && filled(config('webpush.vapid.private_key')),
            422,
            'As chaves VAPID precisam ser configuradas no servidor.',
        );

        $subscriptions = $request->user()
            ->pushSubscriptions()
            ->where('endpoint', $validated['endpoint'])
            ->get();

        abort_unless($subscriptions->isNotEmpty(), 404, 'Este navegador ainda não está inscrito para notificações.');

        Notification::route('WebPush', $subscriptions)
            ->notifyNow(new TestBrowserNotification);

        return response()->json(['sent' => true]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string', 'max:500'],
        ]);

        $request->user()->deletePushSubscription($validated['endpoint']);

        if (! $request->user()->pushSubscriptions()->exists()) {
            $request->user()
                ->notificationPreference()
                ->updateOrCreate([], ['browser_notifications_enabled' => false]);
        }

        return response()->json(['subscribed' => false]);
    }
}
