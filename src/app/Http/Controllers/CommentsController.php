<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\Comment;
use App\Models\Lesson;
use Illuminate\Http\Request;
use App\Http\Views\CommentView;

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
            return $lesson->comments()->whereNull('parent_id')->with('user', 'comments')->get();
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

    /**
     * Display a listing of the comments by authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function user(Request $request)
    {
        $user_id = $request->user()->id;
        $comments = Comment::distinct()
            ->join('lessons', 'comments.lesson_id', '=', 'lessons.id')
            ->join('chapters', 'lessons.chapter_id', '=', 'chapters.id')
            ->join('courses', 'chapters.course_id', '=', 'courses.id')
            ->leftJoin('users', 'comments.user_id', '=', 'users.id')
            ->select('comments.*', 'chapters.id as chapter_id', 'users.name as user_name')
            ->where(function($query) use ($user_id){
                $query->where('comments.user_id', $user_id)
                      ->orWhere('courses.user_id', $user_id);
            })
            ->latest()
            ->get()
            ->toArray();
        return response()->json(['comments' => CommentView::user($comments)]);
    }
}
