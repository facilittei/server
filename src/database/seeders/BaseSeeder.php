<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Course;
use App\Models\Chapter;
use App\Models\Lesson;

class BaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $teacher = $this->createTeacher();
        $students = $this->createStudents();
        $course = $this->createCourse($teacher, $students);
        $chapters = $this->createChapters($course);
        $lessons = $this->createLessons($chapters);
    }

    /**
     * Creates teacher.
     *
     * @return void
     */
    private function createTeacher() {
        return $this->createUser('Bill Gates', 'gates@microsoft.com');
    }

    /**
     * Create students.
     *
     * @return void
     */
    private function createStudents() {
        return [
            $this->createUser('Jeff Bezos', 'jeff@amazon.com'), 
            $this->createUser('Larry Page', 'larry@google.com'),
        ];
    }

    /**
     * Create user.
     *
     * @return void
     */
    private function createUser($name, $email) {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Create course.
     *
     * @return void
     */
    private function createCourse($teacher, $students) {
        $course = Course::create([
            'user_id' => $teacher->id,
            'title' => 'Startup Business',
            'is_published' => true,
        ]);
        $course->students()->attach([$students[0]->id, $students[1]->id]);
        return $course;
    }

    /**
     * Create chapters.
     *
     * @return void
     */
    private function createChapters($course) {
        $chapter1 = Chapter::create([
            'course_id' => $course->id,
            'title' => 'Introduction',
            'position' => 1,
        ]);
        $chapter2 = Chapter::create([
            'course_id' => $course->id,
            'title' => 'Getting started',
            'position' => 2,
        ]);
        $chapter3 = Chapter::create([
            'course_id' => $course->id,
            'title' => 'About business',
            'position' => 3,
        ]);
        return [$chapter1, $chapter2, $chapter3];
    }

    /**
     * Create lessons.
     *
     * @return void
     */
    private function createLessons($chapters) {
        $lesson1 = Lesson::create([
            'chapter_id' => $chapters[0]->id,
            'title' => 'Introduction - Lesson 01',
            'position' => 1,
        ]);
        $lesson2 = Lesson::create([
            'chapter_id' => $chapters[0]->id,
            'title' => 'Introduction - Lesson 02',
            'position' => 2,
        ]);
        $lesson3 = Lesson::create([
            'chapter_id' => $chapters[1]->id,
            'title' => 'Getting started - Lesson 03',
            'position' => 1,
        ]);
        $lesson4 = Lesson::create([
            'chapter_id' => $chapters[2]->id,
            'title' => 'About business - Lesson 04',
            'position' => 1,
        ]);
        return [
            $lesson1,
            $lesson2,
            $lesson3,
            $lesson4,
        ];
    }
}
