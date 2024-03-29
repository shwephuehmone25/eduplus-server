<?php

use Illuminate\Http\Request;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsTeacher;
use App\Http\Middleware\IsSuperAdmin;
use App\Http\Middleware\CheckRole;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\User\LikeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\LevelController;
use App\Http\Controllers\Admin\VideoController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\NoticeController;
use App\Http\Controllers\Teacher\MeetingController;
use App\Http\Controllers\Admin\SubcategoryController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VarietyController;
use App\Http\Controllers\Admin\RankController;
use App\Http\Controllers\Teacher\AccountController;
use App\Http\Controllers\Admin\AllocationController;
use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\Admin\GradeController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\TypeController;
use App\Http\Controllers\Admin\OptionController;
use App\Http\Controllers\Admin\CollectionController;
use App\Http\Controllers\Admin\ResultController;
use App\Http\Controllers\User\PlacementTestController;
use App\Http\Controllers\User\WishlistController;
use App\Http\Controllers\Admin\AdminController;
use Google\Service\Adsense\Row;
use Google\Service\AlertCenter\UserChanges;
use App\Http\Controllers\User\PaymentController;

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

// Route::middleware(['auth:sanctum', 'IsTeacher'])->group(function () {
    /*Teacher Routes*/
    Route::get('/teacher/getAssigncourses/{teacher}', [TeacherController::class, 'getAssignCourses']);
    Route::get('/teacher/show', [TeacherController::class, 'showProfile']);
    Route::post('/teacher/editProfile/{teacherId}', [TeacherController::class, 'updateProfile']);
    // });

/*User Routes*/
Route::post('/phone/register', [AuthController::class, 'getStart']);
Route::post('/otp/verify/{phoneId}', [AuthController::class, 'verify']);
Route::post('/user/create/{phoneId}', [AuthController::class, 'createUser']);

Route::post('/student/login', [LoginController::class, 'loginAsStudent'])->middleware('checkUserStatus');

