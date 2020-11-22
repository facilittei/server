<?php

namespace App\Listeners;

use App\Events\EnrollMany;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;

class BulkStudent implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  EnrollMany  $event
     * @return void
     */
    public function handle(EnrollMany $event)
    {
        $students = [];
        for ($i = 0; $i < count($event->records); $i++) {
            $user = explode(',', $event->records[$i]);
            if (count($user) == 2) {
                list($name, $email) = $user;
                $isUser = User::where('email', $email)->select('id')->first();
                if ($isUser) {
                    $students[] = $isUser->id;
                } else {
                    $newUser = User::create([
                        'name' => $name,
                        'email' => $email,
                        'password' => bcrypt(Str::random(10)),
                    ]);
                    $students[] = $newUser->id;
                }
            }
        }
        $event->course->students()->sync($students);
    }
}
