<?php

namespace App\Http\Controllers\Admin;

use App\Models\Teacher;
use App\Models\Section;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SectionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sections = Section::all();

        return response()->json(['data' => $sections]);
    }

     /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getSectionDetails($id)
    {
        $section = Section::find($id);

        if (!$section) {

            return response()->json(['error' => 'Section not found'], 404);
        }

        return response()->json(['data' => $section]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i',
                'capacity' => 'required',
                'teacher_id' => 'required',
                'course_id' => 'required|exists:courses,id',
            ]);

            $section = Section::create($data);

            $section->teachers()->sync($data['teacher_id']);

            return response()->json([
                'message' => 'Section created successfully',
                'data' => $section,
                'status' => 201
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Section creation failed: ' . $e->getMessage(),
                'status' => 500
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Section $section)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date_format:Y-m-d H:i',
            'end_time' => 'required|date_format:Y-m-d H:i',
        ]);

        $section->update($data);

        return response()->json([
            'message' => 'Section updated successfully',
            'data' => $section,
            'status' => 200
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Section $section)
    {
        $section->delete();

        return response()->json(['message' => 'Section deleted successfully', 'status' => 200]);
    }
}
