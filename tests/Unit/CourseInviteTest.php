<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\CourseInvite;

class CourseInviteTest extends TestCase
{
    use \Tests\CreatesApplication;

    protected function setUp(): void
    {
        $this->createApplication();
    }

    /**
     * Generate invite token.
     *
     * @return void
     */
    public function testGenerateToken()
    {
        $courseInvite = new CourseInvite();
        $this->assertIsString($courseInvite->generateToken(1));
    }

    /**
     * Indentify invite token.
     *
     * @return void
     */
    public function testIdentifyToken()
    {
        $courseId = 1;
        $courseInvite = new CourseInvite();
        $invite = $courseInvite->generateToken($courseId);
        $identify = $courseInvite->identifyToken($invite);

        $this->assertArrayHasKey('course_id', $identify);
        $this->assertArrayHasKey('token', $identify);
        $this->assertEquals($courseId, $identify['course_id']);
    }

    /**
     * Indentify an invalid invite token.
     *
     * @return void
     */
    public function testIdentifyTokenInvalid()
    {
        $courseInvite = new CourseInvite();
        $identify = $courseInvite->identifyToken('123456789');

        $this->assertArrayHasKey('error', $identify);
    }
}
