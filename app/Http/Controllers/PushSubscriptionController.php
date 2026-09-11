<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use App\Services\WebPushSender;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'endpoint' => ['required', 'string', 'max:500'],
            'keys.p256dh' => ['required', 'string'],
            'keys.auth' => ['required', 'string'],
        ]);

        PushSubscription::updateOrCreate(
            ['endpoint' => $data['endpoint']],
            [
                'user_id' => $request->user()->id,
                'public_key' => $data['keys']['p256dh'],
                'auth_token' => $data['keys']['auth'],
            ]
        );

        return response()->json(['ok' => true]);
    }

    /**
     * Kirim notifikasi uji ke perangkat user yang sedang login.
     */
    public function test(Request $request, WebPushSender $sender)
    {
        $subs = $request->user()->pushSubscriptions;

        if ($subs->isEmpty()) {
            return response()->json(['ok' => false, 'message' => 'Belum ada perangkat terdaftar.'], 422);
        }

        $sent = $sender->send($subs, [
            'title' => 'Uji Notifikasi — Asrama AKTI',
            'body' => 'Notifikasi HP aktif. Anda akan menerima peringatan abnormality di sini.',
            'url' => url('/'),
        ]);

        return response()->json(['ok' => true, 'sent' => $sent]);
    }
}
