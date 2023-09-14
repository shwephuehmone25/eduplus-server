<?php

use Illuminate\Http\Request;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsTeacher;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LikeController;
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

Route::middleware(['auth:sanctum', 'IsTeacher'])->group(function () {
    /*Teacher Routes*/
    Route::get('/teacher/getAssigncourses/{teacher}', [TeacherController::class, 'getAssignCourses']);
    Route::get('/teacher/show', [TeacherController::class, 'showProfile']);
    });

/*User Routes*/
Route::post('/phone/register', [AuthController::class, 'getStart']);
Route::post('/otp/verify/{userId}', [AuthController::class, 'verify']);
Route::post('/user/create/{userId}', [AuthController::class, 'createUser']);
Route::post('/student/login', [LoginController::class, 'loginAsStudent']);

/**Common Routes */
Route::middleware('auth:sanctum')->group(function(){
    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('/courses/{id}', [CourseController::class, 'showCourseDetails']);
    Route::get('/courses/purchase/{courseId}', [CourseController::class, 'buyCourses']);
    Route::get('/mycourses/show/{id}', [CourseController::class, 'getMyCourse']);
    Route::get('/meetings', [MeetingController::class, 'getMeetingLists']);
    Route::post('/courses/enroll/{courseId}', [CourseController::class, 'enroll']);
    Route::get('/get/coursesbycategory/{categoryName}', [CourseController::class, 'getCoursesbyCategory']);
    Route::get('/get/purchasedCourses/{categoryName}', [CourseController::class, 'getPurchasedCoursesByCategory']);
    Route::get('/get/likedCourses/{userId}',[CourseController::class,'getLikedCourses']);
    Route::post('/like', [LikeController::class, 'like']);
    Route::post('/unlike', [LikeController::class,'unlike']);
});

Route::post('/check/user', [AccountController::class,'checkUserExists']);

/**Test Routes */
Route::post('/send-message', [AuthController::class, 'sendMessage']);

/* Guard routes*/
Route::post('admin/register', [AuthController::class, 'registerAsAdmin']);
Route::post('admin/login', [LoginController::class, 'loginAsAdmin']);
Route::post('teacher/login', [LoginController::class, 'loginAsTeacher']);

Route::get('auth/google', [AccountController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AccountController::class, 'handleGoogleCallback']);
Route::post('/google/login', [AccountController::class, 'googleLogin']);
Route::post('/meeting/create', [MeetingController::class,'create']);

Route::get('/videos', [ VideoController::class, 'index']);

/**Admin Routes*/
Route::middleware(['auth:sanctum', 'IsAdmin'])->group(function() {

    /**Course Routes */
    Route::post('/courses', [CourseController::class, 'store']);
    Route::post('/courses/{id}', [CourseController::class, 'update']);
    Route::delete('/courses/{id}', [CourseController::class, 'destroy']);
    Route::get('/courses/restore/{id}', [CourseController::class, 'restore']);
    Route::get('restoreAll', [CourseController::class, 'restoreAll']);

    /**Video Routes */
    Route::post('/video/create', [ VideoController::class, 'store']);
    Route::delete('/videos/{id}', [VideoController::class, 'destroy']);

    /**Category Routes */
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/category/{id}', [CategoryController::class, 'getCategoryDetails']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::post('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    /**Subcategory routes*/
    // Route::get('/subcategories', [SubcategoryController::class,'index']);
    // Route::get('/subcategory/{id}', [SubcategoryController::class, 'getSubcategoryDetails']);
    // Route::post('/subcategories', [SubcategoryController::class,'store']);
    // Route::post('/subcategories/{id}', [SubcategoryController::class,'update']);
    // Route::delete('/subcategories/{id}', [SubcategoryController::class,'destroy']);
    // Route::get('/get/subcategories', [SubcategoryController::class, 'getSubcategoriesByCategory']);

    /**Class routes */
    Route::get('/classes', [ClassController::class, 'index']);
    Route::get('/class/{id}', [ClassController::class, 'getClassDetails']);
    Route::post('/classes', [ClassController::class, 'store']);
    Route::post('/classes/{class}', [ClassController::class, 'update']);
    Route::delete('/classes/{class}', [ClassController::class, 'destroy']);

    /**Level Routes */
    Route::get('/levels', [LevelController::class, 'index']);
    Route::get('/level/{id}', [LevelController::class, 'showLevelDetails']);
    Route::post('/levels', [LevelController::class, 'store']);
    Route::post('/levels/{level}', [LevelController::class, 'update']);
    Route::delete('/levels/{level}', [LevelController::class, 'destroy']);

    /**Section routes */
    Route::get('/sections', [SectionController::class, 'index']);
    Route::get('/section/{id}', [SectionController::class, 'getSectionDetails']);
    Route::post('/sections', [SectionController::class, 'store']);
    Route::post('/sections/{section}', [SectionController::class, 'update']);
    Route::delete('/sections/{section}', [SectionController::class, 'destroy']);

    /**Teacher routes */
    Route::post('/teachers', [TeacherController::class, 'store']);
    Route::post('/teachers/{teacher}', [TeacherController::class, 'update']);
    Route::delete('/teachers/{teacher}', [TeacherController::class, 'destroy']);
    Route::get('/teachers', [TeacherController::class, 'getAllTeachers']);
});
