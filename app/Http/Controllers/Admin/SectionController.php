<?php

namespace App\Http\Controllers\Admin;

use App\Models\Rank;
use App\Models\Course;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $section = Section::with('courses', 'ranks')->find($id);

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
        $data = $request->validate([
        'name' => 'required|string|max:255',
        'start_time' => 'required|date_format:Y-m-d H:i:s',
        'end_time' => 'required|date_format:Y-m-d H:i:s',
        // 'capacity' => 'required|integer',
        // 'rank_id' => 'required|exists:ranks,id',
        // 'course_id' => 'required|exists:courses,id',
    ]);

        $section = Section::create([
            'name' => $data['name'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'capacity' => 1,
            // 'course_id' => $data['course_id'],
            // 'rank_id' => $data['rank_id'],
        ]);

        // $course = Course::find($data['course_id']);

        // if (!$course) {
        //     return response()->json([
        //         'message' => 'Course not found',
        //         'status' => 404
        //     ]);
        // }

        // $course->ranks()->attach($data['rank_id']);

        return response()->json([
            'message' => 'Section created successfully',
            'data' => $section,
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
    public function update(Request $request, Section $section)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'start_time' => 'required|date_format:Y-m-d H:i:s',
                'end_time' => 'required|date_format:Y-m-d H:i:s',
                'capacity' => 'required|integer',
                // 'rank_id' => 'required|exists:ranks,id',
                // 'course_id' => 'required|exists:courses,id',
            ]);

            $section->update($data);
            // $course = Course::find($data['course_id']);
            // $section->course()->associate($course);
            // $section->ranks()->sync($data['rank_id']);

            $section->save();

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
