<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;

use App\Models\User;
use App\Models\Course;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    /**
     * Get access token for a user.
     * 
     * @param \App\Models\User $user
     * @return string
     */
    public function accessToken(User $user): string
    {
        return $user->createToken('random')->plainTextToken;
    }

    /**
     * Create a user.
     * 
     * @return \App\Models\User
     */
    public function createUser(): User
    {
        $user = User::factory()->create();
        $user->profile()->create([
            'bio' => implode(' ', $this->faker->sentences(5)),
        ]);
        return $user;
    }

    /**
     * Create a course.
     * 
     * @param \App\Models\User $teacher
     * @return \App\Models\Course
     */
    public function createCourse(User $teacher): Course
    {
        $course = Course::create([
            'user_id' => $teacher->id,
            'price' => $this->faker->randomFloat(2),
            'title' => $this->faker->sentence,
            'description' => '{"blocks":[{"key":"e92u9","text":"The Startup of the year.","type":"unstyled","depth":0,"inlineStyleRanges":[],"entityRanges":[{"offset":7,"length":4,"key":0}],"data":{}}],"entityMap":{"0":{"type":"LINK","mutability":"MUTABLE","data":{"url":"https://facilittei.com","className":"jss342"}}}}',
            'is_published' => true,
            'price' => 10,
        ]);
        return $course;
    }

    /**
     * Enroll a student in a course.
     * 
     * @param \App\Models\Course $course
     * @param \App\Models\User $student
     * @return void
     */
    public function enroll(Course $course, User $student): void
    {
        $course->students()->attach([$student->id]);
    }
}
