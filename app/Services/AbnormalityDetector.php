<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AbnormalityDetector
{
    public function cutoff(): Carbon
    {
        return Carbon::today()->setTimeFromTimeString(config('asrama.ci_cutoff', '21:00'));
    }

    public function isPastCutoff(): bool
    {
        return Carbon::now()->gte($this->cutoff());
    }

    /**
     * Mahasiswa "abnormal": aktif, punya kamar, belum CI hari ini, dan tidak
     * punya keterangan aktif (sakit/izin/dinas). Sama dengan status 'absent' di kiosk.
     *
     * @return Collection<int, Student>
     */
    public function students(): Collection
    {
        $today = Carbon::today();

        return Student::query()
            ->where('is_active', true)
            ->whereNotNull('room_id')
            ->whereDoesntHave('attendanceLogs', fn ($q) => $q
                ->whereDate('scanned_at', $today)
                ->where('direction', 'ci'))
            ->whereDoesntHave('conditions', fn ($q) => $q
                ->whereDate('start_date', '<=', $today)
                ->where(fn ($q2) => $q2->whereNull('end_date')->orWhereDate('end_date', '>=', $today)))
            ->with('room.floor')
            ->orderBy('name')
            ->get();
    }
}
