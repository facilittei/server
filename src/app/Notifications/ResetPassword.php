<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Crypt;

class ResetPassword extends ResetPasswordNotification
{
    use Queueable;

    /**
     * The password reset token.
     *
     * @var string
     */
    public $token;

    /**
     * The user.
     *
     * @var App\Models\User
     */
    public $user;

    /**
     * Create a new notification instance.
     *
     * @param  string  $token
     * @param  App\Models\User  $user
     * @return void
     */
    public function __construct($token, $user)
    {
        $this->token = $token;
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $email = Crypt::encryptString($this->user->email);
        $link = config('app.client_url').'/reset-password?token='.$this->token.'&info='.$email;

        return (new MailMessage)
            ->subject(trans('messages.password_reset_subject'))
            ->markdown('mail.users.reset', [
                'token' => $this->token,
                'user' => $this->user,
                'link' => $link,
            ]);
    }
}
