<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Http\Presenters\CoursePresenter;
use App\Queries\CourseQuery;
use App\Queries\OrderQuery;
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
        $queryParams = [$user->id, $user->id];
        $students = DB::select(StudentQuery::buildGetTotal(), $queryParams);
        $studentsByCourse = DB::select(StudentQuery::buildGetTotalByCourse(), $queryParams);
        $lessonsByCourse = DB::select(CourseQuery::buildGetTotalLessons(), $queryParams);
        $lessonsFavoriteByCourse = DB::select(CourseQuery::buildGetTotalFavorites(), $queryParams);
        $lessonsFavoriteByCourseForStudent = DB::select(StudentQuery::buildGetTotalFavorites(), [$queryParams[0]]);
        $commentsByCourse = DB::select(CourseQuery::buildGetTotalComments(), $queryParams);
        $coursesCount = $user->courses()->count();
        $sales = DB::select(OrderQuery::buildGetTotalSales(), [
            OrderStatus::SUCCEED->value,
            $user->id,
        ]);
        $fees = DB::select(OrderQuery::buildGetTotalFees(), [
            OrderStatus::SUCCEED->value,
            $user->id,
        ]);
        $limit = $request->query('limit') ?? $coursesCount;

        $report = [];
        $report['teaching'] = [
            'courses' => $user->getCourseByStatus(true, $limit),
            'drafts' => $user->getCourseByStatus(false, $limit),
            'students' => $students[0]->total,
            'courses_total' => $coursesCount,
            'courses_students' => $studentsByCourse,
            'courses_lessons' => $lessonsByCourse,
            'favorites' => $lessonsFavoriteByCourse,
            'comments' => $commentsByCourse,
            'sales' => $sales,
            'fees' => $fees,
        ];

        $studentLatestLesson = DB::select(StudentQuery::buildGetLatestCompletedLesson(), [$queryParams[0]]);
        $enrolledCount = $user->enrolled()->count();

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
                )->limit($request->query('limit') ?? $enrolledCount)->get(),
            'latest_watched' => $studentLatestLesson,
            'courses_total' => $enrolledCount,
            'courses_students' => $studentsByCourse,
            'courses_lessons' => $lessonsByCourse,
            'favorites' => $lessonsFavoriteByCourseForStudent,
            'comments' => $commentsByCourse,
        ];

        $rs = CoursePresenter::home($report);

        return response()->json($rs);
    }
}
