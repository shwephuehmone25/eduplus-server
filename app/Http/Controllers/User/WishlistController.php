<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function addToWishlist(Request $request)
    {
        $request->validate([
            'allocation_id' => 'required|exists:allocations,id',
        ]);
    
        $user = auth()->user();
        $allocationId = $request->input('allocation_id');
    
        if ($user->wishlist()->where('allocation_id', $allocationId)->exists()) 
        {
            return response()->json(['message' => 'Item is already in the wishlist']);
        }
    
        $user->wishlist()->attach($allocationId);
    
        return response()->json(['message' => 'Item added to wishlist']);
    }

    public function removeFromWishlist(Request $request)
    {
        $request->validate([
            'allocation_id' => 'required|exists:allocations,id',
        ]);

        $user = auth()->user();
        $allocationId = $request->input('allocation_id');

        $wishlistItem = $user->wishlist()->where('allocation_id', $allocationId)->first();

        if ($wishlistItem)
         {
            $user->wishlist()->detach($allocationId);

            return response()->json(['message' => 'Item removed from wishlist']);
        } else {
            return response()->json(['message' => 'Item not found in the wishlist']);
        }
    }

    public function getWishlist()
    {
        $user = auth()->user();

        $wishlistItems = $user->wishlist()->with('allocation.course.categories')->get();

        $courses = $wishlistItems->map(function ($item) {
            return [
                'course' => $item->allocation->course,
                'category' => $item->allocation->course->category,
            ];
        });

        return response()->json(['courses' => $courses]);
    }

    public function getAllWishlists()
    {
        $user = auth()->user();

        $wishlistItems = $user->wishlist()->with('course.categories')->get();

        return response()->json(['wishlists' => $wishlistItems]);
    }
}
