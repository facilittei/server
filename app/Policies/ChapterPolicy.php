<?php

namespace App\Policies;

use App\Models\Chapter;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChapterPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Chapter  $chapter
     * @return mixed
     */
    public function view(User $user, Chapter $chapter)
    {
        return $user->id === $chapter->course->user_id
            || in_array($chapter->course->id, $user->enrolled->pluck('id')->toArray());
    }
}
