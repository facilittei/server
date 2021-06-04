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
        $query .= 'WHERE courses.user_id = ? AND courses.deleted_at IS NULL ';

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
        $query .= 'WHERE courses.user_id = ? AND courses.deleted_at IS NULL ';
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
        $query .= 'WHERE lesson_user.user_id = ? AND courses.deleted_at IS NULL ';
        $query .= 'ORDER BY lesson_user.updated_at DESC LIMIT 1 ';

        return $query;
    }

    /**
     * Build a query to get the course status
     *
     * @return string
     */
    public static function buildCourseStats()
    {
        $query = 'SELECT courses.id AS course_id, COUNT(lesson_user.lesson_id) AS watched ';
        $query .= 'FROM courses ';
        $query .= 'INNER JOIN chapters ON courses.id = chapters.course_id ';
        $query .= 'INNER JOIN lessons ON chapters.id = lessons.chapter_id ';
        $query .= 'INNER JOIN lesson_user ON lessons.id = lesson_user.lesson_id ';
        $query .= 'WHERE lesson_user.user_id = ? ';
        $query .= 'GROUP BY lesson_user.lesson_id ';

        return $query;
    }

    /**
     * Build a query to get the course status
     *
     * @return string
     */
    public static function buildCourseLessonStats()
    {
        $query = 'SELECT courses.id AS course_id, COUNT(course_user.course_id) AS total ';
        $query .= 'FROM courses ';
        $query .= 'INNER JOIN chapters ON courses.id = chapters.course_id ';
        $query .= 'INNER JOIN lessons ON chapters.id = lessons.chapter_id ';
        $query .= 'INNER JOIN course_user ON course_user.course_id = courses.id ';
        $query .= 'WHERE course_user.user_id = ? ';
        $query .= 'GROUP BY course_user.course_id ';

        return $query;
    }

    /**
     * Build a query to get the number of comments by courses.
     *
     * @return string
     */
    public static function buildGetTotalComments()
    {
        $query = 'SELECT course_id, COUNT(comments.id) AS total FROM comments ';
        $query .= 'WHERE comments.user_id = ? ';
        $query .= 'GROUP BY course_id ';
        
        return $query;
    }

    /**
     * Build a query to get the number of favorites by courses.
     *
     * @return string
     */
    public static function buildGetTotalFavorites()
    {
        $query = 'SELECT courses.id, COUNT(favorite_lesson.id) AS total FROM favorite_lesson ';
        $query .= 'INNER JOIN lessons ON favorite_lesson.lesson_id = lessons.id ';
        $query .= 'INNER JOIN chapters ON lessons.chapter_id = chapters.id ';
        $query .= 'INNER JOIN courses ON chapters.course_id = courses.id ';
        $query .= 'WHERE favorite_lesson.user_id = ? ';
        $query .= 'GROUP BY courses.id ';
        
        return $query;
    }
}
