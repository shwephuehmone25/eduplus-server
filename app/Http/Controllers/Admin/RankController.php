<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rank;
use Illuminate\Support\Facades\Validator;

class RankController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $ranks = Rank::all();

        return response()->json(['data' => $ranks]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showModuleDetails($id)
    {
        $rank = Rank::find($id);

        if (!$rank) {

            return response()->json(['error' => 'Module not found'], 404);
        }

        return response()->json(['data' => $rank]);
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
            'name' => 'required|string|max:255|unique:ranks,name',
            'price' => 'required'
        ]);

        $rank = Rank::create($data);

        return response()->json([
            'message' => 'Module is created successfully',
            'data' => $rank,
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
    public function update(Request $request, Rank $rank)
    {
         $rules = [
        'name' => 'required|string|max:255|unique:ranks,name,' . $rank->id,
        'price' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) 
        {
            return response()->json(['error' => $validator->errors(), 'status' => 422]);
        }

        try {
            $rank->name = $request->input('name');
            $rank->price = $request->input('price');
            $rank->save();

            return response()->json(['message' => 'Module is updated successfully', 'data' => $rank, 'status' => 200]);
        } catch (\Exception $e) 
        {
            return response()->json(['error' => 'Module update failed', 'message' => $e->getMessage(), 'status' => 500]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Rank $rank)
    {
        $rank->delete();

        return response()->json(['message' => 'Module is deleted successfully', 'status' => 204]);
    }
}
