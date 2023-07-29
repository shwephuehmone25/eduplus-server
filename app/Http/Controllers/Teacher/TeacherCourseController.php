<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Course;
use App\Models\TeacherCourse;
use Illuminate\Http\Request;

class TeacherCourseController extends Controller
{
    public function index(){
        $teacherCourses = Teacher::with('courses')->get();

        return response()->json(['teacherCourses' => $teacherCourses]);
    }

    public function store(Request $request){
        $data = $request->validate([
            'course_id' => 'required',
            'teacher_id' =>'required'
        ]);

        $teacherCourse = new TeacherCourse();
        $teacherCourse->course_id = $data['course_id'];
        $teacherCourse->teacher_id = $data['teacher_id'];
        $teacherCourse->save();

        return response()->json(['message', 'Course assigned to teacher successfully!', 'teacher_course' => $teacherCourse], 201);
    }
}