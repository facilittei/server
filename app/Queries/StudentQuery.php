<?php

namespace App\Queries;

class StudentQuery
{
    /**
     * Build a query to get the number of students by a specific teacher.
     *
     * @return string
     */
    public static function buildGetTotalByTeacher()
    {
        $query = 'SELECT COUNT(DISTINCT course_user.user_id) AS total FROM course_user ';
        $query .= 'INNER JOIN courses ON courses.id = course_user.course_id ';
        $query .= 'WHERE courses.user_id = ? AND courses.deleted_at = NULL ';

        return $query;
    }

    /**
     * Build a query to get the number of students by course
     * and a specific teacher.
     *
     * @return string
     */
    public static function buildGetTotalByCourseTeacher()
    {
        $query = 'SELECT courses.id, COUNT(course_user.user_id) AS total FROM course_user ';
        $query .= 'INNER JOIN courses ON courses.id = course_user.course_id ';
        $query .= 'WHERE courses.user_id = ? AND courses.deleted_at = NULL ';
        $query .= 'GROUP BY courses.id ';

        return $query;
    }

    /**
     * Build a query to get the latest lesson completed.
     *
     * @return string
     */
    public static function buildGetLatestCompletedLesson()
    {
        $query = 'SELECT courses.id AS course_id, courses.title AS course_title, ';
        $query .= 'chapters.id AS chapter_id, chapters.title as chapter_title, ';
        $query .= 'lessons.id AS lesson_id, lessons.title as lesson_title ';
        $query .= 'FROM courses ';
        $query .= 'INNER JOIN chapters ON courses.id = chapters.course_id ';
        $query .= 'INNER JOIN lessons ON chapters.id = lessons.chapter_id ';
        $query .= 'INNER JOIN lesson_user ON lessons.id = lesson_user.lesson_id ';
        $query .= 'WHERE lesson_user.user_id = ? AND courses.deleted_at = NULL ';
        $query .= 'ORDER BY lesson_user.updated_at DESC LIMIT 1 ';

        return $query;
    }
}
