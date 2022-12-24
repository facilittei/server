<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Comment;
use App\Models\Course;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            BaseSeeder::class,
        ]);

        // User::factory(10)->create();
        // Course::factory(30)->create();
        // Chapter::factory(30)->create();
        // Lesson::factory(100)->create();
        // Comment::factory(150)->create();
        // Profile::factory(10)->create();
        // Group::factory(1)->create();

        User::create([
            'name' => 'Admin',
            'email' => 'hey@facilittei.com',
            'email_verified_at' => now(),
            'password' => '$2y$10$3OGjDBw3nuroAKSvf.bHGO3XnEw.Yij9cEn5IA.4UHcbV6hmsHy62',
            'remember_token' => Str::random(10),
            'role' => 'ADMIN_CONSOLE',
        ]);
    }
}
