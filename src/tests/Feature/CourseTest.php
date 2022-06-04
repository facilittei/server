<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;


class CourseTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * The user enrolled courses.
     *
     * @return void
     */
    public function test_enrolled()
    {
        $teacher = $this->createUser();
        $student = $this->createUser();
        $course = $this->createCourse($teacher);
        $this->enroll($course, $student);

        $token = $this->accessToken($student);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->json('GET', '/api/courses/enrolled');

        $response->assertStatus(200);
        $response->assertJson(
            fn (AssertableJson $json) =>
            $json->has(1)
                ->first(
                    fn ($json) =>
                    $json->where('id', $course->id)
                        ->where('title', $course->title)
                        ->where('description', $course->description)
                        ->etc()
                )
        );
    }
}
