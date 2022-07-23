<?php

namespace Tests\Unit;

use App\Http\Views\CourseView;
use PHPUnit\Framework\TestCase;
use stdClass;

class CourseViewTest extends TestCase
{
    /**
     * Get total amount from collection.
     *
     * @return void
     */
    public function testGetCourseLessonTotal()
    {
        $courseLesson = new stdClass();
        $courseLesson->id = 1;
        $courseLesson->total = 10;
        $collection = [$courseLesson];

        $this->assertEquals(10, CourseView::getCollectionByCourse($collection, 1));
    }

    /**
     * Get zero from an empty collection.
     *
     * @return void
     */
    public function testEmptyCollection()
    {
        $collection = [];

        $this->assertEquals(0, CourseView::getCollectionByCourse($collection, 1));
    }

    /**
     * Collection without id should return 0.
     *
     * @return void
     */
    public function testCollectionWithoutId()
    {
        $courseLesson = new stdClass();
        $courseLesson->total = 10;
        $collection = [$courseLesson];

        $this->assertEquals(0, CourseView::getCollectionByCourse($collection, 1));
    }

    /**
     * Collection without total should return 0.
     *
     * @return void
     */
    public function testCollectionWithoutTotal()
    {
        $courseLesson = new stdClass();
        $courseLesson->id = 1;
        $collection = [$courseLesson];

        $this->assertEquals(0, CourseView::getCollectionByCourse($collection, 1));
    }

    /**
     * Collection with latest watched lesson.
     *
     * @return void
     */
    public function testLatestWatchedLessonCollection()
    {
        $rs = new stdClass();
        $rs->course_id = 1;
        $rs->course_title = 'Building API';
        $rs->chapter_id = 2;
        $rs->chapter_title = 'Introduction';
        $rs->lesson_id = 3;
        $rs->lesson_title = 'Getting started';
        $collection = [$rs];

        $lesson = CourseView::formatLatestWatchedLesson($collection);
        $this->assertArrayHasKey('id', $lesson['course']);
        $this->assertArrayHasKey('title', $lesson['course']);
        $this->assertArrayHasKey('id', $lesson['chapters']);
        $this->assertArrayHasKey('title', $lesson['chapters']);
        $this->assertArrayHasKey('id', $lesson['lessons']);
        $this->assertArrayHasKey('title', $lesson['lessons']);
    }

    /**
     * Collection without latest watched lesson.
     *
     * @return void
     */
    public function testEmptyLatestWatchedLessonCollection()
    {
        $collection = [];
        $this->assertNull(CourseView::formatLatestWatchedLesson($collection));
    }
}
