<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        $title = $this->faker->randomElement([
            'Conference Planning',
            'Delegation Management',
            'Security Coordination',
            'Media Coverage',
            'Travel Experiences',
        ]);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(4),
            'summary' => $this->faker->sentence(14),
            'description' => $this->faker->paragraphs(3, true),
            'is_published' => true,
            'position' => $this->faker->numberBetween(1, 20),
            'published_at' => now(),
        ];
    }

    public function managementTraining(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Management Training',
            'slug' => 'management-training',
        ]);
    }

    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
        ]);
    }
}
