<?php

namespace Tests\Unit;

use App\Http\Middleware\CamelToUnderscore;
use PHPUnit\Framework\TestCase;

class CamelToUnderscoreTest extends TestCase
{
    protected $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new CamelToUnderscore();
    }

    /**
     * Transform camelCase string to underscore
     *
     * @return void
     */
    public function testTransformUnderscoreKey()
    {
        $actual = $this->middleware->toUnderscore('courseId');
        $this->assertEquals('course_id', $actual);
    }

    /**
     * Transform regular string from camelCase to underscore
     *
     * @return void
     */
    public function testTransformRegularKey()
    {
        $actual = $this->middleware->toUnderscore('title');
        $this->assertEquals('title', $actual);
    }

    /**
     * Transform content array from camelCase to underscore
     *
     * @return void
     */
    public function testTransformArray()
    {
        $content = [
            'chapter' => [
                'courseId' => 31,
                'title' => 'Fourth Chapter',
                'createdAt' => '2021-04-01 13:20:49',
            ],
        ];

        $actual = $this->middleware->transform($content);
        $this->assertArrayHasKey('title', $actual['chapter']);
        $this->assertArrayHasKey('course_id', $actual['chapter']);
        $this->assertArrayHasKey('created_at', $actual['chapter']);
    }
}
