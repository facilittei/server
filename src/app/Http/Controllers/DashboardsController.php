<?php

namespace App\Http\Controllers;

use App\Http\Presenters\CoursePresenter;
use App\Queries\CourseQuery;
use App\Queries\StudentQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardsController extends Controller
{
    /**
     * Display latest courses, lessons, students, comments
     * and favorites from the autheticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function home(Request $request)
    {
        $user = $request->user();

        $queryParams = [$user->id];
        $students = DB::select(StudentQuery::buildGetTotal(), $queryParams);
        $studentsByCourse = DB::select(StudentQuery::buildGetTotalByCourse(), $queryParams);
        $lessonsByCourse = DB::select(CourseQuery::buildGetTotalLessons(), $queryParams);
        $lessonsFavoritedByCourse = DB::select(CourseQuery::buildGetTotalFavorites(), $queryParams);
        $commentsByCourse = DB::select(CourseQuery::buildGetTotalComments(), $queryParams);

        $report = [];
        $report['teaching'] = [
            'courses' => $user->courses()->select('id', 'title', 'is_published', 'cover', 'created_at', 'updated_at')->limit(2)->get(),
            'students' => $students[0]->total,
            'courses_students' => $studentsByCourse,
            'courses_lessons' => $lessonsByCourse,
            'favorites' => $lessonsFavoritedByCourse,
            'comments' => $commentsByCourse,
        ];

        $studentLastestLesson = DB::select(StudentQuery::buildGetLatestCompletedLesson(), $queryParams);

        $report['learning'] = [
            'courses' => $user->enrolled()
                ->orderBy('courses.updated_at')
                ->select(
                    'courses.id',
                    'courses.title',
                    'courses.slug',
                    'courses.cover',
                    'courses.created_at',
                    'courses.updated_at',
                )->limit(2)->get(),
            'latestWatched' => $studentLastestLesson,
            'courses_students' => $studentsByCourse,
            'courses_lessons' => $lessonsByCourse,
            'favorites' => $lessonsFavoritedByCourse,
            'comments' => $commentsByCourse,
        ];

        return response()->json(CoursePresenter::home($report));
    }
}
