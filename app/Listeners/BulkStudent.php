<?php

namespace App\Listeners;

use App\Events\EnrollMany;
use App\Mail\CourseEnrollManyMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
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
                $userDB = null;
                $isUser = User::where('email', $email)->select('id', 'name', 'email')->first();
                if ($isUser) {
                    $students[] = $isUser->id;
                    $userDB = $isUser;
                } else {
                    $newUser = User::create([
                        'name' => $name,
                        'email' => $email,
                        'password' => bcrypt(Str::random(10)),
                    ]);
                    $students[] = $newUser->id;
                    $userDB = $newUser;
                }
                Mail::to($userDB->email)->queue(new CourseEnrollManyMail($event->course, $userDB));
            }
        }
        $event->course->students()->sync($students);
    }
}
