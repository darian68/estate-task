<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTaskCommentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user and a task
        $this->user = User::factory()->create();
        $this->task = Task::factory()->create();

        // Authenticate the user
        $this->actingAs($this->user, 'sanctum');
    }

    /** @test */
    public function can_create_comment_with_valid_body()
    {
        $payload = ['body' => 'This is a valid comment.'];

        $response = $this->postJson("/api/v1/tasks/{$this->task->id}/comments", $payload);

        $response->assertStatus(201)
                 ->assertJsonFragment([
                     'body' => $payload['body']
                 ]);
    }

    /** @test */
    public function body_is_required_missing()
    {
        $response = $this->postJson("/api/v1/tasks/{$this->task->id}/comments", []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('body');
    }

    /** @test */
    public function body_is_required_empty_string()
    {
        $response = $this->postJson("/api/v1/tasks/{$this->task->id}/comments", ['body' => '']);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('body');
    }

    /** @test */
    public function body_is_required_null()
    {
        $response = $this->postJson("/api/v1/tasks/{$this->task->id}/comments", ['body' => null]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('body');
    }

    /** @test */
    public function body_must_be_string_integer()
    {
        $response = $this->postJson("/api/v1/tasks/{$this->task->id}/comments", ['body' => 123]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('body');
    }

    /** @test */
    public function body_must_be_string_float()
    {
        $response = $this->postJson("/api/v1/tasks/{$this->task->id}/comments", ['body' => 12.5]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('body');
    }

    /** @test */
    public function body_must_be_string_boolean()
    {
        $responseTrue = $this->postJson("/api/v1/tasks/{$this->task->id}/comments", ['body' => true]);
        $responseFalse = $this->postJson("/api/v1/tasks/{$this->task->id}/comments", ['body' => false]);

        $responseTrue->assertStatus(422)->assertJsonValidationErrors('body');
        $responseFalse->assertStatus(422)->assertJsonValidationErrors('body');
    }

    /** @test */
    public function body_must_be_string_array()
    {
        $response = $this->postJson("/api/v1/tasks/{$this->task->id}/comments", ['body' => ['a', 'b']]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('body');
    }

    /** @test */
    public function body_must_be_string_object()
    {
        $response = $this->postJson("/api/v1/tasks/{$this->task->id}/comments", ['body' => new \stdClass()]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('body');
    }

    /** @test */
    public function body_max_length_passes_exactly_1000()
    {
        $body = str_repeat('a', 1000);
        $response = $this->postJson("/api/v1/tasks/{$this->task->id}/comments", ['body' => $body]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['body' => $body]);
    }

    /** @test */
    public function body_max_length_fails_1001()
    {
        $body = str_repeat('a', 1001);
        $response = $this->postJson("/api/v1/tasks/{$this->task->id}/comments", ['body' => $body]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('body');
    }

    /** @test */
    public function body_whitespace_only_string()
    {
        $response = $this->postJson("/api/v1/tasks/{$this->task->id}/comments", ['body' => ' ']);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors('body');
    }
}