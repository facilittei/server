<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Faker\Factory as Faker;
use App\Models\User;
use App\Models\Course;
use App\Models\Chapter;
use App\Models\Lesson;
use App\Models\Comment;
use App\Models\Group;

class BaseSeeder extends Seeder
{
    private $faker;

    function __construct() {
        $this->faker = Faker::create();
    }

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
        $this->createComments($lessons);
        $this->createGroup($teacher); // beta group
        $this->watchLessons($students, $lessons);
        $this->favoriteLessons($students, $lessons);
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
        $this->createUser('Elon Musk', 'elon@tesla.com');

        return [
            $this->createUser('Jeff Bezos', 'jeff@amazon.com'), 
            $this->createUser('Larry Page', 'larry@google.com'),
            $this->createUser('Mark Zuckerberg', 'mark@facebook.com'),
        ];
    }

    /**
     * Create user.
     *
     * @return void
     */
    private function createUser($name, $email) {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $user->profile()->create([
            'bio' => $name . ' ' . implode(' ', $this->faker->sentences(3)),
        ]);

        return $user;
    }

    /**
     * Create course.
     *
     * @return void
     */
    private function createCourse($teacher, $students) {
        $course = Course::create([
            'user_id' => $teacher->id,
            'price' => $this->faker->randomFloat(2),
            'title' => 'Startup Business',
            'description' => '{"blocks":[{"key":"e92u9","text":"The Startup of the year.","type":"unstyled","depth":0,"inlineStyleRanges":[],"entityRanges":[{"offset":7,"length":4,"key":0}],"data":{}}],"entityMap":{"0":{"type":"LINK","mutability":"MUTABLE","data":{"url":"https://facilittei.com","className":"jss342"}}}}',
            'is_published' => true,
        ]);

        Course::create([
            'user_id' => $teacher->id,
            'title' => 'Startup Business 2',
            'description' => '{"blocks":[{"key":"e92u9","text":"The Startup of the year.","type":"unstyled","depth":0,"inlineStyleRanges":[],"entityRanges":[{"offset":7,"length":4,"key":0}],"data":{}}],"entityMap":{"0":{"type":"LINK","mutability":"MUTABLE","data":{"url":"https://facilittei.com","className":"jss342"}}}}',
            'is_published' => false,
        ]);

        $course->students()->attach([$students[0]->id, $students[1]->id, $students[2]->id]);
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
            'is_published' => true,
        ]);
        $chapter2 = Chapter::create([
            'course_id' => $course->id,
            'title' => 'Getting started',
            'position' => 2,
            'is_published' => true,
        ]);
        $chapter3 = Chapter::create([
            'course_id' => $course->id,
            'title' => 'About business',
            'position' => 3,
            'is_published' => true,
        ]);
        return [$chapter1, $chapter2, $chapter3];
    }

    /**
     * Create lessons.
     *
     * @return void
     */
    private function createLessons($chapters) {
        $lessons = [];
        $chaptersAssoc = [0,0,1,2];
        for ($i = 0; $i < 4; $i++) {
            $description = '{"blocks":[{"key":"e92u9","text":"Content of lesson 0'. $i .'","type":"unstyled","depth":0,"inlineStyleRanges":[],"entityRanges":[{"offset":7,"length":4,"key":0}],"data":{}}],"entityMap":{"0":{"type":"LINK","mutability":"MUTABLE","data":{"url":"https://facilittei.com","className":"jss342"}}}}';
            $lessons[] = Lesson::create([
                'chapter_id' => $chapters[$chaptersAssoc[$i]]->id,
                'title' => 'Lesson 0'. $i + 1,
                'description' => $description,
                'position' => $i == 1 ? 2 : 1,
                'is_published' => true,
                'is_preview' => $i % 2 ? true : false,
            ]);
        }
        return $lessons;
    }

    /**
     * Create comments.
     *
     * @return void
     */
    private function createComments($lessons) {
        foreach ($lessons as $lesson) {
            for ($i = 1; $i <= 3; $i++) {
                Comment::create([
                    'course_id' => $lesson->chapter->course_id,
                    'lesson_id' => $lesson->id,
                    'user_id' => random_int(1, 3), // teacher and students
                    'description' => $this->faker->sentence(),
                ]);
            }
        }
    }

    /**
     * Create group.
     *
     * @return void
     */
    private function createGroup($teacher) {
        $group = Group::create([
            'name' => 'Beta',
            'code' => 'usuarios_beta',
        ]);
        $group->users()->attach([$teacher->id]);
    }

    /**
     * Watch lessons.
     * 
     * We have four lessons and will apply the watched flag
     * to users but differently
     * 
     * @return void
     */
    private function watchLessons($students, $lessons) {
        for ($i = 0; $i < 4; $i++) {
            $students[0]->watched()->attach($lessons[$i]->id);
        }

        for ($i = 0; $i < 3; $i++) {
            $students[1]->watched()->attach($lessons[$i]->id);
        }
    }

    /**
     * Favorites lessons.
     * 
     * We have four lessons and will apply the favorite flag
     * to users but differently
     * 
     * @return void
     */
    private function favoriteLessons($students, $lessons) {
        for ($i = 0; $i < 2; $i++) {
            $students[0]->favorited()->attach($lessons[$i]->id);
        }

        for ($i = 0; $i < 1; $i++) {
            $students[1]->favorited()->attach($lessons[$i]->id);
        }
    }
}
