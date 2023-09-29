<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Allocation;

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
    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required',
            'rank_id' => 'required',
            'section_id' => 'required',
            'teacher_id' => 'required',
        ]);

        $allocation = Allocation::create($request->all());

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

            return response()->json(['message' => 'Assigned to teachers successfully', 'data' => $allocation, 'status' => 200 ]);
        } catch (\Exception $e) {

            return response()->json(['error' => 'An error occurred while updating the allocation.', 'status' => 500]);
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
