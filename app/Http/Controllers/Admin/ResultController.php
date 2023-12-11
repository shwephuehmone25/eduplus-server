<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Result;

class ResultController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $results = Result::all();

        return response()->json(['data' => $results]);
    }

    public function show($result_id)
    {
        $result = Result::whereHas('user', function ($query) {
            $query->whereId(auth()->id());
        })->findOrFail($result_id);
    
        return response()->json(['data' => $result]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): JsonResponse
    {
        $result = Result::create($request->validated() + ['user_id' => auth('api')->id()]);
        $result->questions()->sync($request->input('questions', []));

        return response()->json([
            'message' => 'Result is created successfully',
            'data' => $result,
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
    public function update(Request $request, Result $result)
    {
        $result->update($request->validated() + ['user_id' => auth('api')->id()]);
        $result->questions()->sync($request->input('questions', []));
    
        return response()->json([
            'message' => 'Successfully updated!',
            'data' => $result, 
            'status' => 200]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Result $result): RedirectResponse
    {
        $result->delete();

        return response()->json(['message' => 'Result is deleted successfully', 'status' => 204]);
    }
}