Route::get('/courses/{id}/{teacher_type}', [CourseController::class, 'showCourseDetailsWithType']);
Route::get('/courses/{id}', [CourseController::class, 'showCourseDetails']);
Route::get('/modules', [RankController::class, 'index']);
Route::get('/module/{id}', [RankController::class, 'showModuleDetails']);
Route::get('/sections', [SectionController::class, 'index']);
Route::get('/section/{id}', [SectionController::class, 'getSectionDetails']);
Route::get('/levels/{categoryId}', [LevelController::class, 'getCourseByCategoryId']);
Route::get('/level/{id}', [LevelController::class, 'showLevelDetails']);
Route::get('/users/count', [UserController::class, 'countVerifiedUsers']);
Route::get('/totalCourses/count', [CourseController::class, 'countCourses']);
Route::get('/totalTeachers/count', [CourseController::class, 'countCourses']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/get/coursesbycategory/{categoryName}', [CourseController::class, 'getCoursesbyCategory']);

/**Common Routes*/
Route::middleware('auth:sanctum')->group(function(){
    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('/purchase/course/{allocationId}', [CourseController::class, 'buyCourses']);
    Route::get('/mycourses/show/{userId}', [CourseController::class, 'getMyCourses']);
    Route::get('/meetings', [MeetingController::class, 'getMeetingLists']);
    Route::post('/courses/enroll/{courseId}', [CourseController::class, 'enroll']);
    Route::get('/get/purchasedCourses/{categoryName}', [CourseController::class, 'getPurchasedCoursesByCategory']);
    Route::get('/get/likedCourses/{userId}',[CourseController::class,'getLikedCourses']);
    Route::post('/like', [LikeController::class, 'like']);
    Route::post('/unlike', [LikeController::class,'unlike']);
    Route::post('/changePassword/{userId}', [UserController::class, 'changePassword']);
    Route::post('/verifyCurrentPhone/{userId}', [UserController::class, 'verifyCurrentPhone']);
    Route::post('/updatePhone/{userId}', [UserController::class, 'updatePhone']);

    /**payment routes*/
    Route::post('/checkout', [PaymentController::class, 'checkout']);
    Route::post('/process', [PaymentController::class, 'process']);
    Route::post('/submit', [PaymentController::class, 'submit']);
    Route::get('/enquiry/{orderId}', [PaymentController::class, 'enquiry']);
    Route::get('/auth/aya/callback', [PaymentController::class, 'handleCallback']);

    // Route::get('/get/likedCourses/{userId}',[CourseController::class,'getLikedCourses']);

    Route::get('/test/{grade_id}',[PlacementTestController::class, 'getQuestionsByGrades']);
    Route::post('test',[PlacementTestController::class, 'store']);
    Route::get('/myresult/get/{resultId}', [ResultController::class, 'show']);
    Route::post('like', [LikeController::class,'like']);
    Route::delete('unlike', [LikeController::class,'unlike']);
    Route::post('course/addToWishlist', [WishlistController::class, 'addToWishlist']);
    Route::post('course/removeFromWishlist', [WishlistController::class, 'removeFromWishlist']);
    Route::get('/getAllWishlists/{userId}',[WishlistController::class,'getAllWishlists']);
    Route::get('/get/wishlist/{userId}',[WishlistController::class,'getWishlist']);

    Route::post('/user/uploadProfile', [UserController::class, 'uploadProfile']);
    Route::get('/user/showProfile/{userId}', [UserController::class, 'showUserDetails']);
    Route::post('/user/editProfile/{userId}', [UserController::class, 'editProfile']);
    Route::get('/get/{userId}/purchasedcourseDetails/{allocationId}', [CourseController::class, 'getPurchasedCoursesDetails']);
    Route::get('/get/moduleTwoDetails/{moduleName}', [CourseController::class, 'getModuleTwoDetails']);
    Route::get('/schools', [SchoolController::class, 'index']);
    Route::get('/grades', [GradeController::class, 'index']);
    Route::get('/school/grades/{schoolId}', [GradeController::class, 'gradeBySchool']);
    Route::get('/types', [TypeController::class, 'index']);
    Route::get('/options', [OptionController::class, 'index']);
    Route::get('/get/questionsbyGrade/{gradeName}', [QuestionController::class, 'getQuestionsByGrade']);
    Route::get('get/questions/{question}', [QuestionController::class, 'showQuestionDetails']);
    Route::post('/resetPassword/{user}', [UserController::class, 'resetPassword']);
    Route::post('/user/editProfile/{userId}', [UserController::class, 'editProfileDetail']);
});

/* Guard routes*/
Route::post('admin/register', [AuthController::class, 'registerAsAdmin']);
Route::post('admin/login', [LoginController::class, 'loginAsAdmin']);
Route::post('teacher/login', [LoginController::class, 'loginAsTeacher']);

Route::get('auth/google', [AccountController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AccountController::class, 'handleGoogleCallback']);
Route::post('/google/login', [AccountController::class, 'googleLogin']);
Route::post('/meeting/create', [MeetingController::class,'create']);

Route::post('/forgotPassword', [UserController::class, 'forgotPassword']);

Route::get('/videos', [ VideoController::class, 'index']);

/**common Admin Routes*/
Route::middleware(['auth:sanctum', 'checkRole:super_admin,normal_admin' ])->group(function () {
    /**Question routes*/
    Route::get('/questions/getAll', [QuestionController::class, 'index']);

    /**Admin routes */
    Route::get('/admins', [AdminController::class, 'index']);
    Route::get('/admins/count', [AdminController::class, 'countTotalAdmins']);
    Route::post('/admins/editProfile/{adminId}', [AdminController::class, 'updateProfile']);

    /**Users Routes*/
    Route::get('/userlists/get', [UserController::class, 'getAllUsers']);
    Route::get('/get/userByChart', [UserController::class, 'registrationsChart']);
    Route::post('/user/create', [UserController::class, 'createUserByAdmin']);

    /**Course Routes*/
    Route::post('/courses', [CourseController::class, 'store']);
    Route::post('/courses/{id}', [CourseController::class, 'update']);
    Route::get('/courses/restore/{id}', [CourseController::class, 'restore']);
    Route::post('upload_image', [CourseController::class, 'imageUpload']);

    /**Category Routes*/
    Route::get('/category/{id}', [CategoryController::class, 'getCategoryDetails']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::post('/categories/{id}', [CategoryController::class, 'update']);

    /**Rank Routes*/
    Route::post('/modules', [RankController::class, 'store']);
    Route::post('/modules/{rank}', [RankController::class, 'update']);

    /**Module Toggle */
    Route::post('/update/studentModule/{id}', [UserController::class, 'moduleFinish']);
    Route::get('/display/studentModule', [UserController::class, 'displayStudentModule']);

    /**Level Routes*/
    Route::post('/levels', [LevelController::class, 'store']);
    Route::post('/levels/{level}', [LevelController::class, 'update']);

    /**Section routes*/
    Route::post('/section/create', [SectionController::class, 'store']);
    Route::post('/section/edit/{section}', [SectionController::class, 'update']);

    /**Teacher routes*/
    Route::post('/teachers', [TeacherController::class, 'store']);
    Route::post('/teachers/{teacher}', [TeacherController::class, 'update']);
    Route::get('/searchable/teachers', [TeacherController::class, 'getAllTeachers']);
    Route::get('/teacherLists/get', [TeacherController::class, 'index']);

    /**News Variety route*/
    Route::get('/varieties', [VarietyController::class, 'index']);
    Route::post('/varieties', [VarietyController::class, 'store']);
    Route::get('/varieties/{variety}', [VarietyController::class, 'getVarietyDetails']);
    Route::post('/varieties/{variety}', [VarietyController::class, 'update']);

    /**News Routes*/
    Route::get('/news', [NoticeController::class, 'index']);
    Route::get('/news/{news}', [NoticeController::class, 'getNoticeDetails']);
    Route::post('/news', [NoticeController::class, 'store']);
    Route::post('/news/{news}', [NoticeController::class, 'update']);
    Route::get('/get/getNewsByVariety/{varietyName}', [NoticeController::class, 'getNewsByVariety']);

    /**School Routes*/
    Route::post('/schools', [SchoolController::class, 'store']);
    Route::post('/schools/{school}', [SchoolController::class, 'update']);

    /**Type Routes*/
    Route::post('/types', [TypeController::class, 'store']);
    Route::post('/types/{type}', [TypeController::class, 'update']);

    /**Question  Batch Routes*/
    Route::get('/collections', [CollectionController::class, 'index']);
    Route::post('/collections', [CollectionController::class, 'store']);
    Route::post('/collections/{collection}', [CollectionController::class, 'update']);

    /**Grade Routes*/
    Route::post('/grades', [GradeController::class, 'store']);
    Route::post('/grades/{grade}', [GradeController::class, 'update']);

    /**Options Routes*/
    Route::post('/options', [OptionController::class, 'store']);
    Route::post('/options/{option}', [OptionController::class, 'update']);

    /**Question Routes*/
    Route::get('/questions/{gradeId}', [QuestionController::class, 'publishQuestionsByGradeId']);
    Route::post('/questions', [QuestionController::class, 'store']);
    Route::post('/questions/{question}', [QuestionController::class, 'update']);

    /**Result Routes*/
    Route::post('/results', [ResultController::class, 'store']);
    Route::post('/result/{result}', [ResultController::class, 'update']);
    Route::get('/results', [ResultController::class, 'index']);
});

/**super_admin Routes*/
Route::middleware(['auth:sanctum', 'checkRole:super_admin' ])->group(function() {

    /**Course Routes*/
    Route::delete('/courses/{id}', [CourseController::class, 'destroy']);
    Route::get('/courses/restore/{id}', [CourseController::class, 'restore']);
    Route::get('restoreAll', [CourseController::class, 'restoreAll']);

    /**Allocation Routes*/
    Route::get('/allocations', [AllocationController::class, 'index']);
    Route::post('/allocation/status/{id}', [AllocationController::class, 'updateStatus']);
    Route::post('/assign/teachers', [AllocationController::class, 'assignedToTeachers']);
    Route::post('/allocation/{allocation}', [AllocationController::class, 'update']);
    Route::delete('/allocation/{allocation}', [AllocationController::class, 'destroy']);
    Route::get('/allocations/restore/{id}', [AllocationController::class, 'restore']);
    Route::get('restoreAll', [AllocationController::class, 'restoreAll']);

    /**Category Routes*/
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    /**Rank Routes*/
    Route::delete('/modules/{rank}', [RankController::class, 'destroy']);

    /**Subcategory routes*/
    // Route::get('/subcategories', [SubcategoryController::class,'index']);
    // Route::get('/subcategory/{id}', [SubcategoryController::class, 'getSubcategoryDetails']);
    // Route::post('/subcategories', [SubcategoryController::class,'store']);
    // Route::post('/subcategories/{id}', [SubcategoryController::class,'update']);
    // Route::delete('/subcategories/{id}', [SubcategoryController::class,'destroy']);
    // Route::get('/get/subcategories', [SubcategoryController::class, 'getSubcategoriesByCategory']);

    /**Class routes*/
    Route::get('/classes', [ClassController::class, 'index']);
    Route::get('/class/{id}', [ClassController::class, 'getClassDetails']);
    Route::post('/classes', [ClassController::class, 'store']);
    Route::post('/classes/{class}', [ClassController::class, 'update']);
    Route::delete('/classes/{class}', [ClassController::class, 'destroy']);

    /**Level Routes*/
    Route::delete('/levels/{level}', [LevelController::class, 'destroy']);

    /**Section routes*/
    Route::delete('/section/{section}', [SectionController::class, 'destroy']);

    /**Teacher routes*/
    Route::put('/teachers/{teacherId}/change/role', [TeacherController::class, 'changeRole']);
    Route::put('/teachers/change/roles', [TeacherController::class, 'changeSelectedRoles']);
    Route::delete('/teachers/{teacher}', [TeacherController::class, 'destroy']);

    /**News Variety route*/
    Route::delete('/varieties/{variety}', [VarietyController::class, 'destroy']);

    /**News Routes*/
    Route::delete('/news/{news}', [NoticeController::class, 'destroy']);
    Route::get('/news/restore/{id}', [NoticeController::class, 'restore']);
    Route::get('/news/restoreAll', [NoticeController::class, 'restoreAll']);

    /**Users Manipulation Routes*/
    Route::post('/user/restrict/{userId}', [UserController::class, 'restrict']);
    Route::delete('/user/{userId}', [UserController::class, 'deleteUser']);
  
    /**School Routes*/
    Route::delete('/schools/{school}', [SchoolController::class, 'destroy']);

    /**Type Routes*/
    Route::delete('/types/{type}', [TypeController::class, 'destroy']);

    /**Grade Routes*/
    Route::delete('/grades/{grade}', [GradeController::class, 'destroy']);

    /**Options Routes*/
    Route::delete('/options/{option}', [OptionController::class, 'destroy']);

    /**Question Routes*/
    Route::delete('/questions/{question}', [QuestionController::class, 'destroy']);

    /**Question Batch Routes*/
    Route::delete('/collection/{collection}', [CollectionController::class, 'destroy']);

    /**Result Routes*/
    Route::delete('/result/{id}', [ResultController::class, 'destroy']);
});

// Route::prefix('/v1/payment')->group(function () {
    
// });
