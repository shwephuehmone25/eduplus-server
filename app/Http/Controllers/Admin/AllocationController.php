<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Allocation;
use App\Models\Teacher;
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
            $teacher = Teacher::find($teacherId);
            $teacher->sections()->attach($request->input('section_id'));

            if ($teacher && $teacher->meeting) {
                $meetingId = $teacher->meeting->id;
                // $allocation->meeting_id = $meetingId;
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
    public function update(Request $request, Allocation $allocation)
    {
        try {
            $request->validate([
                'course_id' => 'required',
                'rank_id' => 'required',
                'section_id' => 'required',
                'teacher_id' => 'required',
            ]);

            $allocation->update($request->all());

            // Find the teacher 
            $teacherId = $request->input('teacher_id');
            $teacher = Teacher::find($teacherId);

            $allocation->save();

            return response()->json(['data' => $allocation, 'message' => 'Allocation updated successfully', 'status' => 200]);
        } catch (ModelNotFoundException $e) 
        {
            return response()->json(['message' => 'Allocation not found', 'status' => 404]);
        } catch (\Exception $e) 
        {
            return response()->json(['message' => 'An error occurred while updating the allocation'. $e->getMessage(), 'status' => 500]);
        }
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
