<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\LikeRequest;
use App\Http\Requests\UnlikeRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\Allocation;

class LikeController extends Controller
{
    public function like(LikeRequest $request): JsonResponse
    {
        $user = $request->user();
        $likeable = $request->likeable();

        $user->like($likeable);

        return response()->json([
            'likes' => $likeable->likes()->count(),
            'message' => 'Add to whistlists successfully',
            'status' => 200
        ]);
    }

    public function unlike(UnlikeRequest $like)
    {
        $allocation_id = $request->input('allocation_id');

   
        $like = Like::where('allocation_id', $allocation_id)->first();

        if ($like) 
        {
            $like->user()->unlike($like->likeable());

            return response()->json([
                'likes' => $like->likeable()->likes()->count(),
                'message' => 'Remove from wishlists successfully',
            ]);
        } else {
            return response()->json([
                'message' => 'Like not found.', 
                'status' => 404]);
        }
    }
}

