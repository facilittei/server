<?php

use App\Http\Controllers\ChaptersController;
use App\Http\Controllers\CommentsController;
use App\Http\Controllers\CoursesController;
use App\Http\Controllers\DashboardsController;
use App\Http\Controllers\LessonsController;
use App\Http\Controllers\ProfilesController;
use \App\Http\Controllers\UsersController;
use \App\Http\Controllers\CourseInvitesController;
use \App\Http\Controllers\CommonsController;
use App\Http\Controllers\GroupInvitesController;
use App\Http\Controllers\GroupsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('healthcheck', [CommonsController::class, 'healthcheck']);

Route::group(['middleware' => ['locale']], function () {
    Route::post('/register', [UsersController::class, 'register']);
    Route::post('/login', [UsersController::class, 'login']);
    Route::post('/groups', [GroupsController::class, 'create']);
    Route::get('/groups', [GroupsController::class, 'list']);
    Route::delete('/groups/{group_id}', [GroupsController::class, 'destroy']);
    Route::post('/group-invites', [GroupInvitesController::class, 'invite']);
    Route::post('/group-invites/{token}', [GroupInvitesController::class, 'accept']);
    Route::get('/verify/{hash}', [UsersController::class, 'verify']);
    Route::post('/recover', [UsersController::class, 'recover']);
    Route::post('/reset', [UsersController::class, 'reset'])->name('password.reset');
    Route::post('/invites/{token}', [CourseInvitesController::class, 'accept']);

    Route::group(['middleware' => ['auth:sanctum', 'verified', 'underscore', 'camelcase']], function () {
        Route::get('/users', [UsersController::class, 'show']);
        Route::put('/users', [UsersController::class, 'update']);
        Route::get('/profiles', [ProfilesController::class, 'show']);
        Route::post('/profiles', [ProfilesController::class, 'store']);
        Route::put('/profiles', [ProfilesController::class, 'update']);
        Route::delete('/profiles', [ProfilesController::class, 'destroy']);
        Route::post('/profiles/upload', [ProfilesController::class, 'upload']);
        Route::delete('/logout', [UsersController::class, 'logout']);
        Route::patch('/courses/{course_id}/chapters/reorder', [ChaptersController::class, 'reorder']);
        Route::get('/courses/{course_id}/chapters', [ChaptersController::class, 'index']);
        Route::resource('/chapters', ChaptersController::class)->except(['index', 'create', 'edit']);
        Route::get('/courses/enrolled/stats', [CoursesController::class, 'stats']);
        Route::get('/courses/enrolled', [CoursesController::class, 'enrolled']);
        Route::get('/courses/{id}/students', [CoursesController::class, 'students']);
        Route::get('/courses/{id}/invites', [CoursesController::class, 'invites']);
        Route::get('/courses/{id}/favorites', [CoursesController::class, 'favorites']);
        Route::post('/courses/{id}/upload', [CoursesController::class, 'upload']);
        Route::post('/courses/{id}/enroll-many', [CoursesController::class, 'enrollMany']);
        Route::post('/courses/{id}/enroll', [CoursesController::class, 'enroll']);
        Route::delete('/courses/{id}/annul', [CoursesController::class, 'annul']);
        Route::delete('/courses/{id}/annul-many', [CoursesController::class, 'annulMany']);
        Route::delete('/courses/{id}/annul-invites', [CoursesController::class, 'annulInvites']);
        Route::resource('/courses', CoursesController::class)->except(['create', 'edit']);
        Route::get('/lessons/search', [LessonsController::class, 'search']);
        Route::post('/chapters/{chapter_id}/lessons/{id}/favorited', [LessonsController::class, 'favorited']);
        Route::post('/chapters/{chapter_id}/lessons/{id}/watched', [LessonsController::class, 'watched']);
        Route::patch('/chapters/{chapter_id}/lessons/reorder', [LessonsController::class, 'reorder']);
        Route::resource('/chapters/{chapter_id}/lessons', LessonsController::class)->except(['create', 'edit']);
        Route::get('/comments', [CommentsController::class, 'user']);
        Route::resource('/lessons/{lesson_id}/comments', CommentsController::class)->except(['show', 'create', 'edit']);

        Route::get('home', [DashboardsController::class, 'home']);
    });
});
