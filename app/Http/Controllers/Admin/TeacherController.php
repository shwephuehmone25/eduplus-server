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
     * count total teachers
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function countTotalTeachers() 
    {
    $teachers = Teacher::count();

    return response()->json(['data' => $teachers]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAllTeachers(Request $request)
    {
        $teachers = [];

        $teachers = Teacher::select("id", "email")->get();

        if ($request->has('q'))
         {
            $search = $request->q;
            $teachers = $teachers->filter(function ($teacher) use ($search) 
            {
                return str_contains(strtolower($teacher->email), strtolower($search));
            });
        }

        return response()->json(['data' => $teachers]);
    }

    public function index()
    {
        $teachers = Teacher::all();

        return response()->json(['data' => $teachers, 'status' => 200]);
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
        $teacher = Teacher::with('meeting', 'allocations')->find($id);

        if ($teacher)
        {
            $this->getAllocationDetail($teacher);
            // Retrieve and attach course details
            $data = [
                'teacher' => $teacher,
            ];

            return response()->json(['data' => $data, 'status' => 200]);
        }
        else
        {
            return response()->json(['message' => 'Teacher not found.', 'status' => 404]);
        }
    }

    protected function getAllocationDetail($teacher)
    {
    $allocations = $teacher->allocations;

        foreach ($allocations as $allocation) {
            $allocation->load('course', 'rank', 'section');
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
