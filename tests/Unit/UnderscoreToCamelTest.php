<?php

namespace Tests\Unit;

use App\Http\Middleware\UnderscoreToCamel;
use PHPUnit\Framework\TestCase;

class UnderscoreToCamelTest extends TestCase
{
    protected $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new UnderscoreToCamel();
    }

    /**
     * Transform underscore string to camelCase
     *
     * @return void
     */
    public function testTransformUnderscoreKey()
    {
        $actual = $this->middleware->toCamel('course_id');
        $this->assertEquals('courseId', $actual);
    }

    /**
     * Transform regular string from underscore to camelCase
     *
     * @return void
     */
    public function testTransformRegularKey()
    {
        $actual = $this->middleware->toCamel('title');
        $this->assertEquals('title', $actual);
    }

    /**
     * Transform content array from underscore to camelCase
     *
     * @return void
     */
    public function testTransformArray()
    {
        $content = [
            'chapter' => [
                'attributes' => [
                    'course_id' => 31,
                    'title' => 'Fourth Chapter',
                    'created_at' => '2021-04-01 13:20:49',
                ],
            ],
        ];

        $actual = $this->middleware->transform($content);
        $this->assertArrayHasKey('title', $actual['chapter']['attributes']);
        $this->assertArrayHasKey('courseId', $actual['chapter']['attributes']);
        $this->assertArrayHasKey('createdAt', $actual['chapter']['attributes']);
    }
}
