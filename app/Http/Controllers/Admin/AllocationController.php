<?php

namespace App\Http\Controllers\Admin;

use App\Models\Course;
use App\Models\Teacher;
use App\Models\Allocation;
use Illuminate\Http\Request;
use App\Models\TeacherSection;
use App\Http\Controllers\Controller;
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
            'course_id' => 'required',
            'rank_id' => 'required',
            'section_id' => 'required',
            'teacher_id' => 'required',
        ]);

        $allocation = Allocation::create($request->all());

        $teacherId = $request->input('teacher_id');
        $sectionId = $request->input('section_id');
        $courseId  = $request->input('course_id');
        $rankId    = $request->input('rank_id');

        $teacher = Teacher::find($teacherId);
        $course = Course::find($courseId);

        if ($teacher && $course) {
            if (!$teacher->sections()->where('section_id', $sectionId)->exists()) {
                $teacher->sections()->attach($sectionId, ['course_id' => $courseId]);
            }

            if (!$course->sections()->where('section_id', $sectionId)->exists()) {
                $course->sections()->attach($sectionId);
            }

            if(!$course->ranks()->where('rank_id', $rankId)->exists()){
                $course->ranks()->attach($rankId);
            }
        }


            if ($teacher && $teacher->meeting)
            {
                $meetingId = $teacher->meeting->id;
                $allocation->meeting_id = $meetingId;
                if (!$allocation->meetings()->where('meeting_id', $meetingId)->exists()) {
                    $allocation->meetings()->attach($meetingId);
                }
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
                'course_id' => 'required',
                'rank_id' => 'required',
                'section_id' => 'required',
                'teacher_id' => 'required',
            ]);

            $allocation = Allocation::find($id);

        if (!$allocation) {
            return response()->json(['error' => 'Allocation not found', 'status' => 404]);
        }

        $teacherId = $request->input('teacher_id');
        $sectionId = $request->input('section_id');
        $courseId = $request->input('course_id');
        $rankId = $request->input('rank_id');

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

        return response()->json(['message' => 'Assigned courses is deleted successfully']);
    }
}
