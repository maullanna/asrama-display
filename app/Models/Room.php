<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = [
        'floor_id',
        'room_number',
        'capacity',
        'access_pin',
        'status_color',
        'keterangan',
        'is_isolation',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_isolation' => 'boolean',
        ];
    }

    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}
