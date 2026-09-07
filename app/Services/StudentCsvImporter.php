<?php

namespace App\Services;

use App\Models\Floor;
use App\Models\Room;
use App\Models\Student;
use Illuminate\Support\Str;

class StudentCsvImporter
{
    /**
     * Import mahasiswa dari file CSV.
     *
     * Kolom (baris pertama = header, urutan bebas, tidak case-sensitive):
     *   nim, nama, pin, lantai, kamar, ketua
     *
     * - nim   : NIM / student_code (wajib, jadi kunci update)
     * - nama  : nama mahasiswa (wajib)
     * - pin   : PIN mesin fingerprint (opsional; kosong = pakai nim)
     * - lantai: angka lantai 1/2/3 (dicocokkan ke "Lantai N")
     * - kamar : nomor kamar (kamar dibuat otomatis bila belum ada)
     * - ketua : 1/ya/true bila ketua kamar
     *
     * @return array{created:int,updated:int,skipped:int,errors:array<int,string>}
     */
    public function import(string $path): array
    {
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        $handle = fopen($path, 'r');
        if ($handle === false) {
            return ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => ['File tidak bisa dibaca.']];
        }

        // Baca header, normalisasi nama kolom.
        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);

            return ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => ['File kosong.']];
        }
        $header = array_map(fn ($h) => Str::of($h)->trim()->lower()->replace(['ï»¿'], '')->toString(), $header);
        $idx = array_flip($header);

        if (! isset($idx['nim'], $idx['nama'])) {
            fclose($handle);

            return ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => ['Kolom wajib "nim" dan "nama" tidak ditemukan di header.']];
        }

        $floorCache = [];
        $roomCache = [];
        $line = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $line++;

            $get = fn (string $key) => isset($idx[$key], $row[$idx[$key]]) ? trim((string) $row[$idx[$key]]) : '';

            $nim = $get('nim');
            $nama = $get('nama');

            if ($nim === '' && $nama === '') {
                continue; // baris kosong
            }
            if ($nim === '' || $nama === '') {
                $skipped++;
                $errors[] = "Baris {$line}: nim/nama kosong.";

                continue;
            }

            $pin = $get('pin') !== '' ? $get('pin') : $nim;
            $lantai = $get('lantai');
            $kamar = $get('kamar');
            $ketua = in_array(Str::lower($get('ketua')), ['1', 'ya', 'y', 'true', 'ketua'], true);

            $roomId = null;
            if ($lantai !== '' && $kamar !== '') {
                $floorName = 'Lantai '.$lantai;
                $floor = $floorCache[$floorName]
                    ??= Floor::firstWhere('name', $floorName);

                if (! $floor) {
                    $skipped++;
                    $errors[] = "Baris {$line}: lantai \"{$lantai}\" tidak ada (hanya Lantai 1-3).";

                    continue;
                }

                $roomKey = $floor->id.'-'.$kamar;
                $room = $roomCache[$roomKey]
                    ??= Room::firstOrCreate(
                        ['floor_id' => $floor->id, 'room_number' => $kamar],
                        ['capacity' => 8, 'sort_order' => (int) preg_replace('/\D/', '', $kamar)]
                    );
                $roomId = $room->id;
            }

            $student = Student::where('student_code', $nim)->first();

            Student::updateOrCreate(
                ['student_code' => $nim],
                [
                    'name' => $nama,
                    'device_pin' => $pin,
                    'room_id' => $roomId,
                    'is_room_leader' => $ketua,
                    'is_active' => true,
                ]
            );

            $student ? $updated++ : $created++;
        }

        fclose($handle);

        return compact('created', 'updated', 'skipped', 'errors');
    }
}
