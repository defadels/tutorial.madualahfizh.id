<?php

namespace Database\Factories;

use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lesson>
 */
class LessonFactory extends Factory
{
    protected $model = Lesson::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'module_id' => Module::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(2),
            'video_url' => $this->faker->optional(0.8)->url(),
            'duration' => $this->faker->optional(0.7)->time('H:i:s'),
            'order' => $this->faker->numberBetween(1, 20),
        ];
    }
}