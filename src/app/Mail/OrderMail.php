<?php

namespace App\Mail;

use App\Models\Course;
use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        public Order $order,
        public Course $course,
        public User $user,
        public bool $isOk,
    ) {
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail = $this->subject(trans('mail.order_subject'));
        if ($this->isOk) {
            return $mail->markdown('mail.orders.success');
        }

        return $mail->markdown('mail.orders.failure');
    }
}
