<?php

namespace Tests\Feature;

use App\Enums\TaskStatus;
use App\Models\User;
use App\Models\Building;
use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BuildingTasksTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Building $building;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);

        $this->building = Building::factory()->create();
    }

    /** @test */
    public function it_retrieves_all_tasks_for_a_building_with_pagination()
    {
        $tasks = Task::factory()
            ->count(6)
            ->for($this->building)
            ->create();

        $tasks->each(fn($task) => TaskComment::factory()->count(2)->for($task)->create());

        $response = $this->getJson("/api/v1/buildings/{$this->building->id}/tasks");

        $response->assertOk()->assertJsonCount(6, 'data');
    }

    /** @test */
    public function it_filters_tasks_by_status()
    {
        Task::factory()->for($this->building)->state(['status' => TaskStatus::COMPLETED->value])->count(2)->create();
        Task::factory()->for($this->building)->state(['status' => TaskStatus::IN_PROGRESS->value])->count(1)->create();

        $response = $this->getJson("/api/v1/buildings/{$this->building->id}/tasks?status=Completed");

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonMissing(['status' => TaskStatus::IN_PROGRESS->value]);
    }

    /** @test */
    public function it_filters_tasks_by_assigned_user()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Task::factory()->for($this->building)->state(['assigned_to' => $userA->id])->create();
        Task::factory()->for($this->building)->state(['assigned_to' => $userB->id])->create();

        $response = $this->getJson("/api/v1/buildings/{$this->building->id}/tasks?assigned_to={$userA->id}");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['assignee' => ['id' => $userA->id]]);
    }

    /** @test */
    public function it_filters_tasks_by_date_range()
    {
        Task::factory()->for($this->building)->state(['created_at' => '2025-10-10'])->create();
        Task::factory()->for($this->building)->state(['created_at' => '2025-09-25'])->create();

        $response = $this->getJson("/api/v1/buildings/{$this->building->id}/tasks?created_from=2025-10-01&created_to=2025-10-31");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertSeeText('2025-10-10');
    }

    /** @test */
    public function it_combines_multiple_filters_correctly()
    {
        $assignedUser = User::factory()->create();

        Task::factory()->for($this->building)->state([
            'status' => 'In Progress',
            'assigned_to' => $assignedUser->id,
            'created_at' => '2025-10-15',
        ])->create();

        Task::factory()->for($this->building)->state(['status' => 'Completed'])->create();

        $url = "/api/v1/buildings/{$this->building->id}/tasks?status=In Progress&assigned_to={$assignedUser->id}&created_from=2025-10-01&created_to=2025-10-31";

        $response = $this->getJson($url);

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['status' => 'In Progress']);
    }

    /** @test */
    public function each_task_includes_nested_comments()
    {
        $task = Task::factory()->for($this->building)->create();
        TaskComment::factory()->count(2)->for($task)->create();

        $response = $this->getJson("/api/v1/buildings/{$this->building->id}/tasks");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['comments' => [['id', 'comment_text', 'created_at']]]
                ]
            ]);
    }

    /** @test */
    public function returns_empty_data_if_building_has_no_tasks()
    {
        $emptyBuilding = Building::factory()->create();

        $response = $this->getJson("/api/v1/buildings/{$emptyBuilding->id}/tasks");

        $response->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertSeeText('No tasks found for this building', false);
    }

    /** @test */
    public function invalid_building_id_returns_bad_request()
    {
        $response = $this->getJson("/api/v1/buildings/invalid_id/tasks");

        $response->assertStatus(400)
            ->assertJsonFragment(['message' => 'Invalid or missing building ID']);
    }

    /** @test */
    public function nonexistent_building_returns_not_found()
    {
        $response = $this->getJson("/api/v1/buildings/9999/tasks");

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Building not found']);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_tasks()
    {
        auth()->logout();

        $response = $this->getJson("/api/v1/buildings/{$this->building->id}/tasks");

        $response->assertStatus(401)
            ->assertJsonFragment(['message' => 'Unauthenticated.']);
    }

    /** @test */
    public function handles_unknown_status_filter()
    {
        Task::factory()->for($this->building)->state(['status' => 'Completed'])->create();

        $response = $this->getJson("/api/v1/buildings/{$this->building->id}/tasks?status=PendingReview");

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    /** @test */
    public function rejects_invalid_date_format()
    {
        $response = $this->getJson("/api/v1/buildings/{$this->building->id}/tasks?created_from=2025/10/01");

        $response->assertStatus(400)
            ->assertJsonFragment(['message' => 'Invalid date format']);
    }

    /** @test */
    public function returns_empty_data_when_filters_have_no_match()
    {
        $response = $this->getJson("/api/v1/buildings/{$this->building->id}/tasks?created_from=2025-01-01&created_to=2025-01-02");

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    /** @test */
    public function filters_are_case_insensitive()
    {
        Task::factory()->for($this->building)->state(['status' => 'Completed'])->create();

        $response = $this->getJson("/api/v1/buildings/{$this->building->id}/tasks?status=completed");

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function input_is_sanitized_against_sql_injection()
    {
        Task::factory()->for($this->building)->state(['status' => 'Completed'])->create();

        $response = $this->getJson("/api/v1/buildings/{$this->building->id}/tasks?status=Completed';DROP TABLE tasks--");

        $response->assertStatus(422)
            ->assertJsonCount(0, 'data');
    }
}