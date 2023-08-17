<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Teacher;
use App\Models\TeacherCourse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $courses = Course::with('categories', 'levels', 'classrooms', 'sections', 'teachers','subcategories', 'meetings')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return response()->json(['courses' => $courses]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $course = Course::create([
            'course_name' => $request->input('course_name'),
            'description' => $request->input('description'),
            'price' => $request->input('price'),
            'period' => $request->input('period'),
            'announce_date' => $request->input('announce_date')
        ]);

        $course->categories()->attach($request->input('category_id'));
        $course->subcategories()->attach($request->input('subcategory_id'));
        $course->levels()->attach($request->input('level_id'));
        $course->classrooms()->attach($request->input('classroom_id'));
        $course->sections()->attach($request->input('section_id'));
        $course->teachers()->attach($request->input('teacher_id'));

        return response()->json(['message' => 'Course created successfully', 'course' => $course], 201);
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
        $course = Course::findOrFail($id);
        $course->update([
            'course_name' => $request->input('course_name'),
            'description' => $request->input('description'),
            'price' => $request->input('price'),
            'period' => $request->input('period'),
            'announce_date' => $request->input('announce_date'),
        ]);

        $course->categories()->sync($request->input('category_id'));
        $course->subcategories()->sync($request->input('subcategory_id'));
        $course->levels()->sync($request->input('level_id'));
        $course->classrooms()->sync($request->input('classroom_id'));
        $course->sections()->sync($request->input('section_id'));
        $course->teachers()->sync($request->input('teachers_id'));

        return response()->json(['message' => 'Course updated successfully', 'course' => $course], 200);
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

        return response()->json(['message' => 'Course deleted successfully'], 200);
    }

}
