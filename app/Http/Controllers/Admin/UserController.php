<?php

namespace App\Http\Controllers\Admin;

use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Notifications\AccountVerification;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Get all news
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllUsers()
    {
        $users = User::where('isVerified', 1)->get();

        return response()->json(['data' => $users, 'status' => 200]);
    }

    public function changePassword(Request $request, $id){

        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:6',
            'confirm_new_password' => 'required|same:new_password'
        ]);

        $user = User::find($id);

        if($user)
        {
            if(!Hash::check($request->old_password, $user->password)){
                return response()->json(['message' => 'Old password is incorrect!', 'status' => 422]);
            }
    
            $user->update(['password' => Hash::make($request->new_password)]);
            return response()->json(['message' => 'Password changed successfully!', 'status' => 200]);
        }

        return response()->json(['error' => 'User not found!', 'status' => 404]);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => ['required', 'numeric'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
                'status' => 422
            ]);
        }

        $user = User::where('phone_number', $request->input('phone_number'))->first();

        if(!$user){
            return response()->json(['message' => 'User not found!', 'status' => 404]);
        }

        $user_id = $user->id;

        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        Otp::create([
            'otp' => $otp,
            'user_id' => $user_id,
        ]);

        if ($user) {
            $user->notify(new AccountVerification($otp));
        }

        return response()->json([
            'message' => 'OTP sent successfully!',
            'user_id' => $user_id,
            'status' => 200
        ]);
    }

    public function resetPassword(Request $request, $id)
    {
        $request->validate([
            'new_password' => 'required|min:6',
            'confirm_new_password' => 'required|same:new_password'
        ]);

        $user = User::find($id);

        if($user)
        {
            $user->update(['password' => Hash::make($request->new_password)]);
            return response()->json(['message' => 'Password changed successfully!', 'status' => 200]);
        }

        return response()->json(['error' => 'User not found!', 'status' => 404]);
    }

    public function getUsersByCategoryId(Category $category)
    {
        $categoryId = $category->id;
        $users = User::join('students_allocations', 'users.id', '=', 'students_allocations.user_id')
                        ->join('allocations', 'allocations.id', 'students_allocations.allocation_id')
                        ->join('courses_categories', 'courses_categories.course_id', 'allocations.course_id')
                        ->where('courses_categories.category_id', $categoryId)
                        ->get();

        $count = count($users);

        return response()->json(['data' => $users, 'count' => $count, 'status' => 200]);
    }

    public function changePhoneNumber(Request $request, $id)
    {
        $user = User::find($id);

        $validator = Validator::make($request->all(), [
            'new_phone_number' => ['required', 'numeric', 'unique:users,phone_number'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
                'status' => 422
            ]);
        }

        $user->update(['phone_number' => $request->input('new_phone_number')]);

        $user_id = $user->id;
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        Otp::create([
            'otp' => $otp,
            'user_id' => $user_id,
        ]);

        if ($user) {
            $user->notify(new AccountVerification($otp));
        }

        return response()->json([
            'message' => 'OTP sent successfully!',
            'user_id' => $user_id,
            'status' => 200
        ]);
    }
}