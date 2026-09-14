<?php

namespace Tests\Feature;

use App\Models\Vocation;
use App\Services\VocationImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class VocationImporterTest extends TestCase
{
    use RefreshDatabase;

    private function importRows(array $rows): array
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray($rows);
        $path = tempnam(sys_get_temp_dir(), 'xlsx').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);
        $result = app(VocationImporter::class)->import($path);
        @unlink($path);

        return $result;
    }

    public function test_it_imports_vocations_with_location_mapping(): void
    {
        $result = $this->importRows([
            ['nama', 'lokasi'],
            ['Andi', 'Sunter'],
            ['Budi', 'karawang'], // case-insensitive
        ]);

        $this->assertSame(2, $result['created']);
        $this->assertEmpty($result['errors']);
        $this->assertDatabaseHas('vocations', ['name' => 'Andi', 'location' => 'sunter']);
        $this->assertDatabaseHas('vocations', ['name' => 'Budi', 'location' => 'karawang']);
    }

    public function test_invalid_location_is_skipped(): void
    {
        $result = $this->importRows([
            ['nama', 'lokasi'],
            ['Cito', 'Bandung'], // lokasi tidak valid
        ]);

        $this->assertSame(0, $result['created']);
        $this->assertSame(1, $result['skipped']);
        $this->assertNotEmpty($result['errors']);
    }

    public function test_reimport_does_not_duplicate(): void
    {
        $this->importRows([['nama', 'lokasi'], ['Andi', 'Sunter']]);
        $result = $this->importRows([['nama', 'lokasi'], ['Andi', 'Sunter']]);

        $this->assertSame(0, $result['created']);
        $this->assertSame(1, Vocation::count());
    }
}
