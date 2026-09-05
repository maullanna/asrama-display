<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = [
        'room_id',
        'student_code',
        'device_pin',
        'name',
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
