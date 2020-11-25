<?php

namespace App\Events;

use App\Models\Course;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EnrollMany
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Course to enroll
     *
     * @var App\Models\Course
     */
    public Course $course;

    /**
     * Records to enroll
     *
     * @var array
     */
    public array $records;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Course $course, array $records)
    {
        $this->course = $course;
        $this->records = $records;
    }
}
