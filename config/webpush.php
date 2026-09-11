<?php

return [

    /*
     | Kunci VAPID untuk Web Push. Public key dipakai di sisi browser saat
     | subscribe; private key dipakai server saat mengirim notifikasi.
     | Set di .env: VAPID_PUBLIC_KEY, VAPID_PRIVATE_KEY, VAPID_SUBJECT.
     */
    'vapid' => [
        'subject' => env('VAPID_SUBJECT', 'mailto:admin@asrama.akti.ac.id'),
        'public_key' => env('VAPID_PUBLIC_KEY'),
        'private_key' => env('VAPID_PRIVATE_KEY'),
    ],

];
