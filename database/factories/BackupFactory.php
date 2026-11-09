<?php

namespace Database\Factories;

use App\Models\Backup;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BackupFactory extends Factory
{
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
