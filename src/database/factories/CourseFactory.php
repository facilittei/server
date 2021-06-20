<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CourseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Course::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => random_int(1, 10),
            'title' => $this->faker->sentence(3),
            'slug' => Str::slug($this->faker->sentence(3)),
            'description' => implode(' ', $this->faker->sentences(3)),
        ];
    }
}
