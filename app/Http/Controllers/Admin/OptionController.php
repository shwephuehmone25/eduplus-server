<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Option;

class OptionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $options = Option::all();

        return response()->json(['data' => $options]);
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
            'option_text' => 'required|string|max:255|unique:options,option_text',
            'question_id' => 'required|exists:questions,id',
            'points' => 'required',
        ]);

        $option = Option::create($data);

        return response()->json([
            'message' => 'Option is created successfully',
            'data' => $option,
            'status' => 201
            ]);
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Option $option)
    {
        $data = $request->validate([
            'option_text' => 'required|string|max:255|unique:options,option_text' . $option->id,
            'question_id' => 'required|exists:questions,id',
            'points' => 'required',
        ]);

        $option->update($data);

        return response()->json(['message' => 'Option is updated successfully', 'data' => $option, 'status' => 200]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Option $option)
    {
        $option->delete();

        return response()->json(['message' => 'Option is deleted successfully', 'status' => 204]);
    }
}
