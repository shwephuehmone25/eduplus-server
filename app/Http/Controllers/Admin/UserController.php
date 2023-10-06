<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Otp;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Get all users
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllUsers()
    {
        $users = User::where('isVerified', 1)->with('images')->get();

        return response()->json(['data' => $users, 'status' => 200]);
    }
}
