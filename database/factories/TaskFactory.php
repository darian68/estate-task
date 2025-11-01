<?php

namespace Database\Factories;

use App\Enums\TaskStatus;
use App\Models\Building;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statusValues = TaskStatus::values();
        return [
            'building_id' => Building::factory(), // default: create building unless overridden
            'created_by' => User::factory(),
            'assigned_to' => User::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(1),
            'status' => $this->faker->randomElement($statusValues),
            'due_at' => $this->faker->dateTimeBetween('now', '+3 days'),
        ];
    }
}
