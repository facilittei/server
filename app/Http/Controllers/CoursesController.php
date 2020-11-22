<?php

namespace App\Http\Controllers;

use App\Events\EnrollMany;
use App\Http\Requests\CourseRequest;
use App\Models\Course;
use Illuminate\Http\Request;

class CoursesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $request->user()->courses;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CourseRequest $request)
    {
        $user = $request->user();
        $course = $user->courses()->create($request->all());

        if ($course) {
            return response()->json([
                'course' => $course,
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CourseRequest $request, $id)
    {
        $user = $request->user();
        $course = Course::where('user_id', $user->id)->findOrFail($id);

        if ($course->update($request->all())) {
            return response()->json([
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $course = Course::where('user_id', $user->id)->findOrFail($id);

        if ($course->delete()) {
            return response()->json([
                'message' => trans('messages.general_destroy'),
            ]);
        }

        return response()->json([
            'error' => trans('messages.general_error'),
        ], 422);
    }

    /**
     * Upload the course cover.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function upload(Request $request, $id)
    {
        $request->validate([
            'cover' => 'file|mimes:jpeg,png|max:3000',
        ]);

        $user = $request->user();
        $course = Course::where('user_id', $user->id)->findOrFail($id);
        $cover = '';

        if ($course->cover) {
            $file = str_replace('courses/', '', $course->cover);
            $cover = $request->file('cover')->storePubliclyAs('courses', $file, 'public');
        } else {
            $cover = $request->file('cover')->storePublicly('courses', 'public');
        }

        if ($course->update(['cover' => $cover])) {
            return response()->json([
                'cover' => $cover,
                'message' => trans('messages.general_create'),
            ]);
        }

        return response()->json([
            'error' => trans('messages.general_error'),
        ], 422);
    }

    /**
     * Enroll a list of users sent by file
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function enrollMany(Request $request, $id)
    {
        $request->validate([
            'attach' => 'file|mimes:txt,csv|max:512',
        ]);

        $course = Course::where('user_id', $request->user()->id)->findOrFail($id);
        $records = explode(PHP_EOL, file_get_contents($request->file('attach')));
        event(new EnrollMany($course, $records));
    }
}
