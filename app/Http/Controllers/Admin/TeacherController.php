<?php

namespace App\Http\Controllers\Admin;

use App\Models\Teacher;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAllTeachers(Request $request)
    {
        $teachers = [];

        if($request->has('q')){
            $search = $request->q;
            $teachers =Teacher::select("id", "name")
            		->where('name', 'LIKE', "%$search%")
            		->get();
        }
     
        return response()->json(['data' => $teachers]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:teachers,email|ends_with:@ilbcedu.com',
            'password' => 'required|min:6|confirmed',
        ]);

        $teacher = new Teacher();
        $teacher->name = $request->input('name');
        $teacher->email = $request->input('email');
        $teacher->password = bcrypt($request->input('password'));
        $teacher->google_id = $request->input('google_id');
        $teacher->save();
       
        return response()->json(['message' => 'Teacher created successfully', 'data' => $teacher, 'status' => 201]);
    }

     /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Teacher $teacher)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:teachers,email|ends_with:@ilbcedu.com,' . $teacher->id,
            'password' => 'nullable|min:6|confirmed',
        ]);

        $teacher->name = $request->input('name');
        $teacher->email = $request->input('email');

        if ($request->input('password')) {
            $teacher->password = bcrypt($request->input('password'));
        }

        $teacher->save();

        return response()->json(['message' => 'Teacher updated successfully', 'data' => $teacher, 'status' => 200]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Teacher $teacher)
    {
        $teacher->delete();

        return response()->json(['message' => 'Teacher deleted successfully', 'status' => 200]);
    }

    /**
     * Get the assigned courses for teachers.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAssignCourses($id)
    {
        $assignCourses = Course::with('meetings', 'categories')
                        ->join('teacher_courses', 'courses.id', 'teacher_courses.course_id')
                        ->where('teacher_courses.teacher_id', $id)
                        ->get(['courses.*']);

        $meetingsExist = false; 
        foreach ($assignCourses as $course) { 
            if (!$course->meetings->isEmpty()) 
            {
                $meetingsExist = true;
                break; 
            } 
        } 

        if (!$meetingsExist) 
        { 

            return response()->json(['message' => 'No meeting found for this course.', 'status' => 404]); 
        } 
        else 
        { 
            
            return response()->json(['data' => $assignCourses, 'status' => 200]); 
        } 

        // foreach ($assignCourses as $course) {
        //     if (!$course->relationLoaded('meetings') || $course->meetings->isEmpty()) {
        // return response()->json(['message' => 'Some courses have missing or empty meetings.', 'status' => 404]);
        //     }
        // }

        // return response()->json(['data' => $assignCourses, 'status' => 200]);   
    }
}
