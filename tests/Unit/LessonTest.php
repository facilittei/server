<?php

namespace Tests\Unit;

use App\Models\Lesson;
use PHPUnit\Framework\TestCase;
use stdClass;

class LessonTest extends TestCase
{
    /**
     * Retrieve iframe src from Sound Cloud.
     *
     * @return void
     */
    public function testEmbedAudioSoundcloud()
    {
        $embed = '<iframe width="100%" height="300" scrolling="no" frameborder="no" allow="autoplay" src="https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/938056000%3Fsecret_token%3Ds-bZjk2N8phA6&color=%23ff5500&auto_play=false&hide_related=false&show_comments=true&show_user=true&show_reposts=false&show_teaser=true&visual=true"></iframe><div style="font-size: 10px; color: #cccccc;line-break: anywhere;word-break: normal;overflow: hidden;white-space: nowrap;text-overflow: ellipsis; font-family: Interstate,Lucida Grande,Lucida Sans Unicode,Lucida Sans,Garuda,Verdana,Tahoma,sans-serif;font-weight: 100;"><a href="https://soundcloud.com/user-236930302" title="Andrew Esteves" target="_blank" style="color: #cccccc; text-decoration: none;">Andrew Esteves</a> · <a href="https://soundcloud.com/user-236930302/audio-test/s-bZjk2N8phA6" title="Audio - Test" target="_blank" style="color: #cccccc; text-decoration: none;">Audio - Test</a></div>';

        $audio = 'https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/938056000%3Fsecret_token%3Ds-bZjk2N8phA6&color=%23ff5500&auto_play=false&hide_related=false&show_comments=true&show_user=true&show_reposts=false&show_teaser=true&visual=true';

        $lesson = new Lesson();
        $lesson->__set('audio', $embed);
        $this->assertEquals($audio, $lesson->audio);
    }

    /**
     * Retrieve iframe src from YouTube.
     *
     * @return void
     */
    public function testEmbedVideoYoutube()
    {
        $embed = '<iframe width="560" height="315" src="https://www.youtube.com/embed/rvNk8DbQlCI" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';

        $video = 'https://www.youtube.com/embed/rvNk8DbQlCI';

        $lesson = new Lesson();
        $lesson->__set('video', $embed);
        $this->assertEquals($video, $lesson->video);
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

        $expected = [
            [
                'id' => 5,
                'title' => 'Hic similique nam quibusdam.',
                "chapter" => [
                    'id' => 31,
                    'title' => 'Lorem ipsum'
                ],
            ],
        ];

        $actual = Lesson::formatResultWithChapter([$res]);
        $this->assertEquals($expected, $actual);
    }
}
