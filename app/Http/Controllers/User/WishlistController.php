<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function addToWishlist(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);
    
        $user = auth()->user();
        $courseId = $request->input('course_id');
    
        if ($user->wishlist()->where('course_id', $courseId)->exists()) 
        {
            return response()->json(['message' => 'Item is already in the wishlist']);
        }
    
        $user->wishlist()->create(['course_id' => $courseId]);
    
        return response()->json(['message' => 'Item added to wishlist']);
    }

    public function removeFromWishlist(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);
    
        $user = auth()->user();
        $courseId = $request->input('course_id');
    
        $wishlistItem = $user->wishlist()->where('course_id', $courseId)->first();
    
        if ($wishlistItem)
        {
            $wishlistItem->delete(); 
    
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

        $wishlistItems = $user->wishlist()->with('course.allocations', 'course.categories')->get();

        return response()->json(['wishlists' => $wishlistItems]);
    }
}
