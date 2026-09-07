<?php

namespace Database\Seeders;

use App\Models\Floor;
use Illuminate\Database\Seeder;

class FloorSeeder extends Seeder
{
    /**
     * Lantai bersifat statis: hanya Lantai 1, 2, dan 3.
     * firstOrCreate membuat data ini idempoten (aman dijalankan berulang,
     * tidak menduplikasi bila sudah ada).
     */
    public function run(): void
    {
        $floors = [
            ['name' => 'Lantai 1', 'slug' => 'lantai-1', 'sort_order' => 1],
            ['name' => 'Lantai 2', 'slug' => 'lantai-2', 'sort_order' => 2],
            ['name' => 'Lantai 3', 'slug' => 'lantai-3', 'sort_order' => 3],
        ];

        foreach ($floors as $floor) {
            Floor::firstOrCreate(['slug' => $floor['slug']], $floor);
        }
    }
}
