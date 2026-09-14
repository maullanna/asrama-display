<?php

namespace App\Services;

use App\Models\Vocation;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class VocationImporter
{
    /**
     * Import mahasiswa vokasi dari file Excel (.xlsx / .xls).
     *
     * Kolom (baris pertama = header, urutan bebas, tidak case-sensitive):
     *   nama, lokasi
     *
     * - nama   : nama mahasiswa (wajib)
     * - lokasi : "sunter" atau "karawang" (wajib; tidak case-sensitive)
     *
     * Foto diupload terpisah lewat panel. firstOrCreate (nama+lokasi) mencegah
     * duplikat saat file di-import ulang.
     *
     * @return array{created:int,skipped:int,errors:array<int,string>}
     */
    public function import(string $path): array
    {
        $created = 0;
        $skipped = 0;
        $errors = [];

        try {
            $reader = IOFactory::createReaderForFile($path);
            $reader->setReadDataOnly(true);
            $rows = $reader->load($path)->getActiveSheet()->toArray(null, true, false, false);
        } catch (\Throwable $e) {
            return ['created' => 0, 'skipped' => 0, 'errors' => ['File tidak bisa dibaca sebagai Excel.']];
        }

        if (empty($rows)) {
            return ['created' => 0, 'skipped' => 0, 'errors' => ['File kosong.']];
        }

        $header = array_map(fn ($h) => Str::of((string) $h)->trim()->lower()->toString(), $rows[0]);
        $idx = array_flip($header);

        if (! isset($idx['nama'], $idx['lokasi'])) {
            return ['created' => 0, 'skipped' => 0, 'errors' => ['Kolom wajib "nama" dan "lokasi" tidak ditemukan di header.']];
        }

        $validLocations = array_keys(Vocation::LOCATIONS); // ['sunter','karawang']
        $total = count($rows);

        for ($i = 1; $i < $total; $i++) {
            $row = $rows[$i];
            $line = $i + 1;

            $get = fn (string $key) => isset($idx[$key], $row[$idx[$key]]) ? trim((string) $row[$idx[$key]]) : '';

            $nama = $get('nama');
            $lokasi = Str::lower($get('lokasi'));

            if ($nama === '' && $lokasi === '') {
                continue; // baris kosong
            }
            if ($nama === '') {
                $skipped++;
                $errors[] = "Baris {$line}: nama kosong.";

                continue;
            }
            if (! in_array($lokasi, $validLocations, true)) {
                $skipped++;
                $errors[] = "Baris {$line}: lokasi \"{$get('lokasi')}\" tidak valid (hanya Sunter/Karawang).";

                continue;
            }

            $vocation = Vocation::firstOrCreate(
                ['name' => $nama, 'location' => $lokasi],
                ['is_active' => true, 'sort_order' => $i],
            );

            if ($vocation->wasRecentlyCreated) {
                $created++;
            } else {
                $skipped++;
            }
        }

        return compact('created', 'skipped', 'errors');
    }
}
