<?php

namespace App\Http\Controllers\User;

use App\Http\Requests\LikeRequest;
use App\Http\Requests\UnlikeRequest;

class LikeController extends Controller
{
    public function like(LikeRequest $request): JsonResponse
    {
        $userId = $request->input('user_id');
        $courseId = $request->input('course_id');

        auth()->loginUsingId($userId); 

        $course = $request->likeable();

        auth()->user()->like($course);

        return response()->json([
            'user_id' => $userId,
            'course_id' => $courseId,
            'likes' => $course->likes()->count(),
            'message' => 'Liked course successfully',
            'status' => 200
        ]);
    }

    public function unlike(UnlikeRequest $like)
    {
        $like->user()->unlike($like->likeable());

        return response()->json([
            'likes' => $like->likeable()->likes()->count(),
            'message' => 'Remove from wishlists successfully',
        ]);
    }
}
