<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChapterRequest;
use App\Models\Chapter;
use App\Models\Course;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChaptersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $course_id)
    {
        $course = Course::findOrFail($course_id);
        if ($request->user()->can('view', $course)) {
            return $course->chapters;
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ChapterRequest $request)
    {
        $req = $request->all();
        $user = $request->user();
        $course = Course::where('user_id', $user->id)->findOrFail($req['course_id']);

        $chapters = $course->chapters;
        $req['position'] = count($chapters) > 0 ? ($chapters[count($chapters) - 1])['position'] + 1 : 1;
        $chapter = $course->chapters()->create($req);

        if ($chapter) {
            return response()->json([
                'chapter' => $chapter,
                'message' => trans('messages.general_create'),
            ]);
        }

        return response()->json([
            'error' => trans('messages.general_error'),
        ], 422);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $chapter = Chapter::findOrFail($id);
        if ($request->user()->can('view', $chapter)) {
            return $chapter->load('lessons');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $req = $request->all();
        $user = $request->user();
        $course = Course::where('user_id', $user->id)->findOrFail($req['course_id']);

        $chapter = Chapter::where('course_id', $course->id)->findOrFail($id);

        if ($chapter->update($req)) {
            return response()->json([
                'chapter' => $chapter,
                'message' => trans('messages.general_update'),
            ]);
        }

        return response()->json([
            'error' => trans('messages.general_error'),
        ], 422);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $req = $request->all();
        $user = $request->user();
        $course = Course::where('user_id', $user->id)->findOrFail($req['course_id']);
        $chapter = Chapter::where('course_id', $course->id)->findOrFail($id);

        if ($chapter->delete()) {
            return response()->json([
                'chapter' => $chapter,
                'message' => trans('messages.general_destroy'),
            ]);
        }

        return response()->json([
            'error' => trans('messages.general_error'),
        ], 422);
    }

    /**
     * Reorder the course chapters resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $course_id
     * @return \Illuminate\Http\Response
     */
    public function reorder(Request $request, $course_id)
    {
        $req = $request->all();
        $user = $request->user();
        $course = Course::where('user_id', $user->id)->findOrFail($course_id);
        $table = Chapter::getModel()->getTable();

        $cases = [];
        $ids = [];
        $params = [];

        $position = 0;
        foreach ($req['chapters'] as $id) {
            $position++;
            $id = (int) $id;
            $cases[] = "WHEN {$id} THEN {$position}";
            $ids[] = $id;
        }

        $ids = implode(',', $ids);
        $cases = implode(' ', $cases);
        $params[] = Carbon::now();
        $params[] = $course_id;

        $res = DB::update("UPDATE `{$table}` SET `position` = CASE `id` {$cases} END, `updated_at` = ? WHERE `id` IN ({$ids}) AND course_id = ?", $params);

        if ($res) {
            return response()->json([
                'message' => trans('messages.general_update'),
            ]);
        }

        return response()->json([
            'error' => trans('messages.general_error'),
        ], 422);
    }
}
