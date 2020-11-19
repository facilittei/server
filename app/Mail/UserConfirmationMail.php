<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The user.
     *
     * @var \App\Models\User
     */
    public $user;

    /**
     * The verification link.
     *
     * @var string
     */
    public $verification;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->verification = strval($this->user->id) . '-' . base64_encode($this->user->created_at);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(trans('mail.email_confirmation_subject'))
            ->markdown('mail.users.confirmation');
    }
}
