<?php

namespace Tests\Unit\Filters;

use App\Enums\TaskStatus;
use App\Filters\TaskFilter;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskFilterTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $assignee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->assignee = User::factory()->create();
    }

    /**
     * Helper to run the filter and return IDs for easy assertion
     */
    protected function applyFilter(array $filters)
    {
        $query = Task::query();
        $filter = new TaskFilter($filters);
        return $filter->apply($query)->pluck('id')->all();
    }

    /** @test */
    public function it_returns_all_tasks_when_filters_are_empty()
    {
        $tasks = Task::factory()->count(3)->create();

        $resultIds = $this->applyFilter([]);

        $this->assertEqualsCanonicalizing($tasks->pluck('id')->all(), $resultIds);
    }

    /** @test */
    public function it_filters_by_single_field_status()
    {
        $openTask = Task::factory()->create(['status' => TaskStatus::OPEN]);
        $doneTask = Task::factory()->create(['status' => TaskStatus::COMPLETED]);

        $resultIds = $this->applyFilter(['status' => TaskStatus::OPEN->value]);

        $this->assertEquals([$openTask->id], $resultIds);
    }

    /** @test */
    public function it_filters_by_single_field_assigned_to()
    {
        $taskA = Task::factory()->create(['assigned_to' => $this->assignee->id]);
        $taskB = Task::factory()->create(['assigned_to' => $this->user->id]);

        $resultIds = $this->applyFilter(['assigned_to' => $this->assignee->id]);

        $this->assertEquals([$taskA->id], $resultIds);
    }

    /** @test */
    public function it_filters_by_single_field_created_from()
    {
        // created_from = 2025-11-02 UTC means tasks on or after that date
        $inside = Task::factory()->create([
            'created_at' => Carbon::parse('2025-11-02 00:00:00', 'UTC'),
        ]);

        $before = Task::factory()->create([
            'created_at' => Carbon::parse('2025-11-01 23:59:59', 'UTC'),
        ]);

        $filters = [
            'created_from' => '2025-11-02',
        ];

        $resultIds = $this->applyFilter($filters);

        $this->assertTrue(in_array($inside->id, $resultIds));
        $this->assertFalse(in_array($before->id, $resultIds));
    }

    /** @test */
    public function it_filters_by_single_field_created_to()
    {
        // created_to = 2025-11-02 means tasks on or before that date
        $inside = Task::factory()->create([
            'created_at' => Carbon::parse('2025-11-02 23:59:59', 'UTC'),
        ]);

        $after = Task::factory()->create([
            'created_at' => Carbon::parse('2025-11-03 00:00:00', 'UTC'),
        ]);

        $filters = [
            'created_to' => '2025-11-02',
        ];

        $resultIds = $this->applyFilter($filters);

        $this->assertTrue(in_array($inside->id, $resultIds));
        $this->assertFalse(in_array($after->id, $resultIds));
    }

    /** @test */
    public function it_filters_by_single_field_timezone()
    {
        // Test effect of timezone conversion (Asia/Ho_Chi_Minh = UTC+7)
        // created_from = 2025-11-01 local → UTC = 2025-10-31 17:00:00
        $tz = 'Asia/Ho_Chi_Minh';

        $tasks = Task::factory()->count(3)->create();

        $resultIds = $this->applyFilter(['timezone' => $tz]);

        $this->assertEqualsCanonicalizing($tasks->pluck('id')->all(), $resultIds);
    }

    /** @test */
    public function it_filters_by_two_fields_status_assigned_to()
    {
        $match = Task::factory()->create([
            'status' => TaskStatus::OPEN,
            'assigned_to' => $this->user->id,
        ]);

        $diffStatus = Task::factory()->create([
            'status' => TaskStatus::COMPLETED,
            'assigned_to' => $this->user->id,
        ]);

        $diffAssignee = Task::factory()->create([
            'status' => TaskStatus::OPEN,
            'assigned_to' => $this->assignee->id,
        ]);

        $filters = [
            'status' => TaskStatus::OPEN->value,
            'assigned_to' => $this->user->id,
        ];

        $resultIds = $this->applyFilter($filters);

        $this->assertTrue(in_array($match->id, $resultIds));
        $this->assertFalse(in_array($diffStatus->id, $resultIds));
        $this->assertFalse(in_array($diffAssignee->id, $resultIds));
    }

    /** @test */
    public function it_filters_by_two_fields_status_created_from()
    {
        $match = Task::factory()->create([
            'status' => TaskStatus::OPEN,
            'created_at' => Carbon::parse('2025-11-02 00:00:00', 'UTC'),
        ]);

        $before = Task::factory()->create([
            'status' => TaskStatus::OPEN,
            'created_at' => Carbon::parse('2025-11-01 23:59:59', 'UTC'),
        ]);

        $diffStatus = Task::factory()->create([
            'status' => TaskStatus::COMPLETED,
            'created_at' => Carbon::parse('2025-11-02 00:00:00', 'UTC'),
        ]);

        $filters = [
            'status' => TaskStatus::OPEN->value,
            'created_from' => '2025-11-02',
        ];

        $resultIds = $this->applyFilter($filters);

        $this->assertTrue(in_array($match->id, $resultIds));
        $this->assertFalse(in_array($before->id, $resultIds));
        $this->assertFalse(in_array($diffStatus->id, $resultIds));
    }

    /** @test */
    public function it_filters_by_two_fields_status_created_to()
    {
        $match = Task::factory()->create([
            'status' => TaskStatus::OPEN,
            'created_at' => Carbon::parse('2025-11-02 12:00:00', 'UTC'),
        ]);

        $after = Task::factory()->create([
            'status' => TaskStatus::OPEN,
            'created_at' => Carbon::parse('2025-11-03 00:00:00', 'UTC'),
        ]);

        $diffStatus = Task::factory()->create([
            'status' => TaskStatus::COMPLETED,
            'created_at' => Carbon::parse('2025-11-02 10:00:00', 'UTC'),
        ]);

        $filters = [
            'status' => TaskStatus::OPEN->value,
            'created_to' => '2025-11-02',
        ];

        $resultIds = $this->applyFilter($filters);

        $this->assertTrue(in_array($match->id, $resultIds));
        $this->assertFalse(in_array($after->id, $resultIds));
        $this->assertFalse(in_array($diffStatus->id, $resultIds));
    }

    /** @test */
    public function it_filters_by_two_fields_status_timezone()
    {
        $tz = 'Asia/Ho_Chi_Minh';
        $match = Task::factory()->create([
            'status' => TaskStatus::OPEN,
            'created_at' => Carbon::parse('2025-10-31 18:00:00', 'UTC'), // after start in UTC
        ]);

        $outside = Task::factory()->create([
            'status' => TaskStatus::OPEN,
            'created_at' => Carbon::parse('2025-10-31 16:59:59', 'UTC'),
        ]);

        $diffStatus = Task::factory()->create([
            'status' => TaskStatus::COMPLETED,
            'created_at' => Carbon::parse('2025-10-31 18:00:00', 'UTC'),
        ]);

        $filters = [
            'status' => TaskStatus::OPEN->value,
            'created_from' => '2025-11-01',
            'timezone' => $tz,
        ];

        $resultIds = $this->applyFilter($filters);

        $this->assertTrue(in_array($match->id, $resultIds));
        $this->assertFalse(in_array($outside->id, $resultIds));
        $this->assertFalse(in_array($diffStatus->id, $resultIds));
    }

    /** @test */
    public function it_filters_by_two_fields_assigned_to_created_from()
    {
        $match = Task::factory()->create([
            'assigned_to' => $this->user->id,
            'created_at' => Carbon::parse('2025-11-02 00:00:00', 'UTC'),
        ]);

        $before = Task::factory()->create([
            'assigned_to' => $this->user->id,
            'created_at' => Carbon::parse('2025-11-01 23:59:59', 'UTC'),
        ]);

        $diffAssignee = Task::factory()->create([
            'assigned_to' => $this->assignee->id,
            'created_at' => Carbon::parse('2025-11-02 00:00:00', 'UTC'),
        ]);

        $filters = [
            'assigned_to' => $this->user->id,
            'created_from' => '2025-11-02',
        ];

        $resultIds = $this->applyFilter($filters);

        $this->assertTrue(in_array($match->id, $resultIds));
        $this->assertFalse(in_array($before->id, $resultIds));
        $this->assertFalse(in_array($diffAssignee->id, $resultIds));
    }

    /** @test */
    public function it_filters_by_two_fields_assigned_to_created_to()
    {
        $match = Task::factory()->create([
            'assigned_to' => $this->user->id,
            'created_at' => Carbon::parse('2025-11-02 12:00:00', 'UTC'),
        ]);

        $after = Task::factory()->create([
            'assigned_to' => $this->user->id,
            'created_at' => Carbon::parse('2025-11-03 00:00:00', 'UTC'),
        ]);

        $diffAssignee = Task::factory()->create([
            'assigned_to' => $this->assignee->id,
            'created_at' => Carbon::parse('2025-11-02 12:00:00', 'UTC'),
        ]);

        $filters = [
            'assigned_to' => $this->user->id,
            'created_to' => '2025-11-02',
        ];

        $resultIds = $this->applyFilter($filters);

        $this->assertTrue(in_array($match->id, $resultIds));
        $this->assertFalse(in_array($after->id, $resultIds));
        $this->assertFalse(in_array($diffAssignee->id, $resultIds));
    }

    /** @test */
    public function it_filters_by_two_fields_assigned_to_timezone()
    {

        $tz = 'Asia/Ho_Chi_Minh';
        $match = Task::factory()->create([
            'assigned_to' => $this->user->id,
            'created_at' => Carbon::parse('2025-10-31 18:00:00', 'UTC'),
        ]);

        $outside = Task::factory()->create([
            'assigned_to' => $this->user->id,
            'created_at' => Carbon::parse('2025-10-31 16:59:59', 'UTC'),
        ]);

        $diffAssignee = Task::factory()->create([
            'assigned_to' => $this->assignee->id,
            'created_at' => Carbon::parse('2025-10-31 18:00:00', 'UTC'),
        ]);

        $filters = [
            'assigned_to' => $this->user->id,
            'timezone' => $tz,
        ];

        $resultIds = $this->applyFilter($filters);

        $this->assertTrue(in_array($match->id, $resultIds));
        $this->assertTrue(in_array($outside->id, $resultIds));
        $this->assertFalse(in_array($diffAssignee->id, $resultIds));
    }

    /** @test */
    public function it_filters_by_two_fields_created_from_created_to()
    {
        $inside = Task::factory()->create([
            'created_at' => Carbon::parse('2025-11-02 12:00:00', 'UTC'),
        ]);

        $before = Task::factory()->create([
            'created_at' => Carbon::parse('2025-11-01 23:59:59', 'UTC'),
        ]);

        $after = Task::factory()->create([
            'created_at' => Carbon::parse('2025-11-03 00:00:00', 'UTC'),
        ]);

        $filters = [
            'created_from' => '2025-11-02',
            'created_to' => '2025-11-02',
        ];

        $resultIds = $this->applyFilter($filters);

        $this->assertTrue(in_array($inside->id, $resultIds));
        $this->assertFalse(in_array($before->id, $resultIds));
        $this->assertFalse(in_array($after->id, $resultIds));
    }

    /** @test */
    public function it_filters_by_two_fields_created_to_timezone()
    {
        $tz = 'Asia/Ho_Chi_Minh';
        $inside = Task::factory()->create([
            'created_at' => Carbon::parse('2025-11-02 15:00:00', 'UTC'),
        ]);

        $after = Task::factory()->create([
            'created_at' => Carbon::parse('2025-11-03 17:00:01', 'UTC'), // beyond local midnight
        ]);

        $filters = [
            'created_to' => '2025-11-03',
            'timezone' => $tz,
        ];

        $resultIds = $this->applyFilter($filters);

        $this->assertTrue(in_array($inside->id, $resultIds));
        $this->assertFalse(in_array($after->id, $resultIds));
    }

    /** @test */
    public function it_filters_by_three_fields_status_assigned_to_and_date_range()
    {
        // UTC timestamps
        $match = Task::factory()->create([
            'status' => TaskStatus::OPEN,
            'assigned_to' => $this->assignee->id,
            'created_at' => Carbon::create(2025, 11, 2, 0, 0, 0, 'UTC'),
        ]);

        // Various non-matches
        Task::factory()->create(['status' => TaskStatus::COMPLETED]);
        Task::factory()->create(['assigned_to' => $this->user->id]);
        Task::factory()->create(['created_at' => Carbon::create(2025, 11, 10, 0, 0, 0, 'UTC')]);

        $filters = [
            'status' => TaskStatus::OPEN->value,
            'assigned_to' => $this->assignee->id,
            'created_from' => '2025-11-01',
            'created_to' => '2025-11-03',
        ];

        $resultIds = $this->applyFilter($filters);

        $this->assertEquals([$match->id], $resultIds);
    }

    /** @test */
    public function it_filters_by_four_fields_status_assigned_to_created_from_and_timezone()
    {
        $tz = 'Asia/Ho_Chi_Minh';

        // 2025-11-01 in +7 is 2025-10-31 17:00 UTC
        $match = Task::factory()->create([
            'status' => TaskStatus::OPEN,
            'assigned_to' => $this->assignee->id,
            'created_at' => Carbon::parse('2025-10-31 18:00:00', 'UTC'), // within range
        ]);

        Task::factory()->create([
            'status' => TaskStatus::OPEN,
            'assigned_to' => $this->assignee->id,
            'created_at' => Carbon::parse('2025-10-30 00:00:00', 'UTC'), // out of range
        ]);

        $filters = [
            'status' => TaskStatus::OPEN->value,
            'assigned_to' => $this->assignee->id,
            'created_from' => '2025-11-01',
            'timezone' => $tz,
        ];

        $resultIds = $this->applyFilter($filters);

        $this->assertEquals([$match->id], $resultIds);
    }

    /** @test */
    public function it_filters_by_all_fields_full_filter()
    {
        $tz = 'Asia/Ho_Chi_Minh';

        $match = Task::factory()->create([
            'status' => TaskStatus::OPEN,
            'assigned_to' => $this->assignee->id,
            'created_at' => Carbon::create(2025, 11, 2, 0, 0, 0, 'UTC'),
        ]);

        // Non-matching cases
        Task::factory()->create(['status' => TaskStatus::COMPLETED]);
        Task::factory()->create(['assigned_to' => $this->user->id]);
        Task::factory()->create(['created_at' => Carbon::create(2025, 11, 10, 0, 0, 0, 'UTC')]);

        $filters = [
            'status' => TaskStatus::OPEN->value,
            'assigned_to' => $this->assignee->id,
            'created_from' => '2025-11-01',
            'created_to' => '2025-11-03',
            'timezone' => $tz,
        ];

        $resultIds = $this->applyFilter($filters);

        $this->assertEquals([$match->id], $resultIds);
    }

    /** @test */
    public function it_applies_valid_time_range_with_timezone()
    {
        $tz = 'Asia/Ho_Chi_Minh';

        // In +7 timezone, created_from=2025-11-01 and created_to=2025-11-02
        // => UTC range 2025-10-31 17:00:00 to 2025-11-02 16:59:59
        $inside = Task::factory()->create([
            'created_at' => Carbon::parse('2025-11-01 00:00:00', 'UTC'),
        ]);
        $outside = Task::factory()->create([
            'created_at' => Carbon::parse('2025-11-03 00:00:00', 'UTC'),
        ]);

        $filters = [
            'created_from' => '2025-11-01',
            'created_to' => '2025-11-02',
            'timezone' => $tz,
        ];

        $resultIds = $this->applyFilter($filters);

        $this->assertTrue(in_array($inside->id, $resultIds));
        $this->assertFalse(in_array($outside->id, $resultIds));
    }

    /** @test */
    public function it_returns_empty_for_unmatched_time_range_with_timezone()
    {
        $tz = 'Asia/Ho_Chi_Minh';

        $task = Task::factory()->create([
            'created_at' => Carbon::parse('2025-11-10 00:00:00', 'UTC'),
        ]);

        $filters = [
            'created_from' => '2025-11-01',
            'created_to' => '2025-11-02',
            'timezone' => $tz,
        ];

        $resultIds = $this->applyFilter($filters);

        $this->assertEmpty($resultIds);
    }

    /** @test */
    public function it_filters_by_created_from_and_to_with_timezone()
    {
        $tz = 'Asia/Ho_Chi_Minh';

        // Create tasks in UTC
        $inRange = Task::factory()->create([
            'created_at' => Carbon::create(2025, 11, 1, 0, 0, 0, $tz),
        ]);

        $before = Task::factory()->create([
            'created_at' => Carbon::create(2025, 10, 30, 0, 0, 0, $tz),
        ]);

        $after = Task::factory()->create([
            'created_at' => Carbon::create(2025, 11, 5, 0, 0, 0, $tz),
        ]);

        // Client provides date range in their timezone (+7)
        $filters = [
            'created_from' => '2025-11-01',
            'created_to'   => '2025-11-03',
            'timezone'     => $tz,
        ];

        $query = Task::query();
        $filter = new TaskFilter($filters);
        $results = $filter->apply($query)->get();

        $this->assertTrue($results->contains($inRange));
        $this->assertFalse($results->contains($before));
        $this->assertFalse($results->contains($after));
    }
}
