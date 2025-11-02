<?php

namespace Tests\Feature;

use App\Enums\TaskStatus;
use App\Models\Building;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\Rules\Enum;
use Tests\TestCase;

class StoreBuildingTasksTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Building $building;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->building = Building::factory()->create();

        $this->actingAs($this->user, 'sanctum');
    }

    /** @test */
    public function create_task_with_valid_data()
    {
        $payload = [
            'title' => 'New Task',
            'description' => 'Task description',
            'assigned_to' => User::factory()->create()->id,
            'status' => TaskStatus::OPEN,
            'due_at' => now()->addDays(2)->toDateString(),
        ];

        $response = $this->postJson("/api/v1/buildings/{$this->building->id}/tasks", $payload);

        $response->assertStatus(201)
                 ->assertJsonFragment([
                     'title' => $payload['title'],
                     'description' => $payload['description'],
                     'status' => $payload['status']
                 ]);
    }

    /** @test */
    public function fail_when_title_missing()
    {
        $payload = [
            'description' => 'Some description',
        ];

        $response = $this->postJson("/api/v1/buildings/{$this->building->id}/tasks", $payload);

        $response->assertStatus(422)->assertJsonValidationErrors('title');
    }

    /** @test */
    public function fail_when_title_empty_or_null()
    {
        foreach (['', null] as $value) {
            $payload = ['title' => $value];
            $response = $this->postJson("/api/v1/buildings/{$this->building->id}/tasks", $payload);
            $response->assertStatus(422)->assertJsonValidationErrors('title');
        }
    }

    /** @test */
    public function fail_when_title_not_string()
    {
        $invalidValues = [123, 12.5, true, false, ['a', 'b'], new \stdClass()];

        foreach ($invalidValues as $value) {
            $payload = ['title' => $value];
            $response = $this->postJson("/api/v1/buildings/{$this->building->id}/tasks", $payload);
            $response->assertStatus(422)->assertJsonValidationErrors('title');
        }
    }

    /** @test */
    public function fail_when_title_too_long()
    {
        $payload = ['title' => str_repeat('a', 256)];
        $response = $this->postJson("/api/v1/buildings/{$this->building->id}/tasks", $payload);
        $response->assertStatus(422)->assertJsonValidationErrors('title');
    }

    /** @test */
    public function fail_when_description_not_string()
    {
        $invalidValues = [123, 12.5, true, false, ['a', 'b'], new \stdClass()];

        foreach ($invalidValues as $value) {
            $payload = ['title' => 'Valid title', 'description' => $value];
            $response = $this->postJson("/api/v1/buildings/{$this->building->id}/tasks", $payload);
            $response->assertStatus(422)->assertJsonValidationErrors('description');
        }
    }

    /** @test */
    public function fail_when_assigned_to_does_not_exist()
    {
        $payload = ['title' => 'Valid title', 'assigned_to' => 99999];
        $response = $this->postJson("/api/v1/buildings/{$this->building->id}/tasks", $payload);
        $response->assertStatus(422)->assertJsonValidationErrors('assigned_to');
    }

    /** @test */
    public function fail_when_status_not_in_enum()
    {
        $payload = ['title' => 'Valid title', 'status' => 'invalid_status'];
        $response = $this->postJson("/api/v1/buildings/{$this->building->id}/tasks", $payload);
        $response->assertStatus(422)->assertJsonValidationErrors('status');
    }

    /** @test */
    public function fail_when_due_at_invalid_date_or_past()
    {
        $invalidDates = ['invalid-date', now()->subDay()->toDateString()];

        foreach ($invalidDates as $date) {
            $payload = ['title' => 'Valid title', 'due_at' => $date];
            $response = $this->postJson("/api/v1/buildings/{$this->building->id}/tasks", $payload);
            $response->assertStatus(422)->assertJsonValidationErrors('due_at');
        }
    }
}