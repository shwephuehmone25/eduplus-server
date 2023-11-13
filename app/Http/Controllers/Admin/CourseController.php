<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Category;
use App\Mail\SendMail;
use App\Models\Course;
use App\Models\Teacher;
use App\Models\Enrollment;
use App\Models\StudentModule;
use Illuminate\Http\Request;
use App\Models\TeacherCourse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\Allocation;
use App\Models\CourseCategory;
use App\Models\TeacherStudent;
use App\Models\StudentSection;
use App\Models\StudentAllocation;
use Google\Service\Classroom\Resource\Courses;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Http\Middleware\CheckRole;
use App\Models\Image;
use App\Models\Rank;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\AwsS3V3\PortableVisibilityConverter;

class CourseController extends Controller
{

    /**
     * count total courses
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function countCourses()
    {
    $courses = Course::count();

    return response()->json(['data' => $courses]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $courses = Course::whereHas('allocations')
        // ->with('allocations')
        // ->get();

        // return response()->json(['data' => $courses]);

        $courses = Course::with('categories', 'levels', 'sections')->get();

        return response()->json(['data' => $courses]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getCoursesbyCategory(Request $request, $categoryName)
    {
        $category = Category::where('name', $categoryName)->first();

        if (!$category) {
            return response()->json(['error' => 'Category not found', 'staus' => 404]);
        }

        // $courses = $category->courses()
        // ->whereHas('allocations')
        // ->with('allocations')
        // ->get();

        $courses = $category->courses()
        ->whereHas('allocations')
        ->with(['allocations.section', 'allocations.rank', 'allocations.teacher'])
        ->get();

        return response()->json(['data' => $courses]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showCourseDetails($id)
    {

        $course = Course::with([
            'categories',
            'levels',
            'sections',
            'allocations',
            'allocations.teacher'
        ])->find($id);

        if (!$course) {
            return response()->json(['error' => 'Course not found', 'status' => 404]);
        }

        $course['teachers'] = [];

        $modules = Rank::all();

        $prices = $modules->map(function ($module) use ($course) {
            $localPrice = intval($course->price_for_local) / 2; 
            $expatPrice = intval($course->price_for_expat) / 2;
            $module->price = [
                'local_price' => $localPrice,
                'expat_price' => $expatPrice,
            ];
            return $module;
        });

        foreach ($course->allocations as $allocation) {
            if ($allocation->teacher) {
                $course['teachers'] = $allocation->teacher;
            }
        }

        $course['modules'] = $modules;

        return response()->json(['data' => $course]);
    }

    // protected function teachersForCourse($courseId)
    // {
    //     return Teacher::whereHas('sections', function ($query) use ($courseId) {
    //         $query->where('course_id', $courseId);
    //     })->get();
    // }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'course_name' => 'required|string|max:255',
                'description' => 'required|string',
                'period' => 'required|string',
                'price_for_local' => 'required|string',
                'price_for_expat' => 'required|string',
                'image_url' => 'required|string',
                'category_id' => 'required',
                'level_id' => 'required',
            ]);

            if ($validator->fails())
            {
                return response()->json(['errors' => $validator->errors(), 'status' => 422]);
            }

            DB::beginTransaction();

            $course = Course::create([
                'course_name' => $request->input('course_name'),
                'description' => $request->input('description'),
                'image_url' => $request->input('image_url'),
                'period' => $request->input('period'),
                'price_for_local' => $request->input('price_for_local'),
                'price_for_expat' => $request->input('price_for_expat'),
            ]);

            $course->categories()->attach($request->input('category_id'));
            $course->levels()->attach($request->input('level_id'));

            $course->save();

            DB::commit();

            return response()->json(['message' => 'Course created successfully', 'data' => $course,'status' => 201]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['message' => 'Failed to create the course', 'error' => $e->getMessage(), 'status' =>  500 ]);
        }
    }

    /**
     * Upload a course's image.
     *
     * Upload and store course image to a storage service (e.g., Amazon S3) and
     * save the image URL to the database.
     *
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function imageUpload(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'image' => 'required|file|mimes:jpg,jpeg,png|max:5120',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors()->all();
                return response()->json(['message' => 'Validation failed', 'errors' => $errors, 'status' => 422], 422);
            }

            if ($request->hasFile('image')) {
                $s3Path = Storage::disk('s3')->put('courses', $request->file('image'));

                $image = new Image();
                $image->url = Storage::disk('s3')->url($s3Path);
                $image->save();

                return response()->json(['message' => 'Image file uploaded successfully!', 'data' => $image, 'status' => 201]);
            }else{
                return response()->json(['message' => 'No file uploaded!', 'status' => 400]);
            }
        }
        catch(\Exception $e){
            return response()->json(['message' => 'Failed to upload image', 'error' => $e->getMessage(), 'status' => 500]);
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
        try {

            $validator = Validator::make($request->all(), [
                'course_name' => 'required|string|max:255',
                'description' => 'required|string',
                'period' => 'required|string',
                'price_for_local' => 'required|string',
                'price_for_expat' => 'required|string',
                'category_id' => 'nullable|exists:categories,id',
                'level_id' => 'nullable|exists:levels,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors(), 'status' => 422]);
            }

            DB::beginTransaction();

            $course = Course::findOrFail($id);
            $course->update([
                'course_name' => $request->input('course_name'),
                'description' => $request->input('description'),
                'period' => $request->input('period'),
                'price_for_local' => $request->input('price_for_local'),
                'price_for_expat' => $request->input('price_for_expat'),
            ]);

            $relatedData = [
                'categories' => $request->input('category_id'),
                'levels' => $request->input('level_id'),
            ];

            foreach ($relatedData as $relation => $ids) {
                if (!empty($ids)) {
                    $course->{$relation}()->sync($ids);
                } else {
                    $course->{$relation}()->detach();
                }
            }

            if ($request->hasFile('image')) {
                $image =$course->images()->first();
                if ($image) {
                    $imageUrl = parse_url($image->url, PHP_URL_PATH);
                    if (Storage::disk('s3')->exists($imageUrl)) {
                        Storage::disk('s3')->delete($imageUrl);
                    }
                    $image->delete();
                } else {
                    $image = new Image();
                }

                $s3Path = Storage::disk('s3')->put('courses', $request->file('image'));

                $image->url = $s3Path;
                $course->images()->save($image);
            }

            DB::commit();

            return response()->json(['message' => 'Course updated successfully', 'data' => $course, 'status' => 200]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['message' => 'Failed to update the course', 'error' => $e->getMessage(), 'status' => 500]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $course = Course::findOrFail($id);

        if(!$course)
        {
            return response()->json(['message' => 'Course not found!', 'status' => 404]);
        }

        $course->delete();

        return response()->json(['message' => 'Course is deleted successfully', 'status' => 204]);

    }

    /**
     * Summary of buyCourse
     * @param $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function buyCourses(Request $request, $allocationId)
    {
        $allocation = Allocation::findOrFail($allocationId);
        $user = Auth::user();

        $existingAllocation = $user->allocations()
                            ->where('allocation_id', '!=', $allocation->id)
                            ->where('course_id', $allocation->course_id)
                            ->where('rank_id', $allocation->rank_id)
                            ->first();

        if ($existingAllocation)
        {
            return response()->json(['message' => 'You have already purchased this course.']);
        }

        $studentModule = StudentModule::where('user_id', $user->id)
            ->where('course_id', $allocation->course_id)
            ->first();

        if ($studentModule && $studentModule->is_complete === false )
        {
            return response()->json(['message' => 'You need to complete this course before purchasing.']);
        }

        if (!$allocation->users->contains($user->id)) {

            if ($allocation->capacity > 0) {
                $teacherId = $allocation->teacher_id;
                $user->teachers()->attach($teacherId);

                $allocation->users()->attach($user->id);
                $allocation->decrement('capacity', 1);

                $enrollment = new Enrollment([
                    'enroll_date' => now(),
                    'isPresent' => true,
                    'user_id' => $user->id,
                    'course_id' => $allocation->course_id,
                    'end_date' => now(),
                ]);
                $enrollment->save();

                if (!$studentModule) {
                    $studentModule = new StudentModule([
                        'user_id' => $user->id,
                        'course_id' => $allocation->course_id,
                        'rank_id' => $allocation->rank_id,
                        'is_complete' => true,
                        'end_date' => now(),
                    ]);
                } else {
                    $studentModule->is_complete = true;
                }
                $studentModule->save();

                return response()->json(['message' => 'Course purchased and enrolled successfully!']);
            } else {
                return response()->json(['message' => 'This course is already full!']);
            }
        } else {
            return response()->json(['message' => 'Course already purchased and enrolled.']);
        }
    }

    /**
     * Summary of getMyCourse
     * @param mixed $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMyCourse($userId)
    {
        $user = User::findOrFail($userId);

        $myCourse = $user->allocations()->with('section', 'course', 'rank', 'teacher', 'meetings')->get();

        if (!$myCourse) {
            return response()->json([
                'message' => 'Course not found',
                'status' => 404,
            ] );
        }

        return response()->json([
            'message' => 'Your Purchased Courses are',
            'data' => $myCourse,
            'status' => 200
        ]);
    }

    /**
     * Summary of getPurchasedCoursesByCategory
     * @param mixed $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPurchasedCoursesByCategory(Request $request, $categoryName)
    {
        $category = Category::where('name', $categoryName)->first();

        if (!$category)
        {

            return response()->json(['error' => 'Category not found'], 404);
        }

        $studentId = auth()->user()->id;

        $purchasedCourses = StudentCourse::where('user_id', $studentId)
            ->whereIn('course_id', $category->courses->pluck('id'))
            ->get();

            $courseDetails = [];

            foreach ($purchasedCourses as $purchasedCourse) {
                $course = Course::find($purchasedCourse->course_id);
                if ($course) {
                    $courseDetails[] = $course;
                }
            }

        return response()->json([
            'purchasedCourses' => $courseDetails,
            'status' => 200
        ]);
    }

    /**
     * Summary of getPurchasedCoursesDetails
     * @param mixed $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPurchasedCoursesDetails($userId, $allocation_id)
    {
        $user = User::findOrFail($userId);

        $purchasedCourse = $user->allocations()
            ->with('meetings')
            ->where('allocations.id', $allocation_id)
            ->first();

        if (!$purchasedCourse)
        {
            return response()->json([
                'message' => 'Course not found in your purchased courses',
                'status' => 404,
            ]);
        }

        return response()->json([
            'message' => 'Purchased Course Details',
            'data' => $purchasedCourse,
            'status' => 200,
        ]);
    }

    /**
     * Restore a single deleted course by ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id)
    {
        $restoredCourse =Course::withTrashed()->find($id)->restore();

        if ($restoredCourse)
        {
            return response()->json(['message' => 'Course restored successfully'], 200);
        } else {

            return response()->json(['message' => 'Course not found or already restored'], 404);
        }
    }

    /**
     * Restore all deleted courses.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function restoreAll()
    {
        $restoredCourses =Course::onlyTrashed()->restore();

        if ($restoredCourses)
        {

            return response()->json(['message' => 'All deleted Courses restored successfully', 'status' => 200]);
        } else {

            return response()->json(['message' => 'No deleted Courses found to restore', 'status' => 404]);
        }
    }

    public function getLikedCourses($userId)
    {
        $user = User::find($userId);

    if (!$user) {

        return response()->json(['message' => 'User not found', 'status' => 404]);
    }

    $likedCourses = $user->likes()
        ->where('likeable_type', 'App\Models\Course')
        ->with('likeable')
        ->get()
        ->pluck('likeable');

        if ($likedCourses->isEmpty()) {

        return response()->json(['message' => 'No liked courses found', 'status' => 204]);
    }

    return response()->json(['liked_courses' => $likedCourses, 'status' => 200]);
    }
}
