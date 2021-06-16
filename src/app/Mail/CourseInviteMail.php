<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Course;
use App\Models\CourseInvite;

class CourseInviteMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The course.
     *
     * @var \App\Models\Course
     */
    public $course;

    /**
     * The invite.
     *
     * @var \App\Models\CourseInvite
     */
    public $invite;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Course $course, CourseInvite $invite)
    {
        $this->course = $course;
        $this->invite = $invite;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(trans('mail.course_subject'))->markdown('mail.courses.invite');
    }    
}
