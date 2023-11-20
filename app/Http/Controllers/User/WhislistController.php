<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WhislistController extends Controller
{
    public function addToWishlist(Request $request)
    {
        $request->validate([
            'allocation_id' => 'required|exists:allocations,id',
        ]);

        $user = auth()->user();
        $allocationId = $request->input('allocation_id');
    
        if ($user->wishlist()->where('allocation_id', $allocationId)->count() === 0) 
        {
            $user->wishlist()->create(['allocation_id' => $allocationId]);
            return response()->json(['message' => 'Item added to wishlist']);
        } else {
            return response()->json(['message' => 'Item is already in the wishlist']);
        }
    }
}
