<?php

namespace Database\Factories;

use App\Models\Chapter;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChapterFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Chapter::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'course_id' => random_int(1, 10),
            'title' => $this->faker->sentence(3),
            'description' => implode(' ', $this->faker->sentences(3)),
            'position' => random_int(1, 100),
        ];
    }
}
