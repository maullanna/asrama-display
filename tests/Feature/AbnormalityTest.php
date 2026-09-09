<?php

namespace Tests\Feature;

use App\Models\AttendanceLog;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AbnormalityTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function makeStudent(string $name): Student
    {
        $floor = Floor::firstOrCreate(['slug' => 'lantai-1'], ['name' => 'Lantai 1', 'sort_order' => 1]);
        $room = Room::firstOrCreate(['floor_id' => $floor->id, 'room_number' => '101'], ['capacity' => 8, 'sort_order' => 1]);

        return Student::create([
            'room_id' => $room->id,
            'student_code' => 'NIM'.$name,
            'name' => $name,
            'device_pin' => 'PIN'.$name,
        ]);
    }

    public function test_abnormal_listed_after_cutoff(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(22, 0)); // setelah 21:00
        $this->makeStudent('Budi'); // tanpa CI, tanpa keterangan -> abnormal

        $this->get('/kiosk-rooms')
            ->assertOk()
            ->assertSee('"name":"Budi"', false); // ada di abnormal-json
    }

    public function test_not_abnormal_before_cutoff(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(10, 0)); // sebelum 21:00
        $this->makeStudent('Budi');

        $this->get('/kiosk-rooms')
            ->assertOk()
            ->assertDontSee('"name":"Budi"', false); // abnormal-json kosong []
    }

    public function test_student_with_ci_is_not_abnormal(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(22, 0));
        $student = $this->makeStudent('Andi');
        AttendanceLog::create([
            'student_id' => $student->id,
            'device_pin' => $student->device_pin,
            'scanned_at' => Carbon::today()->setTime(19, 0),
            'direction' => 'ci',
            'method' => 'fingerprint',
        ]);

        $this->get('/kiosk-rooms')
            ->assertOk()
            ->assertDontSee('"name":"Andi"', false);
    }
}
