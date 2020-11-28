<?php

namespace Database\Factories;

use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;

class LessonFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Lesson::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'chapter_id' => random_int(1, 30),
            'title' => $this->faker->sentence(3),
            'description' => implode(' ', $this->faker->sentences(3)),
            'duration' => $this->faker->time(),
        ];
    }
}
