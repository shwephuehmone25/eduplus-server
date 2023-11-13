<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Type;
use App\Models\Question;
use App\Models\Option;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class PlacementTestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getQuestionsByGrades($grade_id)
    {
        $maxQuestions = 20;
        $randomTypes = Type::inRandomOrder()->get();
        $questions = collect();

        foreach ($randomTypes as $randomType) {
            if ($questions->count() >= $maxQuestions) {
                break;
            }

            $typeQuestions = Question::where('type_id', $randomType->id)
                ->where('grade_id', $grade_id)
                ->inRandomOrder()
                ->with(['options' => function ($query) {
                    $query->inRandomOrder();
                }])
                ->take(4)
                ->get();

            $remainingSpace = $maxQuestions - $questions->count();
            if ($typeQuestions->count() > $remainingSpace) {
                $typeQuestions = $typeQuestions->take($remainingSpace);
            }

            $questions = $questions->merge($typeQuestions);
        }

        if ($questions->count() > $maxQuestions) {
            $questions = $questions->take($maxQuestions);
        }

        return response()->json($questions);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->check())
         {
            return response()->json(['error' => 'Unauthenticated.', 'status' => 401]);
        }
    
        $user = auth()->user();
    
        if (!$user) 
        {
            return response()->json(['error' => 'User not found.', 'status' => 404]);
        }

        $options = Option::find(array_values($request->input('questions')));
    
        $result = auth()->user()->results()->create([
            'total_points' => $options->sum('points'),
        ]);
    
        $questions = $options->mapWithKeys(function ($option) {
            return [
                $option->question_id => [
                    'option_id' => $option->id,
                    'points' => $option->points,
                ],
            ];
        })->toArray();
    
        $result->questions()->sync($questions);
    
        return response()->json([
            'message' => "Result is created successfully",
            'result_id' => $result->id, 
            'status' =>201,  
        ]);
    }
}
