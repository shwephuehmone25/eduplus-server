<?php

namespace App\Http\Controllers;

use App\Http\Requests\LikeRequest;
use App\Http\Requests\UnlikeRequest;

class LikeController extends Controller
{
    public function like(LikeRequest $request)
    {
        $request->user()->like($request->likeable());

        return response()->json([
            'likes' => $request->likeable()->likes()->count(),
            'message' => 'Add to wishlists successfully',
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
