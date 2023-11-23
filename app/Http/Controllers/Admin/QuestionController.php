<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\Option;
use App\Models\Grade;
use App\Models\Type;
class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $randomTypes = Type::inRandomOrder(2)->pluck('id');
        
        $questions = Question::whereIn('type_id', $randomTypes)
            ->inRandomOrder()
            ->take(20)
            ->get();
        
        return response()->json([
            'data' => $questions
        ]);
    }

    /**
     * Display a listing of the question by grade id.
     *
     * @return \Illuminate\Http\Response
     */
    public function getQuestionsByGrade(Request $request, $gradeName)
    {
        try {
            $grade = Grade::where('name', $gradeName)->select('id')->firstOrFail();

            $questions = Question::where('grade_id', $grade->id)
                ->with('options', 'school:id,name', 'type:id,name')
                ->get();

            $questions = $questions->map(function ($question) use ($grade) {
                $question['school_id'] = $question->school->id;
                $question['type_id'] = $question->type->id;
                $question['options'] = $question->options;
                return $question;
            });
            return response()->json(['data' => $questions]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) 
        {
            return response()->json([
                'message' => 'Grade not found', 
                'status' => 404
            ]);
        }
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
            'options' => 'required|array',
            'options.*.option_text' => 'required|string',
            'options.*.points' => 'integer|nullable',
        ]);

        $question = Question::create($data);

        foreach ($data['options'] as $optionData) {
            $option = new Option([
                'option_text' => $optionData['option_text'],
                'points' => $optionData['points'] ?? 0,
            ]);

            $question->options()->save($option);
        }

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
            'options' => 'required|array',
            'options.*.option_text' => 'required|string',
            'options.*.points' => 'integer|nullable',
        ]);

        $question->update($data);

        $question->options()->delete();

        foreach ($data['options'] as $optionData) {
            $option = new Option([
                'option_text' => $optionData['option_text'],
                'points' => $optionData['points'] ?? null,
            ]);

            $question->options()->save($option);
        }

        return response()->json([
            'message' => 'Question is updated successfully',
            'data' => $question,
            'status' => 200
        ]);
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
