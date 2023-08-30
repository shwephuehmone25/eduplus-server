<?php

namespace App\Http\Controllers\Admin;

use App\Models\Course;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

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
        $teacher = Teacher::with('meeting', 'courses')->find($id);
       
        if ($teacher) 
        { 
            // Retrieve and attach course details
            $this->getCourseDetail($teacher); 

            $data = [
                'teacher' => $teacher,
                'courses' => $teacher->courses->map(function ($course) use ($teacher) {
                    $course->meeting = $teacher->meeting;
                    return $course;
                }),
            ];
    
            return response()->json(['data' => $data, 'status' => 200]);
        } 
        else 
        { 
            return response()->json(['message' => 'Teacher not found.', 'status' => 404]);
        }   
    }

    protected function getCourseDetail($teacher)
    {
        $courses = $teacher->courses;

        foreach ($courses as $course) {
            // Load categories for each course
            $course->load('categories'); 
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showProfile(Request $request)
    {
        $email = $request->input('email');

        // Retrieve the teacher's details by email
        $teacher = Teacher::where('email', $email)->first();

        if (!$teacher) {
            
            return response()->json(['error' => 'Unauthorized', 'status' => 403]);
        }

        return response()->json(['teacher' => $teacher]);
    }
}
