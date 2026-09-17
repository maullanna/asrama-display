<?php

namespace App\Services;

use App\Models\AttendanceLog;
use App\Models\Student;
use Illuminate\Support\Carbon;

class AttendanceIngestor
{
    /**
     * Parsing body ATTLOG dari mesin (tab-separated) lalu simpan ke attendance_logs.
     *
     * Format tiap baris (dari mesin Solution X105-ID):
     *   PIN \t YYYY-MM-DD HH:MM:SS \t status \t verify \t ...
     *
     * - PIN dicocokkan ke Student.device_pin (null bila belum terdaftar).
     * - Arah CI/CO ditentukan dari JAM scan (Check IN 19:00-02:00, Check OUT 04:00-06:00).
     *   Di luar window: fallback ke tombol mesin (status 1 = CO, selain itu CI).
     * - updateOrCreate mencegah duplikat bila mesin mengirim ulang data lama.
     *
     * @return int Jumlah baris absensi yang berhasil diproses.
     */
    public function ingestAttlog(string $body, ?string $deviceSn = null): int
    {
        $count = 0;
        $lines = preg_split('/\r\n|\r|\n/', trim($body)) ?: [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $fields = explode("\t", $line);
            if (count($fields) < 2) {
                continue;
            }

            $pin = trim($fields[0]);
            $timestamp = trim($fields[1]);
            $status = isset($fields[2]) && trim($fields[2]) !== '' ? (int) trim($fields[2]) : 0;

            if ($pin === '' || $timestamp === '') {
                continue;
            }

            try {
                $scannedAt = Carbon::parse($timestamp);
            } catch (\Throwable $e) {
                continue;
            }

            $student = Student::where('device_pin', $pin)->first();

            // Arah dari jam scan; di luar window pakai tombol mesin sebagai fallback.
            $direction = AttendanceLog::directionForTime($scannedAt)
                ?? ($status === 1 ? 'co' : 'ci');

            AttendanceLog::updateOrCreate(
                [
                    'device_pin' => $pin,
                    'scanned_at' => $scannedAt,
                    'direction' => $direction,
                ],
                [
                    'student_id' => $student?->id,
                    'device_sn' => $deviceSn,
                    'method' => 'fingerprint',
                ]
            );

            $count++;
        }

        return $count;
    }
}
