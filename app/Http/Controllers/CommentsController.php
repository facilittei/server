<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\Comment;
use App\Models\Lesson;
use Illuminate\Http\Request;

class CommentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $lesson_id)
    {
        $lesson = Lesson::findOrFail($lesson_id);
        if ($request->user()->can('view', $lesson->chapter->course)) {
            return $lesson->comments->load('user');
        }

        return response()->json(['comments' => []]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\CommentRequest;
     * @param  int  $lesson_id
     * @return \Illuminate\Http\Response
     */
    public function store(CommentRequest $request, $lesson_id)
    {
        $req = $request->all();
        $lesson = Lesson::findOrFail($lesson_id);
        $course = $lesson->chapter->course;

        if ($request->user()->can('view', $course)) {
            $req['user_id'] = $request->user()->id;
            $req['course_id'] = $course->id;
            $comment = $lesson->comments()->create($req);

            if ($comment) {
                return response()->json([
                    'comment' => $comment,
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $lesson_id
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CommentRequest $request, $lesson_id, $id)
    {
        $comment = Comment::findOrFail($id);

        if ($request->user()->can('update', $comment)) {

            if ($comment->update(['description' => $request->input('description')])) {
                return response()->json([
                    'comment' => $comment,
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
     * @param  int  $lesson_id
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $lesson_id, $id)
    {
        $comment = Comment::findOrFail($id);

        if ($request->user()->can('delete', $comment)) {
            if ($comment->delete()) {
                return response()->json([
                    'comment' => $comment,
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
}
