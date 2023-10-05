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

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $courses = Course::with('categories', 'levels')->get();

        if ($courses->isEmpty())
        {

        return response()->json(['message' => 'No courses found.', 'status' => 404]);
        }

        return response()->json(['data' => $courses]);
    }

    public function getCoursesbyCategory(Request $request, $categoryName)
    {
        $category = Category::where('name', $categoryName)->first();

        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        $courses = $category->courses;

        return response()->json(['courses' => $courses]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showCourseDetails($id)
    {
        $course = Course::with('categories', 'levels', 'sections', 'sections.teachers')
            ->find($id);

        // dd($course);

        if (!$course) {

            return response()->json(['error' => 'Course not found'], 404);
        }

        return response()->json(['data' => $course]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $todayDate = date('Y-m-d');

            $validator = Validator::make($request->all(), [
                'course_name' => 'required|string|max:255',
                'description' => 'required|string',
                'period' => 'required|string',
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
                'price' => $request->input('price'),
                'period' => $request->input('period'),
            ]);

            $course->categories()->attach($request->input('category_id'));
            $course->levels()->attach($request->input('level_id'));

            $course->save();

            DB::commit();

            return response()->json(['message' => 'Course created successfully', 'data' => $course,'status' => 201]);
        } catch (\Exception $e) {
            DB::rollback();
            dd($e);
            return response()->json(['message' => 'Failed to create the course','status' =>  500 ]);
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
            $todayDate = date('Y-m-d');

            $validator = Validator::make($request->all(), [
                'course_name' => 'required|string|max:255',
                'description' => 'required|string',
                'period' => 'required|string',
                // 'announce_date' => 'required|date_format:Y-m-d|after_or_equal:' . $todayDate,
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
                'price' => $request->input('price'),
                'period' => $request->input('period'),
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
        try {
        DB::beginTransaction();

        $course = Course::findOrFail($id);

        // Detach all related relationships
        $course->categories()->detach();
        $course->levels()->detach();

        // Delete the course
        $course->delete();

        DB::commit();

        return response()->json(['message' => 'Course is deleted successfully', 'status' => 200]);
    } catch (\Exception $e) {
        DB::rollback();

        return response()->json(['message' => 'Failed to delete the course', 'error' => $e->getMessage(), 'status' => 500]);
        }
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

            if ($allocation->capacity === 1) {
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

        $purchasedCourses = Allocation::join('students_allocations', 'allocations.id', '=', 'students_allocations.allocation_id')
                                        ->join('courses_categories', 'allocations.course_id', '=', 'courses_categories.course_id')
                                        ->join('categories', 'categories.id', '=', 'courses_categories.category_id')
                                        ->where('categories.name', '=', $categoryName, 'and', 'students_allocations.user_id', '=', $studentId)
                                        ->get('allocations.*');
                                        
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
    public function getPurchasedCoursesDetails($userId, $courseId)
    {
        $user = User::findOrFail($userId);

        $purchasedCourse = $user->courses()
            ->with('meetings')
            ->where('courses.id', $courseId)
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
