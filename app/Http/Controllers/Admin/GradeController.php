<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Grade;

class GradeController extends Controller
{
   /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $grades = Grade::all();

        return response()->json(['data' => $grades]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'school_id' => 'required',
        ]);

        // Create a new grade instance
        $grade = new Grade([
            'name' => $data['name'],
        ]);

        $grade->save();

        $grade->schools()->attach($data['school_id']);

        return response()->json([
            'message' => 'Grade is created successfully',
            'data' => $grade,
            'status' => 201
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Grade $grade)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:grades$grades,name,' . $grade->id,
            'school_id' => 'nullable|exists:schools,id',
        ]);

        $grade->update($data);

        return response()->json(['message' => 'Grade is updated successfully', 'data' => $grade, 'status' => 200]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Grade $grade)
    {
        $grade->delete();

        return response()->json(['message' => 'Grade is deleted successfully', 'status' => 204]);
    }
}
