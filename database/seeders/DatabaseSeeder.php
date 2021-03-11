<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Comment;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::factory(10)->create();
        Course::factory(30)->create();
        Chapter::factory(30)->create();
        Lesson::factory(100)->create();
        Comment::factory(150)->create();
    }
}
