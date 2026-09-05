<?php

namespace Database\Seeders;

use App\Models\AttendanceLog;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Student;
use App\Models\StudentCondition;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AsramaSeeder extends Seeder
{
    public function run(): void
    {
        $faker = fake('id_ID');

        $floors = [
            ['name' => 'Lantai 1', 'slug' => 'lantai-1', 'rooms' => ['101', '102', '103', '104']],
            ['name' => 'Lantai 2', 'slug' => 'lantai-2', 'rooms' => ['201', '202', '203', '204']],
        ];

        $studentCodeSequence = 1;
        $students = collect();

        foreach ($floors as $i => $floorData) {
            $floor = Floor::create([
                'name' => $floorData['name'],
                'slug' => $floorData['slug'],
                'sort_order' => $i,
            ]);

            foreach ($floorData['rooms'] as $j => $number) {
                $room = Room::create([
                    'floor_id' => $floor->id,
                    'room_number' => $number,
                    'capacity' => 8,
                    'access_pin' => str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT),
                    'sort_order' => $j,
                ]);

                $occupants = random_int(5, 8);

                for ($k = 0; $k < $occupants; $k++) {
                    $student = Student::create([
                        'room_id' => $room->id,
                        'student_code' => '2026' . str_pad((string) $studentCodeSequence, 4, '0', STR_PAD_LEFT),
                        'device_pin' => str_pad((string) $studentCodeSequence, 6, '0', STR_PAD_LEFT),
                        'name' => $faker->name(),
                        'photo_path' => null,
                        'is_room_leader' => $k === 0,
                        'is_active' => true,
                    ]);

                    $studentCodeSequence++;
                    $students->push($student);
                }
            }
        }

        $today = Carbon::today();

        foreach ($students as $student) {
            $roll = random_int(1, 100);

            if ($roll <= 5) {
                // sedang sakit
                StudentCondition::create([
                    'student_id' => $student->id,
                    'type' => 'sakit',
                    'direction' => null,
                    'start_date' => $today->copy()->subDay(),
                    'end_date' => $today->copy()->addDay(),
                    'note' => $faker->randomElement(['Demam', 'Flu', 'Sakit perut', 'Migrain']),
                    'reported_by_student_id' => null,
                ]);

                continue;
            }

            if ($roll <= 15) {
                // sedang izin keluar (CO), belum ada log CO
                $leader = $student->room->students()->where('is_room_leader', true)->first();

                StudentCondition::create([
                    'student_id' => $student->id,
                    'type' => 'izin',
                    'direction' => 'co',
                    'start_date' => $today,
                    'end_date' => $today,
                    'note' => $faker->randomElement(['Pulang', 'Urusan kampus', 'Acara keluarga']),
                    'reported_by_student_id' => $leader?->id,
                ]);

                AttendanceLog::create([
                    'student_id' => $student->id,
                    'device_pin' => $student->device_pin,
                    'device_sn' => 'X105-ID-01',
                    'scanned_at' => $today->copy()->setTime(6, random_int(0, 59)),
                    'direction' => 'ci',
                    'method' => 'fingerprint',
                ]);

                continue;
            }

            if ($roll <= 85) {
                // sudah CI, belum CO (di kamar)
                AttendanceLog::create([
                    'student_id' => $student->id,
                    'device_pin' => $student->device_pin,
                    'device_sn' => 'X105-ID-01',
                    'scanned_at' => $today->copy()->setTime(random_int(17, 21), random_int(0, 59)),
                    'direction' => 'ci',
                    'method' => 'fingerprint',
                ]);

                continue;
            }

            if ($roll <= 95) {
                // sudah CI lalu CO
                $ci = $today->copy()->setTime(random_int(6, 8), random_int(0, 59));
                $co = $today->copy()->setTime(random_int(9, 16), random_int(0, 59));

                AttendanceLog::create([
                    'student_id' => $student->id,
                    'device_pin' => $student->device_pin,
                    'device_sn' => 'X105-ID-01',
                    'scanned_at' => $ci,
                    'direction' => 'ci',
                    'method' => 'fingerprint',
                ]);

                AttendanceLog::create([
                    'student_id' => $student->id,
                    'device_pin' => $student->device_pin,
                    'device_sn' => 'X105-ID-01',
                    'scanned_at' => $co,
                    'direction' => 'co',
                    'method' => 'fingerprint',
                ]);

                continue;
            }

            // sisanya: belum ada aktivitas sama sekali
        }

        // beberapa log "hantu" dari mesin dengan PIN yang belum terdaftar
        AttendanceLog::create([
            'student_id' => null,
            'device_pin' => '999999',
            'device_sn' => 'X105-ID-01',
            'scanned_at' => $today->copy()->setTime(7, 12),
            'direction' => 'ci',
            'method' => 'fingerprint',
        ]);
    }
}
