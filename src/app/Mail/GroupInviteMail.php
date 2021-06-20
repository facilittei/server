<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Group;
use App\Models\GroupInvite;

class GroupInviteMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The group.
     *
     * @var \App\Models\Group
     */
    public $group;

    /**
     * The invite.
     *
     * @var \App\Models\GroupInvite
     */
    public $invite;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Group $group, GroupInvite $invite)
    {
        $this->group = $group;
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
