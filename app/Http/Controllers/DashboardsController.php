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
        $students = DB::select(StudentQuery::buildGetTotalByTeacher(), $queryParams);
        $studentsByCourse = DB::select(StudentQuery::buildGetTotalByCourseTeacher(), $queryParams);
        $lessonsByCourse = DB::select(CourseQuery::buildGetTotalLessons(), $queryParams);
        $lessonsFavoritedByCourse = DB::select(CourseQuery::buildGetTotalFavorites(), $queryParams);
        $commentsByCourse = DB::select(CourseQuery::buildGetTotalComments(), $queryParams);

        $report = [];
        $report['teaching'] = [
            'courses' => $user->courses()->select('id', 'title')->get(),
            'students' => $students[0]->total,
            'courses_students' => $studentsByCourse,
            'courses_lessons' => $lessonsByCourse,
            'favorites' => $lessonsFavoritedByCourse,
            'comments' => $commentsByCourse,
        ];

        $report['studing'] = [
            'courses' => $user->enrolled,
            'favorites' => $user->favorited,
        ];

        return response()->json(CoursePresenter::home($report));
    }
}
