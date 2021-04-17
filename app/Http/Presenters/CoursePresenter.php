<?php

namespace App\Http\Presenters;

class CoursePresenter
{
    /**
     * Show user home dashboard report.
     *
     * @param array $report
     * @return array
     */
    public static function home(array $report)
    {
        $data = [];

        if (isset($report['teaching'])) {
            $teach = $report['teaching'];
            $courses = $teach['courses'];

            $teaching = [];
            $teaching['courses'] = [];

            if (count($courses)) {
                $teaching['students'] = $teach['students'];
            }

            for ($i = 0; $i < count($courses); $i++) {
                $course = $courses[$i];

                $teaching['courses'][] = [
                    'id' => $course->id,
                    'title' => $course->title,
                    'students' => CoursePresenter::getCollectionByCourse($teach['courses_students'], $course->id),
                    'lessons' => CoursePresenter::getCollectionByCourse($teach['courses_lessons'], $course->id),
                    'favorites' => CoursePresenter::getCollectionByCourse($teach['favorites'], $course->id),
                    'comments' => CoursePresenter::getCollectionByCourse($teach['comments'], $course->id),
                ];
            }

            $data['teaching'] = $teaching;
        }

        return $data;
    }

    /**
     * Get the course related total count.
     *
     * @param array $collection
     * @param int $courseId
     * @return int
     */
    public static function getCollectionByCourse($collection, $courseId)
    {
        $result = 0;

        for ($i = 0; $i < count($collection); $i++) {
            if (!isset($collection[$i]->id)) {
                continue;
            }

            if ($collection[$i]->id === $courseId) {
                return $collection[$i]->total ?? $result;
            }
        }

        return $result;
    }
}
