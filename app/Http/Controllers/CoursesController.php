<?php

namespace App\Http\Controllers;

use App\Events\EnrollMany;
use App\Http\Requests\CourseRequest;
use App\Mail\CourseEnrollManyMail;
use App\Mail\UserConfirmationMail;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CoursesController extends Controller
{
    /**
     * Display a listing of the resource (teacher).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $request->user()->courses;
    }

    /**
     * Display a listing of the enrolled classes (student).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function enrolled(Request $request)
    {
        return $request->user()->enrolled;
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
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        $user = $request->user();

        if ($user->can('view', $course)) {
            return $course;
        }

        return response()->json([
            'error' => trans('auth.unauthorized'),
        ], 401);
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

        return response()->json(['message' => trans('messages.queue_enroll_many')]);
    }

    /**
     * Enroll the specified resource to course.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function enroll(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
        ]);

        $course = Course::where('user_id', $request->user()->id)->findOrFail($id);
        $student = User::where('email', $request->input('email'))->first();

        if (!$student) {
            $req = $request->all();
            $req['password'] = bcrypt(Str::random(10));
            $student = User::create($req);
            Mail::to($student->email)->queue(new UserConfirmationMail($student));
        }

        if ($course->students()->syncWithoutDetaching($student->id)) {
            Mail::to($student->email)->queue(new CourseEnrollManyMail($course, $student));
            return response()->json(['message' => trans('messages.general_success')]);
        }

        return response()->json(['message' => trans('messages.general_error')], 422);
    }

    /**
     * Annul the specified resource from course.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function annul(Request $request, $id)
    {
        $course = Course::where('user_id', $request->user()->id)->findOrFail($id);

        if ($course->students()->detach($request->input('user_id'))) {
            return response()->json(['message' => trans('messages.general_destroy')]);
        }

        return response()->json(['message' => trans('messages.general_error')], 422);
    }

    /**
     * Display a listing of the resource (teacher).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function students(Request $request, $id)
    {
        $course = Course::where('user_id', $request->user()->id)->findOrFail($id);
        return $course->students;
    }
}