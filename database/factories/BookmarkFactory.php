<?php

namespace Database\Factories;

use App\Enums\MetadataStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;


class BookmarkFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'url' => 'https://www.'.$this->faker->domainName(),
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'metadata_status' => MetadataStatus::PENDING->value,
            'metadata_error' => null,
        ];
    }


    public function completed(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'metadata_status' => MetadataStatus::COMPLETED->value,
            ];
        });
    }

    public function failed(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'metadata_status' => MetadataStatus::FAILED->value,
                'metadata_error' => 'Failed to fetch metadata',
            ];
        });
    }
}
