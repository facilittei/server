<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EnrollMany
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Course to enroll
     *
     * @var \App\Models\Course
     */
    public $course;

    /**
     * Records to enroll
     *
     * @var array
     */
    public $records;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($course, $records)
    {
        $this->course = $course;
        $this->records = $records;
    }
}
