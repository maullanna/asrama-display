<?php

namespace App\Services;

use App\Models\PushSubscription;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushSender
{
    /**
     * Kirim satu payload notifikasi ke banyak subscription.
     *
     * @param  iterable<PushSubscription>  $subscriptions
     * @param  array{title:string, body:string, url?:string}  $payload
     * @return int Jumlah notifikasi yang berhasil terkirim.
     */
    public function send(iterable $subscriptions, array $payload): int
    {
        $webPush = new WebPush(['VAPID' => [
            'subject' => config('webpush.vapid.subject'),
            'publicKey' => config('webpush.vapid.public_key'),
            'privateKey' => config('webpush.vapid.private_key'),
        ]]);

        $count = 0;
        foreach ($subscriptions as $sub) {
            $webPush->queueNotification(
                Subscription::create([
                    'endpoint' => $sub->endpoint,
                    'publicKey' => $sub->public_key,
                    'authToken' => $sub->auth_token,
                ]),
                json_encode($payload)
            );
            $count++;
        }

        foreach ($webPush->flush() as $report) {
            if (! $report->isSuccess()) {
                $count--;
                // Bersihkan subscription yang sudah kedaluwarsa / dibatalkan.
                if ($report->isSubscriptionExpired()) {
                    PushSubscription::where('endpoint', $report->getEndpoint())->delete();
                }
            }
        }

        return max($count, 0);
    }
}
