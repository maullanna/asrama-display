<?php

namespace App\Models;

use App\Services\Thumbnailer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Student extends Model
{
    protected static function booted(): void
    {
        // Regenerasi thumbnail otomatis saat foto berubah (upload/ganti dari panel).
        static::saved(function (Student $student) {
            if ($student->wasChanged('photo_path') && $student->photo_path) {
                try {
                    Thumbnailer::generate($student->photo_path, force: true);
                } catch (\Throwable $e) {
                    // abaikan; kiosk fallback ke foto asli
                }
            }
        });
    }

    protected $fillable = [
        'room_id',
        'student_code',
        'device_pin',
        'name',
        'bed_number',
        'photo_path',
        'is_room_leader',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_room_leader' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Path foto untuk kiosk: pakai thumbnail ringan bila ada, jika tidak foto asli.
     */
    public function getPhotoThumbAttribute(): ?string
    {
        if (! $this->photo_path) {
            return null;
        }

        $thumb = Thumbnailer::thumbPathFor($this->photo_path);

        return Storage::disk('public')->exists($thumb) ? $thumb : $this->photo_path;
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(StudentCondition::class);
    }
}
