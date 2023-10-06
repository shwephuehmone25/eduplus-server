<?php

namespace App\Http\Controllers\Admin;

use App\Models\Rank;
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
        $sections = Section::with('courses', 'ranks')->get();

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
        $section = Section::with('courses', 'ranks')->find($id);

        if (!$section)
        {

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
                'rank_id' => 'required',
                'course_id' => 'required|exists:courses,id',
            ]);

            $section = Section::create($data);

            $section->ranks()->sync($data['rank_id']);

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
            try {
            $data = $request->validate([
                'name' => 'string|max:255',
                'start_time' => 'date_format:H:i',
                'end_time' => 'date_format:H:i',
                'capacity' => 'integer',
                'teacher_id' => 'exists:teachers,id',
                'course_id' => 'exists:courses,id',
            ]);

            $section->update($data);

            if (isset($data['teacher_id']))
            {
                $section->teachers()->sync([$data['teacher_id']]);
            }

            return response()->json([
                'message' => 'Section updated successfully',
                'data' => $section,
                'status' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Section update failed: ' . $e->getMessage(),
                'status' => 500
            ]);
        }
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
