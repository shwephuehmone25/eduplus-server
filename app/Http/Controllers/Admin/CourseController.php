<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Category;
use App\Mail\SendMail;
use App\Models\Course;
use App\Models\Teacher;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use App\Models\TeacherCourse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
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
        $courses = Course::with('categories', 'levels', 'classrooms', 'sections', 'teachers','subcategories', 'meetings')->get();
            // ->orderBy('id', 'desc')
            // ->paginate(18);

        if ($courses->isEmpty())
        {

        return response()->json(['message' => 'No courses found.', 'status' => 404]);
        }

        return response()->json(['data' => $courses]);
    }

    public function getCoursesbyCategory(Request $request, $categoryName)
    {
        // Find the category by its name in the 'categories' table
        $category = Category::where('name', $categoryName)->first();

        // Check if the category does not exist
        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        // Retrieve the courses associated with the found category
        $courses = $category->courses;

        // Return the courses as JSON response
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
        $course = Course::with('categories', 'levels', 'classrooms', 'sections', 'teachers', 'subcategories')
            ->find($id);

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
            DB::beginTransaction();

            $course = Course::create([
                'course_name' => $request->input('course_name'),
                'description' => $request->input('description'),
                'price' => $request->input('price'),
                'period' => $request->input('period'),
                'announce_date' => $request->input('announce_date')
            ]);

            $course->categories()->attach($request->input('category_id'));
            $course->levels()->attach($request->input('level_id'));
            $course->classrooms()->attach($request->input('classroom_id'));
            $course->sections()->attach($request->input('section_id'));
            $course->teachers()->attach($request->input('teacher_id'));

            DB::commit();

            return response()->json(['message' => 'Course created successfully', 'data' => $course,'status' => 201]);
        } catch (\Exception $e) {
            DB::rollback();

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
                'price' => 'required|numeric',
                'period' => 'required|string',
                'announce_date' => 'required|date_format:Y-m-d|after_or_equal:' . $todayDate,
                'category_id' => 'nullable|exists:categories,id',
                'level_id' => 'nullable|exists:levels,id',
                'classroom_id' => 'nullable|exists:classrooms,id',
                'section_id' => 'nullable|exists:sections,id',
                'teacher_id' => 'nullable|exists:teachers,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            DB::beginTransaction();

            $course = Course::findOrFail($id);
            $course->update([
                'course_name' => $request->input('course_name'),
                'description' => $request->input('description'),
                'price' => $request->input('price'),
                'period' => $request->input('period'),
                'announce_date' => $request->input('announce_date'),
            ]);

            $relatedData = [
                'categories' => $request->input('category_id'),
                'levels' => $request->input('level_id'),
                'classrooms' => $request->input('classroom_id'),
                'sections' => $request->input('section_id'),
                'teachers' => $request->input('teacher_id'),
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
        $course = Course::findOrFail($id);
        $course->delete();

        return response()->json(['message' => 'Course deleted successfully', 'status' =>200]);
    }

    /**
     * Summary of buyCourse
     * @param $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function buyCourses(Request $request, $courseId)
    {
    // Find the course by ID
    $course = Course::findOrFail($courseId);

    // Check if the user is authenticated
    if (Auth::check()) {
        $user = Auth::user();

        // Check if the user has already purchased the course
        if (!$user->courses->contains($course->id)) {
            // Attach the course to the user's purchased courses
            $user->courses()->attach($course->id);

            return response()->json(['message' => 'Course purchased successfully']);
        } else {

            return response()->json(['message' => 'You have already purchased this course']);
                }
            }
    }

    public function enroll(Request $request, $courseId)
    {
        $course = Course::findOrFail($courseId);

        if(Auth::check()){
            $user = Auth::user();
        }
        // dd($user->courses);
        if(!$user->courses->contains($course->id)){
            $user->courses->attach($course->id);

        $classroom = $course->classrooms->first();

            if(!$classroom){
                return response()->json(['warning' => 'This course does not have a classroom.'], 422);
            }

            if($classroom->capacity <= 0){
                return response()->json(['warning' => 'This course is already full!'], 422);
            }

            $enrollment = new Enrollment([
                'enroll_date' => now(),
                'isPresent' => true
            ]);

            $course->enrollments()->save($enrollment);
            $classroom->decrement('capacity', 1);

            return response()->json(['message' => 'Course purchased and enrolled successfully!']);
        }else{

            return response()->json(['message' => 'You have already purchased this course']);
        }

    }

    /**
     * Summary of getMyCourse
     * @param mixed $id
     * @return \Illuminate\Http\JsonResponse
     */
    // public function getMyCourse($userId)
    // {
    //     $user = User::findOrFail($userId);

    //     $myCourse = $user->courses()->get();
    //     $myCourse = $this->getMeeting($myCourse);

    //     if (!$myCourse) {

    //         return response()->json([
    //             'message' => 'Course not found',
    //             'status' => 404,
    //         ] );
    //     }

    //     return response()->json([
    //         'message' => 'Your Purchased Courses are',
    //         'data' => $myCourse,
    //         'status' => 200
    //     ]);
    // }

    public function getMyCourse($userId)
    {
        $user = User::findOrFail($userId);

        $myCourse = $user->courses()->with('meetings')->get();

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

    protected function getMeeting($myCourse)
    {
        $meetings = $myCourse->meetings;

        foreach ($meetings as $meeting) {
            // Load categories for each course
            $meeting->load('meetings');
        }
    }
}
