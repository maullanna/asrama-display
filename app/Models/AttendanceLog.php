<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class AttendanceLog extends Model
{
    /**
     * Tentukan arah (CI/CO) berdasarkan JAM scan, bukan tombol mesin.
     *
     *  - Check IN  : 19:00 - 02:00 (malam sampai dini hari)
     *  - Check OUT : 04:00 - 06:00 (pagi)
     *  - Di luar kedua window: null (tak dipastikan -> pakai fallback).
     */
    public static function directionForTime(Carbon $t): ?string
    {
        $m = $t->hour * 60 + $t->minute;

        if ($m >= 19 * 60 || $m <= 2 * 60) {
            return 'ci';
        }
        if ($m >= 4 * 60 && $m <= 6 * 60) {
            return 'co';
        }

        return null;
    }

    protected $fillable = [
        'student_id',
        'device_pin',
        'device_sn',
        'scanned_at',
        'direction',
        'method',
    ];

    protected function casts(): array
    {
        return [
            'scanned_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
