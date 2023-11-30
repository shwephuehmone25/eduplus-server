<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Type;
use App\Models\Question;
use App\Models\TestLevel;
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
        $maxQuestions = 10;
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
                ->take(2)
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
        $questionIds = $request->input('questions');

        if (!is_array($questionIds)) {
            return response()->json(['error' => 'Invalid input format.', 'status' => 400]);
        }

        $options = Option::find($questionIds);

        if (!$options || !$options->count()) {
            return response()->json(['error' => 'Invalid options.', 'status' => 400]);
        }

        $totalPoints = $options->sum('points');

        $result = auth()->user()->results()->create([
            'total_points' => $totalPoints,
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

        $testLevel = TestLevel::join('questions', 'test_levels.grade_id', '=', 'questions.grade_id')
            ->where('questions.id', $options->first()->question_id)
            ->select('test_levels.*')
            ->first();

        if (!$testLevel) {
            return response()->json([
                'error' => 'Test level not found.',
                'status' => 404,
            ]);
        }

        $testLevelName = $testLevel->firstWhere('is_greater', ($totalPoints >= 5) ? 1 : 0);

        if (!$testLevelName) {
            return response()->json([
                'error' => 'Test level not found.',
                'status' => 404,
            ]);
        }

        $testLevelName = $testLevelName->name;

        return response()->json([
            'message' => "Your Level is {$testLevelName}",
            'result_id' => $result->id,
            'is_greater' => ($totalPoints >= 5) ? 1 : 0,
            'total_points' => $totalPoints,
            'status' => 201,
        ]);
    }
}
