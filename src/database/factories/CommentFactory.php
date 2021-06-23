<?php

namespace Database\Factories;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Comment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'course_id' => random_int(1, 10),
            'chapter_id' => random_int(1, 10),
            'lesson_id' => random_int(1, 100),
            'user_id' => random_int(1, 10),
            'description' => implode(' ', $this->faker->sentences(3)),
        ];
    }
}
