<?php

namespace App\Console\Commands;

use App\Models\PushSubscription;
use App\Services\AbnormalityDetector;
use App\Services\WebPushSender;
use Illuminate\Console\Command;

class NotifyAbnormal extends Command
{
    protected $signature = 'app:notify-abnormal';

    protected $description = 'Cek mahasiswa abnormal setelah jam batas & kirim web push ke super admin + koordinator.';

    public function handle(AbnormalityDetector $detector, WebPushSender $sender): int
    {
        if (! $detector->isPastCutoff()) {
            $this->info('Belum lewat jam batas CI, dilewati.');

            return self::SUCCESS;
        }

        $students = $detector->students();
        if ($students->isEmpty()) {
            $this->info('Tidak ada mahasiswa abnormal.');

            return self::SUCCESS;
        }

        $subs = PushSubscription::whereHas('user', fn ($q) => $q->whereIn('role', ['super_admin', 'koordinator']))->get();
        if ($subs->isEmpty()) {
            $this->warn('Ada '.$students->count().' mahasiswa abnormal, tapi belum ada perangkat terdaftar.');

            return self::SUCCESS;
        }

        $names = $students->take(8)->pluck('name')->implode(', ');
        $more = $students->count() > 8 ? ' +'.($students->count() - 8).' lagi' : '';

        $sent = $sender->send($subs, [
            'title' => '⚠️ '.$students->count().' mahasiswa belum CI',
            'body' => $names.$more,
            'url' => url('/'),
        ]);

        $this->info("Terkirim ke {$sent} perangkat ({$students->count()} mahasiswa abnormal).");

        return self::SUCCESS;
    }
}
