<?php

namespace App\Http\Controllers;

use App\Http\Requests\LessonRequest;
use App\Models\Chapter;
use App\Models\Lesson;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LessonsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $chapter_id
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $chapter_id)
    {
        $chapter = Chapter::findOrFail($chapter_id);
        if ($request->user()->can('view', $chapter)) {
            $user = $request->user();
            $lessons = $chapter->lessons;
            $lessonsId = $lessons->pluck('id')->toArray();

            return response()->json([
                'lessons' => $lessons,
                'watched' => $user->watched->whereIn('id', $lessonsId)->pluck('id')->all(),
                'favorited' => $user->favorited->whereIn('id', $lessonsId)->pluck('id')->all(),
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\LessonRequest;
     * @param  int  $chapter_id
     * @return \Illuminate\Http\Response
     */
    public function store(LessonRequest $request, $chapter_id)
    {
        $req = $request->all();
        $chapter = Chapter::findOrFail($chapter_id);

        if ($request->user()->can('update', $chapter)) {
            $lessons = $chapter->lessons;
            $req['position'] = count($lessons) > 0 ? ($lessons[count($lessons) - 1])['position'] + 1 : 1;
            $lesson = $chapter->lessons()->create($req);

            if ($lesson) {
                return response()->json([
                    'lesson' => $lesson,
                    'message' => trans('messages.general_create'),
                ]);
            }

            return response()->json([
                'error' => trans('messages.general_error'),
            ], 422);
        }

        return response()->json([
            'error' => trans('auth.unauthorized'),
        ], 401);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $chapter_id
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $chapter_id, $id)
    {
        $lesson = Lesson::where('chapter_id', $chapter_id)->findOrFail($id);

        $previous = Lesson::where('id', '<', $lesson->id)->max('id');
        $next = Lesson::where('id', '>', $lesson->id)->min('id');

        if ($request->user()->can('view', $lesson)) {
            $user = $request->user();

            return response()->json([
                'previous' => $previous,
                'current' => $lesson,
                'next' => $next,
                'watched' => $user->watched->where('id', $id)->pluck('id')->first(),
                'favorited' => $user->favorited->where('id', $id)->pluck('id')->first(),
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\LessonRequest;
     * @param  int  $chapter_id
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(LessonRequest $request, $chapter_id, $id)
    {
        $req = $request->all();
        $lesson = Lesson::where('chapter_id', $chapter_id)->findOrFail($id);

        if ($request->user()->can('update', $lesson)) {

            if ($lesson->update($req)) {
                return response()->json([
                    'lesson' => $lesson,
                    'message' => trans('messages.general_update'),
                ]);
            }

            return response()->json([
                'error' => trans('messages.general_error'),
            ], 422);
        }

        return response()->json([
            'error' => trans('auth.unauthorized'),
        ], 401);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $chapter_id
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $chapter_id, $id)
    {
        $lesson = Lesson::where('chapter_id', $chapter_id)->findOrFail($id);

        if ($request->user()->can('delete', $lesson)) {
            if ($lesson->delete()) {
                return response()->json([
                    'lesson' => $lesson,
                    'message' => trans('messages.general_destroy'),
                ]);
            }

            return response()->json([
                'error' => trans('messages.general_error'),
            ], 422);
        }

        return response()->json([
            'error' => trans('auth.unauthorized'),
        ], 401);
    }

    /**
     * Reorder the course chapters resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $chapter_id
     * @return \Illuminate\Http\Response
     */
    public function reorder(Request $request, $chapter_id)
    {
        $req = $request->all();
        $chapter = Chapter::findOrFail($chapter_id);

        if ($request->user()->can('update', $chapter)) {
            $table = Lesson::getModel()->getTable();

            $cases = [];
            $ids = [];
            $params = [];

            $position = 0;
            foreach ($req['lessons'] as $id) {
                $position++;
                $id = (int) $id;
                $cases[] = "WHEN {$id} THEN {$position}";
                $ids[] = $id;
            }

            $ids = implode(',', $ids);
            $cases = implode(' ', $cases);
            $params[] = Carbon::now();
            $params[] = $chapter_id;

            $res = DB::update("UPDATE `{$table}` SET `position` = CASE `id` {$cases} END, `updated_at` = ? WHERE `id` in ({$ids}) AND chapter_id = ?", $params);

            if ($res) {
                return response()->json([
                    'message' => trans('messages.general_update'),
                ]);
            }

            return response()->json([
                'error' => trans('messages.general_error'),
            ], 422);
        }

        return response()->json([
            'error' => trans('auth.unauthorized'),
        ], 401);
    }

    /**
     * Lessons has been watched.
     *
     * @param  \App\Http\Requests\Request;
     * @param  int  $chapter_id
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function watched(Request $request, $chapter_id, $id)
    {
        $chapter = Chapter::findOrFail($chapter_id);

        if ($request->user()->can('view', $chapter)) {
            $lesson = Lesson::where('chapter_id', $chapter_id)->findOrFail($id);

            if ($request->user()->watched()->toggle($lesson)) {
                return response()->json([
                    'lesson' => $lesson,
                    'message' => trans('messages.general_update'),
                ]);
            }

            return response()->json([
                'error' => trans('messages.general_error'),
            ], 422);
        }

        return response()->json([
            'error' => trans('auth.unauthorized'),
        ], 401);
    }

    /**
     * Lessons has been favorited.
     *
     * @param  \App\Http\Requests\Request;
     * @param  int  $chapter_id
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function favorited(Request $request, $chapter_id, $id)
    {
        $chapter = Chapter::findOrFail($chapter_id);

        if ($request->user()->can('view', $chapter)) {
            $lesson = Lesson::where('chapter_id', $chapter_id)->findOrFail($id);

            if ($request->user()->favorited()->toggle($lesson)) {
                return response()->json([
                    'lesson' => $lesson,
                    'message' => trans('messages.general_update'),
                ]);
            }

            return response()->json([
                'error' => trans('messages.general_error'),
            ], 422);
        }

        return response()->json([
            'error' => trans('auth.unauthorized'),
        ], 401);
    }
}
