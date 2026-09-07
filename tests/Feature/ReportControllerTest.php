<?php

namespace Tests\Feature;

use App\Models\Floor;
use App\Models\Room;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeRoom(string $pin = '1234'): Room
    {
        $floor = Floor::create(['name' => 'Lantai 1', 'slug' => 'lantai-1', 'sort_order' => 1]);

        return Room::create([
            'floor_id' => $floor->id,
            'room_number' => '101',
            'capacity' => 8,
            'access_pin' => $pin,
            'sort_order' => 1,
        ]);
    }

    public function test_report_form_and_kiosk_render(): void
    {
        $this->makeRoom();

        $this->get('/lapor')->assertOk()->assertSee('Lapor Kondisi Anggota');
        $this->get('/')->assertOk(); // kiosk + QR generation tidak error
    }

    public function test_room_leader_can_report_with_correct_pin(): void
    {
        $room = $this->makeRoom('1234');
        $leader = Student::create(['room_id' => $room->id, 'student_code' => 'A1', 'name' => 'Ketua', 'is_room_leader' => true]);
        $member = Student::create(['room_id' => $room->id, 'student_code' => 'A2', 'name' => 'Anggota', 'is_room_leader' => false]);

        $response = $this->post('/lapor', [
            'room_id' => $room->id,
            'access_pin' => '1234',
            'student_id' => $member->id,
            'reason' => 'rumah_sakit',
            'start_date' => '2026-09-07',
            'note' => 'RS Umum',
        ]);

        $response->assertRedirect(route('report.show'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('student_conditions', [
            'student_id' => $member->id,
            'type' => 'sakit',
            'note' => 'Rumah Sakit - RS Umum',
            'reported_by_student_id' => $leader->id,
        ]);
    }

    public function test_wrong_pin_is_rejected(): void
    {
        $room = $this->makeRoom('1234');
        $member = Student::create(['room_id' => $room->id, 'student_code' => 'A2', 'name' => 'Anggota']);

        $response = $this->from('/lapor')->post('/lapor', [
            'room_id' => $room->id,
            'access_pin' => '9999',
            'student_id' => $member->id,
            'reason' => 'sakit',
            'start_date' => '2026-09-07',
        ]);

        $response->assertRedirect('/lapor');
        $response->assertSessionHasErrors('access_pin');
        $this->assertDatabaseCount('student_conditions', 0);
    }

    public function test_member_from_other_room_is_rejected(): void
    {
        $room = $this->makeRoom('1234');
        $otherFloor = Floor::create(['name' => 'Lantai 2', 'slug' => 'lantai-2', 'sort_order' => 2]);
        $otherRoom = Room::create(['floor_id' => $otherFloor->id, 'room_number' => '201', 'capacity' => 8, 'access_pin' => '5678', 'sort_order' => 1]);
        $outsider = Student::create(['room_id' => $otherRoom->id, 'student_code' => 'B1', 'name' => 'Luar']);

        $response = $this->from('/lapor')->post('/lapor', [
            'room_id' => $room->id,
            'access_pin' => '1234',
            'student_id' => $outsider->id,
            'reason' => 'sakit',
            'start_date' => '2026-09-07',
        ]);

        $response->assertSessionHasErrors('student_id');
        $this->assertDatabaseCount('student_conditions', 0);
    }
}
