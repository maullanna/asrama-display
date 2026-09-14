<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vocation extends Model
{
    protected $fillable = [
        'name',
        'photo_path',
        'location',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /** Daftar lokasi vokasi (tetap). */
    public const LOCATIONS = [
        'sunter' => 'Sunter',
        'karawang' => 'Karawang',
    ];
}
