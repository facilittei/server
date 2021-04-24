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
        $query .= 'WHERE courses.user_id = ? ';

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
        $query .= 'WHERE courses.user_id = ? ';
        $query .= 'GROUP BY courses.id ';

        return $query;
    }
}
