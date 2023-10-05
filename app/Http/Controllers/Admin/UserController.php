<?php

namespace App\Http\Controllers\Admin;

use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Notifications\AccountVerification;
use App\Models\Image;
use Illuminate\Support\Facades\Storage;
class UserController extends Controller
{
    /**
     * Get all users
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllUsers()
    {
        $users = User::where('isVerified', 1)->get();

        return response()->json(['data' => $users, 'status' => 200]);
    }

     /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showUserDetails($id)
    {
        $user = User::with('images','allocations')
                ->find($id);

        if (!$user) {

            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json(['data' => $user]);
    }

    public function editProfile(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string',
            'phone_number' => 'required|string',
            'dob' => 'required|date',
            'gender' => 'required|string',
            'image' => 'image|mimes:jpeg,png,jpg,gif',
        ]);

        $user->name = $request->input('name');
        $user->phone_number = $request->input('phone_number');
        $user->dob = $request->input('dob');
        $user->gender = $request->input('gender');
        $user->save();

        if ($request->hasFile('image')) {
            $s3Path = Storage::disk('s3')->put('students', $request->file('image'));

            $image = new Image();
            $image->url = Storage::disk('s3')->url($s3Path);
            $user->images()->save($image);

            $user->load('images');
        }

        return response()->json(['message' => 'User info updated successfully', 'data' => $user, 'status' => 200]);
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
            if(!Hash::check($request->old_password, $user->password))
            {
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

        if(!$user)
        {
            return response()->json(['message' => 'User not found!', 'status' => 404]);
        }

        $user_id = $user->id;

        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        Otp::create([
            'otp' => $otp,
            'user_id' => $user_id,
        ]);

        if ($user)
        {
            $user->notify(new AccountVerification($otp));
        }

        return response()->json([
            'message' => 'OTP sent successfully!',
            'user_id' => $user_id,
            'status' => 200
        ]);
    }
}
