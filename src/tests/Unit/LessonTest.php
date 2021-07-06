<?php

namespace Tests\Unit;

use App\Models\Lesson;
use PHPUnit\Framework\TestCase;
use stdClass;

class LessonTest extends TestCase
{
    
    use \Tests\CreatesApplication;

    protected function setUp(): void
    {
        $this->createApplication();
    }

    /**
     * Nesting chapter in lesson.
     *
     * @return void
     */
    public function testNestingChapterInLesson()
    {
        $res = new stdClass();
        $res->lesson_id = 5;
        $res->lesson_title = 'Hic similique nam quibusdam.';
        $res->chapter_id = 31;
        $res->chapter_title = 'Lorem ipsum';
        $res->course_id = 31;
        $res->course_title = 'Lorem ipsum';

        $expected = [
            [
                'id' => 5,
                'title' => 'Hic similique nam quibusdam.',
                "chapter" => [
                    'id' => 31,
                    'title' => 'Lorem ipsum'
                ],
                "course" => [
                    'id' => 31,
                    'title' => 'Lorem ipsum'
                ],
            ],
        ];

        $actual = Lesson::formatResultWithChapter([$res]);
        $this->assertEquals($expected, $actual);
    }

    public function testYoutubeLink() {
        $url = 'https://www.youtube.com/watch?v=TbCECWFdNBg';
        $expected = 'https://www.youtube.com/embed/TbCECWFdNBg';
        $lesson = new Lesson();

        $lesson->__set('video', $url);
        $this->assertEquals($expected, $lesson->video);
    }

    public function testYoutubeLinkWithoutVideoOnQuery() {
        $url = 'https://www.youtube.com/watch';
        $expected = 'https://www.youtube.com/embed/TbCECWFdNBg';
        $lesson = new Lesson();

        $lesson->__set('video', $url);
        $this->assertNull($lesson->video);
    }

    public function testYoutubeLinkWithMoreParamsOnQuery() {
        $url = 'https://www.youtube.com/watch?v=TbCECWFdNBg&something=else';
        $expected = 'https://www.youtube.com/embed/TbCECWFdNBg';
        $lesson = new Lesson();

        $lesson->__set('video', $url);
        $this->assertEquals($expected, $lesson->video);
    }
}
