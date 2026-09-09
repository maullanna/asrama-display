<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserResourceAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_access_users_page(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));

        $this->get('/admin/users')->assertOk();
    }

    public function test_koordinator_cannot_access_users_page(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'koordinator']));

        $this->get('/admin/users')->assertForbidden();
    }

    public function test_koordinator_can_still_manage_data(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'koordinator']));

        $this->get('/admin/students')->assertOk();
        $this->get('/admin/rooms')->assertOk();
        $this->get('/admin/student-conditions')->assertOk();
    }
}
