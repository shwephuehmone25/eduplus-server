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
        $like->user()->unlike($like->likeable());

        return response()->json([
            'likes' => $like->likeable()->likes()->count(),
            'message' => 'Remove from wishlists successfully',
        ]);
    }
}

