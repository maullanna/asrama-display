<?php

namespace Tests\Feature;

use App\Models\Floor;
use App\Models\Student;
use App\Services\StudentCsvImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentCsvImporterTest extends TestCase
{
    use RefreshDatabase;

    private function importCsv(string $csv): array
    {
        $path = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($path, $csv);
        $result = app(StudentCsvImporter::class)->import($path);
        unlink($path);

        return $result;
    }

    public function test_it_imports_students_and_auto_creates_rooms(): void
    {
        Floor::create(['name' => 'Lantai 1', 'slug' => 'lantai-1', 'sort_order' => 1]);

        $result = $this->importCsv(
            "nim,nama,pin,lantai,kamar,ketua\n"
            ."3265301,ABDUL AZIS,3265301,1,101,1\n"
            ."3265302,BUDI,,1,101,0\n" // pin kosong -> pakai nim
        );

        $this->assertSame(2, $result['created']);
        $this->assertEmpty($result['errors']);

        $this->assertDatabaseHas('students', [
            'student_code' => '3265301', 'name' => 'ABDUL AZIS', 'device_pin' => '3265301', 'is_room_leader' => true,
        ]);
        $this->assertDatabaseHas('students', ['student_code' => '3265302', 'device_pin' => '3265302']);
        $this->assertDatabaseHas('rooms', ['room_number' => '101']); // dibuat otomatis
    }

    public function test_reimport_updates_without_duplicate(): void
    {
        Floor::create(['name' => 'Lantai 1', 'slug' => 'lantai-1', 'sort_order' => 1]);

        $this->importCsv("nim,nama,pin,lantai,kamar,ketua\n3265301,ABDUL,3265301,1,101,1\n");
        $result = $this->importCsv("nim,nama,pin,lantai,kamar,ketua\n3265301,ABDUL EDIT,3265301,1,101,1\n");

        $this->assertSame(1, $result['updated']);
        $this->assertSame(0, $result['created']);
        $this->assertSame(1, Student::count());
        $this->assertDatabaseHas('students', ['student_code' => '3265301', 'name' => 'ABDUL EDIT']);
    }

    public function test_invalid_floor_is_skipped(): void
    {
        Floor::create(['name' => 'Lantai 1', 'slug' => 'lantai-1', 'sort_order' => 1]);

        $result = $this->importCsv("nim,nama,pin,lantai,kamar,ketua\n999,ORANG,999,9,901,0\n");

        $this->assertSame(1, $result['skipped']);
        $this->assertSame(0, $result['created']);
        $this->assertNotEmpty($result['errors']);
    }
}
