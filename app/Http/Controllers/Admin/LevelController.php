<?php

namespace App\Http\Controllers\Admin;

use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Category;

class LevelController extends Controller
{
    /**
     * Display a listing of levels by categoryId.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCourseByCategoryId($categoryId)
    {
        try {
            $levels = Category::findOrFail($categoryId)->levels;

            return response()->json(['data' => $levels, 'status' => 200]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve levels', 'error' => $e->getMessage(), 'status' => 500]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showLevelDetails($id)
    {
        $level = Level::with(['categories'])->find($id);

        if (!$level) {

            return response()->json(['error' => 'Level not found', 'status' => 404]);
        }

        return response()->json(['data' => $level, 'status' => 200]);
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
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'category_id' => 'required',
            ]);
    
            if ($validator->fails()) 
            {
                return response()->json(['errors' => $validator->errors(), 'status' => 422]);
            }
    
            DB::beginTransaction();
    
            $level = Level::create([
                'name' => $request->input('name'),
            ]);
    
            $level->categories()->attach($request->input('category_id'));
    
            $level->save();
    
            DB::commit();
    
            return response()->json(['message' => 'Level is created successfully', 'data' => $level, 'status' => 201]);
        } catch (\Exception $e) 
        {
            DB::rollback();
    
            return response()->json(['message' => 'Failed to create the level', 'error' => $e->getMessage(), 'status' => 500]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Level $level)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255' . $level->id,
                'category_id' => 'required',
            ]);

            if ($validator->fails()) 
            {
                return response()->json(['errors' => $validator->errors(), 'status' => 422]);
            }

            DB::beginTransaction();

            $level->update([
                'name' => $request->input('name'),
            ]);

            $level->categories()->sync($request->input('category_id'));

            $level->save();

            DB::commit();

            return response()->json(['message' => 'Level is updated successfully', 'data' => $level, 'status' => 200]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['message' => 'Failed to update the level', 'error' => $e->getMessage(), 'status' => 500]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Level $level)
    {
        $level->delete();

        return response()->json(['message' => 'Level deleted successfully', 'status' => 204]);
    }
}
