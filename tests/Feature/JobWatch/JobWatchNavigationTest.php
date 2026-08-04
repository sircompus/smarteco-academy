<?php

namespace Tests\Feature\JobWatch;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobWatchNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_watch_module_exists_and_is_active(): void
    {
        $this->assertDatabaseHas('modules', [
            'slug' => 'job-watch',
            'is_active' => true,
            'route_prefix' => 'job-watches',
        ]);
    }

    public function test_student_dashboard_displays_job_watch_module(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSeeText('Veille d’emploi')
            ->assertSee(route('student.job-watches.index'));
    }

    public function test_student_menu_displays_job_watch_link(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('student.job-watches.index'))
            ->assertOk()
            ->assertSeeText("Veille d'emploi")
            ->assertSee(route('student.job-watches.index'));
    }
}
