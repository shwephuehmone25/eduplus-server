<?php

namespace App\Http\Controllers\Admin;

use App\Models\Course;
use App\Models\Teacher;
use Illuminate\Http\Request;
use App\Models\TeacherCourse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $courses = Course::with([
            'categories:id,name', 
            'levels:id,name', 
            'classrooms:id,name', 
            'sections:id,name', 
            'teachers:id,name', 
            'subcategories:id,name'
        ])
        ->select(['id', 'course_name', 'description', 'price', 'period', 'announce_date','created_at'])
        ->orderByDesc('id')
        ->paginate(10);

        return response()->json(['data' => $courses]);
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
            DB::beginTransaction();
    
            $course = Course::create([
                'course_name' => $request->input('course_name'),
                'description' => $request->input('description'),
                'price' => $request->input('price'),
                'period' => $request->input('period'),
                'announce_date' => $request->input('announce_date')
            ]);
    
            $course->categories()->attach($request->input('category_id'));
            $course->levels()->attach($request->input('level_id'));
            $course->classrooms()->attach($request->input('classroom_id'));
            $course->sections()->attach($request->input('section_id'));
            $course->teachers()->attach($request->input('teacher_id'));
    
            DB::commit();
    
            return response()->json(['message' => 'Course created successfully', 'data' => $course,'status' => 201]);
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json(['message' => 'Failed to create the course','status' =>  500 ]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();
    
            $course = Course::findOrFail($id);
            $course->update([
                'course_name' => $request->input('course_name'),
                'description' => $request->input('description'),
                'price' => $request->input('price'),
                'period' => $request->input('period'),
                'announce_date' => $request->input('announce_date'),
            ]);
    
            $relatedData = [
                'categories' => $request->input('category_id'),
                'levels' => $request->input('level_id'),
                'classrooms' => $request->input('classroom_id'),
                'sections' => $request->input('section_id'),
                'teachers' => $request->input('teacher_id'),
            ];
    
            foreach ($relatedData as $relation => $ids) {
                if (!empty($ids)) {
                    $course->{$relation}()->sync($ids);
                } else {
                    $course->{$relation}()->detach();
                }
            }
    
            DB::commit();
    
            return response()->json(['message' => 'Course updated successfully', 'data' => $course, 'status' => 200]);
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json(['message' => 'Failed to update the course','status' => 500]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        $course->delete();

        return response()->json(['message' => 'Course deleted successfully','status' =>200]);
    }
}
