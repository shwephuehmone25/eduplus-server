<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Question;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $questions = Question::all();

        return response()->json(['data' => $questions]);
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
            'question_text' => 'required|string|max:255|unique:questions,question_text',
            'grade_id' => 'required|exists:grades,id',
            'type_id' => 'required|exists:types,id',
            'school_id' => 'required|exists:schools,id',
        ]);

        $question = Question::create($data);

        return response()->json([
            'message' => 'Question is created successfully',
            'data' => $question,
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
    public function update(Request $request, Question $question)
    {
        $data = $request->validate([
            'question_text' => 'required|string|max:255|unique:questions,question_text,' . $question->id,
            'grade_id' => 'required|exists:grades,id',
            'type_id' => 'required|exists:types,id',
            'school_id' => 'required|exists:schools,id',
        ]);

        $question->update($data);

        return response()->json(['message' => 'Question is updated successfully', 'data' => $question, 'status' => 200]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Question $question)
    {
        $question->delete();

        return response()->json(['message' => 'Question is deleted successfully', 'status' => 204]);
    }
}
