<?php

use App\Models\AttendanceLog;
use Illuminate\Database\Migrations\Migration;

/**
 * Koreksi audit: arah CI/CO seharusnya ditentukan dari JAM scan, bukan tombol mesin.
 * Scan pagi (04:00-06:00) yang terlanjur tercatat 'ci' sebenarnya adalah check-out.
 * Migrasi ini men-set ulang direction seluruh riwayat sesuai aturan jam.
 */
return new class extends Migration
{
    public function up(): void
    {
        AttendanceLog::query()->orderBy('id')->chunkById(500, function ($logs) {
            foreach ($logs as $log) {
                if (! $log->scanned_at) {
                    continue;
                }

                $dir = AttendanceLog::directionForTime($log->scanned_at);
                if ($dir === null || $dir === $log->direction) {
                    continue;
                }

                try {
                    $log->timestamps = false;      // pertahankan created_at/updated_at asli
                    $log->direction = $dir;
                    $log->save();
                } catch (\Throwable $e) {
                    // lewati bila bentrok unik (device_pin+scanned_at+direction sudah ada)
                }
            }
        });
    }

    public function down(): void
    {
        // Migrasi data satu arah; tidak dibalik otomatis agar koreksi audit tetap terjaga.
    }
};
