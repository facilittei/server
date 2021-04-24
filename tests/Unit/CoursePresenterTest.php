<?php

namespace Tests\Unit;

use App\Http\Presenters\CoursePresenter;
use PHPUnit\Framework\TestCase;
use stdClass;

class CoursePresenterTest extends TestCase
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

        $this->assertEquals(10, CoursePresenter::getCollectionByCourse($collection, 1));
    }

    /**
     * Get zero from an empty collection.
     *
     * @return void
     */
    public function testEmptyCollection()
    {
        $collection = [];

        $this->assertEquals(0, CoursePresenter::getCollectionByCourse($collection, 1));
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

        $this->assertEquals(0, CoursePresenter::getCollectionByCourse($collection, 1));
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

        $this->assertEquals(0, CoursePresenter::getCollectionByCourse($collection, 1));
    }
}
