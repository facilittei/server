<?php

namespace App\Queries;

class CourseQuery
{
    /**
     * Build a query to get the number of lessons taught by a specific teacher.
     *
     * @return string
     */
    public static function buildGetTotalLessons()
    {
        $query = <<<QUERY
        SELECT courses.id, COUNT(lessons.id) total
        FROM lessons
        INNER JOIN chapters ON chapters.id = lessons.chapter_id
        INNER JOIN courses ON courses.id = chapters.course_id
        WHERE (
            courses.user_id = ?
            OR
            courses.id IN (
                SELECT courses.id FROM courses
                INNER JOIN course_user ON courses.id = course_user.course_id
                WHERE course_user.user_id = ?
            )
        )
        AND lessons.deleted_at IS NULL AND chapters.deleted_at IS NULL 
        GROUP BY courses.id;
        QUERY;

        return $query;
    }

    /**
     * Build a query to get the number of favorited lessons by courses.
     *
     * @return string
     */
    public static function buildGetTotalFavorites()
    {
        $query = <<<QUERY
        SELECT courses.id, COUNT(courses.id) AS total FROM courses 
        INNER JOIN chapters ON courses.id = chapters.course_id 
        INNER JOIN lessons ON chapters.id = lessons.chapter_id 
        INNER JOIN favorite_lesson ON lessons.id = favorite_lesson.lesson_id 
        WHERE (
            courses.user_id = ?
            OR
            courses.id IN (
                SELECT courses.id FROM courses
                INNER JOIN chapters ON courses.id = chapters.course_id
                INNER JOIN lessons ON chapters.id = lessons.chapter_id
                INNER JOIN favorite_lesson ON lessons.id = favorite_lesson.lesson_id
                WHERE favorite_lesson.user_id = ?
            )
        ) AND courses.deleted_at IS NULL 
        AND lessons.deleted_at IS NULL AND chapters.deleted_at IS NULL 
        GROUP BY courses.id;
        QUERY;

        return $query;
    }

    /**
     * Build a query to get the number of comments by courses.
     *
     * @return string
     */
    public static function buildGetTotalComments()
    {
        $query = <<<QUERY
        SELECT courses.id, COUNT(courses.id) AS total FROM courses 
        INNER JOIN comments ON courses.id = comments.course_id 
        WHERE (
            courses.user_id = ?
            OR
            courses.id IN (
                SELECT course_user.course_id FROM course_user
                WHERE course_user.user_id = ?
            )
        ) AND courses.deleted_at IS NULL  
        GROUP BY courses.id;
        QUERY;

        return $query;
    }
}
