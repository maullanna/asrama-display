<?php

namespace Tests\Feature;

use App\Models\Floor;
use App\Models\Student;
use App\Services\StudentImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class StudentImporterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tulis rows ke file .xlsx sementara, lalu import.
     */
    private function importRows(array $rows): array
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray($rows);

        $path = tempnam(sys_get_temp_dir(), 'xlsx').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        $result = app(StudentImporter::class)->import($path);
        @unlink($path);

        return $result;
    }

    public function test_it_imports_students_and_auto_creates_rooms(): void
    {
        Floor::create(['name' => 'Lantai 1', 'slug' => 'lantai-1', 'sort_order' => 1]);

        $result = $this->importRows([
            ['nim', 'nama', 'pin', 'lantai', 'kamar', 'ketua'],
            ['3265301', 'ABDUL AZIS', '3265301', '1', '101', '1'],
            ['3265302', 'BUDI', '', '1', '101', '0'], // pin kosong -> pakai nim
        ]);

        $this->assertSame(2, $result['created']);
        $this->assertEmpty($result['errors']);

        $this->assertDatabaseHas('students', [
            'student_code' => '3265301', 'name' => 'ABDUL AZIS', 'device_pin' => '3265301', 'is_room_leader' => true,
        ]);
        $this->assertDatabaseHas('students', ['student_code' => '3265302', 'device_pin' => '3265302']);
        $this->assertDatabaseHas('rooms', ['room_number' => '101']);
    }

    public function test_reimport_updates_without_duplicate(): void
    {
        Floor::create(['name' => 'Lantai 1', 'slug' => 'lantai-1', 'sort_order' => 1]);

        $this->importRows([['nim', 'nama', 'pin', 'lantai', 'kamar', 'ketua'], ['3265301', 'ABDUL', '3265301', '1', '101', '1']]);
        $result = $this->importRows([['nim', 'nama', 'pin', 'lantai', 'kamar', 'ketua'], ['3265301', 'ABDUL EDIT', '3265301', '1', '101', '1']]);

        $this->assertSame(1, $result['updated']);
        $this->assertSame(0, $result['created']);
        $this->assertSame(1, Student::count());
        $this->assertDatabaseHas('students', ['student_code' => '3265301', 'name' => 'ABDUL EDIT']);
    }

    public function test_invalid_floor_is_skipped(): void
    {
        Floor::create(['name' => 'Lantai 1', 'slug' => 'lantai-1', 'sort_order' => 1]);

        $result = $this->importRows([['nim', 'nama', 'pin', 'lantai', 'kamar', 'ketua'], ['999', 'ORANG', '999', '9', '901', '0']]);

        $this->assertSame(1, $result['skipped']);
        $this->assertSame(0, $result['created']);
        $this->assertNotEmpty($result['errors']);
    }
}
