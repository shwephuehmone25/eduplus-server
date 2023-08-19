<?php

use Illuminate\Http\Request;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsTeacher;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\LevelController;
use App\Http\Controllers\Admin\VideoController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Teacher\MeetingController;
use App\Http\Controllers\Admin\SubcategoryController;
use App\Http\Controllers\Teacher\AccountController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Route::middleware(['IsTeacher::class'])->group(function () {
    /*Teacher Routes*/
    Route::get('auth/google', [AccountController::class, 'redirectToGoogle']);
    Route::get('/auth/google/callback', [AccountController::class, 'handleGoogleCallback']);
    Route::post('/test/google', [AccountController::class, 'test']);
    Route::post('/google/login', [AccountController::class, 'googleLogin']);
    Route::post('/meeting/create/{course_id}', [MeetingController::class,'create']);
    Route::post('teacher/login', [LoginController::class, 'loginAsTeacher']);
    //  });

/*User Routes*/
Route::post('/phone/register', [AuthController::class, 'getStart']);
Route::post('/otp/verify', [AuthController::class, 'verify']);
Route::post('/user/create', [AuthController::class, 'createUser']);
Route::post('/student/login', [LoginController::class, 'loginAsStudent']);

/**Admin Routes*/
// Route::middleware([IsAdmin::class])->group(function() {
    Route::post('admin/register', [AuthController::class, 'registerAsAdmin']);
    Route::post('admin/login', [LoginController::class, 'loginAsAdmin']);

    /**Course Routes */
    Route::get('/courses', [CourseController::class, 'index']);
    Route::post('/courses', [CourseController::class, 'store']);
    Route::put('/courses/{id}', [CourseController::class, 'update']);
    Route::delete('/courses/{id}', [CourseController::class, 'destroy']);

    /**Video Routes */
    Route::get('/videos', [ VideoController::class, 'index']);
    Route::post('/video/create', [ VideoController::class, 'store']);
    Route::delete('/videos/{id}', [VideoController::class, 'destroy']);

    /**Category Routes */
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    /**Subategory routes*/
    Route::get('/subcategories', [SubcategoryController::class,'index']);
    Route::post('/subcategories', [SubcategoryController::class,'store']);
    Route::put('/subcategories/{id}', [SubcategoryController::class,'update']);
    Route::delete('/subcategories/{id}', [SubcategoryController::class,'destroy']);
    Route::get('/get/subcategories', [SubcategoryController::class, 'getSubcategoriesByCategory']);

    /**Class routes */
    Route::get('/classes', [ClassController::class, 'index']);
    Route::post('/classes', [ClassController::class, 'store']);
    Route::put('/classes/{class}', [ClassController::class, 'update']);
    Route::delete('/classes/{class}', [ClassController::class, 'destroy']);

    /**Level Routes */
    Route::get('/levels', [LevelController::class, 'index']);
    Route::post('/levels', [LevelController::class, 'store']);
    Route::put('/levels/{level}', [LevelController::class, 'update']);
    Route::delete('/levels/{level}', [LevelController::class, 'destroy']);

    /**Section routes */
    Route::get('/sections', [SectionController::class, 'index']);
    Route::post('/sections', [SectionController::class, 'store']);
    Route::put('/sections/{section}', [SectionController::class, 'update']);
    Route::delete('/sections/{section}', [SectionController::class, 'destroy']);

    /**Teacher routes */
    Route::post('/teachers', [TeacherController::class, 'store']);
    Route::put('/teachers/{teacher}', [TeacherController::class, 'update']);
    Route::delete('/teachers/{teacher}', [TeacherController::class, 'destroy']);
    Route::get('/teachers', [TeacherController::class, 'getAllTeachers']);
    Route::get('/teacher/getAssigncourses', [TeacherController::class, 'getAssignCourses']);
// });
