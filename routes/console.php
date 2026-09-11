<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Notifikasi abnormality: cek beberapa kali di malam hari (digate oleh jam batas
// di dalam command). Kirim reminder tiap 30 menit selama masih ada yang abnormal.
Schedule::command('app:notify-abnormal')->dailyAt('21:00');
Schedule::command('app:notify-abnormal')->dailyAt('21:30');
Schedule::command('app:notify-abnormal')->dailyAt('22:00');
Schedule::command('app:notify-abnormal')->dailyAt('22:30');
