<?php

namespace App\Http\Controllers\Admin;

use App\Models\Course;
use App\Models\Teacher;
use App\Models\Allocation;
use Illuminate\Http\Request;
use App\Models\TeacherSection;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AllocationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $allocations = Allocation::all();

        if ($allocations->isEmpty())
        {

        return response()->json(['message' => 'No courses found.', 'status' => 404]);
        }

        return response()->json(['data' =>  $allocations]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function assignedToTeachers(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'rank_id' => 'required|exists:ranks,id',
            'section_id' => 'required|exists:sections,id',
            'teacher_id' => 'required|exists:teachers,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'course_type'   => 'required'
        ]);

        $allocation = Allocation::create($request->all());
        $teacherId = $request->input('teacher_id');
        $sectionId = $request->input('section_id');
        $courseId  = $request->input('course_id');
        $rankId    = $request->input('rank_id');
        $classId   = $request->input('classroom_id');
        $course_type = $request->input('course_type');

        $teacher = Teacher::find($teacherId);
        $course = Course::find($courseId);

        if(!$course || $course->trashed()){
            return response()->json(['error' => 'Course not found!', 'status' => 404]);
        }

        if ($teacher && $course) {

            if ($teacher->sections()->where('section_id', $sectionId)->exists()) 
            {
                return response()->json(['error' => 'Teacher is already assigned to this section! Please choose another section to assign', 'status' => 400]);
            }

            if (!$teacher->sections()->where('section_id', $sectionId)->exists())
             {
                $teacher->sections()->attach($sectionId, ['course_id' => $courseId]);
            }

            if (!$course->sections()->where('section_id', $sectionId)->exists()) 
            {
                $course->sections()->attach($sectionId);
            }

            if(!$course->ranks()->where('rank_id', $rankId)->exists())
            {
                $course->ranks()->attach($rankId);
            }

            if(!$course->classrooms()->where('classroom_id', $classId)->exists())
            {
                $course->classrooms()->attach($classId);
            }
        }

            if ($teacher && $teacher->meeting)
            {
                $meetingId = $teacher->meeting->id;
                $allocation->meetings()->attach($meetingId);
            }

        return response()->json(['data' => $allocation, 'message' => 'Assigned to teachers successfully', 'status' => 201]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $allocation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'rank_id' => 'required|exists:ranks,id',
            'section_id' => 'required|exists:sections,id',
            'teacher_id' => 'required|exists:teachers,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'course_type'   => 'required'
        ]);

        $allocation = Allocation::find($id);

        if (!$allocation) {
            return response()->json(['error' => 'Allocation not found', 'status' => 404]);
        }

        $teacherId = $request->input('teacher_id');
        $sectionId = $request->input('section_id');
        $courseId = $request->input('course_id');
        $rankId = $request->input('rank_id');
        $classId   = $request->input('classroom_id');
        $course_type = $request->input('course_type');

        $teacher = Teacher::find($teacherId);
        $course = Course::find($courseId);

        if ($teacher && $course) {
            TeacherSection::create([
                'teacher_id' => $teacherId,
                'section_id' => $sectionId,
                'course_id' => $courseId,
            ]);
            $course->sections()->sync($sectionId);

            $course->ranks()->sync($rankId);
        }

        if(!$course->classrooms()->where('classroom_id', $classId)->exists()){
            $course->classrooms()->attach($classId);
        }

        if ($teacher && $teacher->meeting) {
            $meetingId = $teacher->meeting->id;

            $allocation->meetings()->sync([$meetingId]);
        }

        $allocation->update($request->all());
        return response()->json(['data' => $allocation, 'message' => 'Assignment updated successfully', 'status' => 200]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $allocation
     * @return \Illuminate\Http\Response
     */
    public function destroy(Allocation $allocation)
    {
        $allocation->delete();

        return response()->json(['message' => 'Assigned courses is deleted successfully', 'status' => 204]);
    }

    public function updateStatus(Request $request, $id)
    {
        $allocation = Allocation::find($id);

        if(!$allocation)
        {
            return response()->json(['message' => 'Allocation not found!'], 404);
        }

        $allocation->status = ($allocation->status == 'available') ? 'full' : 'available';
        $allocation->save();

        return response()->json(['message' => 'Status updated successfully!', 'status' => $allocation->status], 200);
    }
}
