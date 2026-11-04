<?php

namespace Database\Factories;

use App\Models\Backup;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Backup>
 */
class BackupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'created_by' => User::factory(),
            'filename' => 'backup-'.now()->format('Y-m-d-His').'.sql.gz',
            'size' => fake()->numberBetween(10_000, 5_000_000),
            'status' => Backup::STATUS_COMPLETED,
            'type' => 'manual',
            'completed_at' => now(),
        ];
    }

    /**
     * Indicate that the backup is still pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Backup::STATUS_PENDING,
            'filename' => null,
            'size' => 0,
            'completed_at' => null,
        ]);
    }
}
