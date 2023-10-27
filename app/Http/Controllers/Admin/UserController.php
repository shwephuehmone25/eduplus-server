<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Notifications\AccountVerification;
use Illuminate\Support\Facades\Auth;
use App\Models\Image;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * count verified users
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function countVerifiedUsers() 
    {
    $users = User::where('isVerified', 1)->count();

    return response()->json(['data' => $users]);
    }

    /**
     * count total authors
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function countAuthors() 
    {
    $users = User::where('isVerified', 1)->count();

    return response()->json(['data' => $users]);
    }

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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showUserDetails($id)
    {
        $user = User::find($id);

        if (!$user) {

            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json(['data' => $user]);
    }

    /**
     * Upload a user's profile image.
     *
     * Upload and store a user's profile image to a storage service (e.g., Amazon S3) and
     * save the image URL to the database.
     *
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function uploadProfile(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'image' => 'required|file|mimes:jpg,jpeg,png|max:5120',
            ]);
    
            if ($validator->fails()) {
                $errors = $validator->errors()->all();
                return response()->json(['message' => 'Validation failed', 'errors' => $errors, 'status' => 422], 422);
            }
    
            if ($request->hasFile('image')) {
                $s3Path = Storage::disk('s3')->put('students', $request->file('image'));
    
                $image = new Image();
                $image->url = Storage::disk('s3')->url($s3Path);
                $image->save();
    
                return response()->json(['message' => 'Image file uploaded successfully!', 'data' => $image, 'status' => 201]);
            }else{
                return response()->json(['message' => 'No file uploaded!', 'status' => 400]);
            }
        }
        catch(\Exception $e){
            return response()->json(['message' => 'Failed to upload image', 'error' => $e->getMessage(), 'status' => 500]);
        }
    }

    /**
     * Edit a user's profile information.
     *
     * Update the user's profile information, such as name, phone number, date of birth,
     * gender, region, address, and image URL.
     *
     * @param  Request $request
     * @param  int $userId The user's ID
     * @return \Illuminate\Http\Response
     */
    public function editProfile(Request $request, $userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['message' => 'User not found', 'status' => 404], 404);
        }

        $request->validate([
            'name' => 'required|string',
            'phone_number' => 'required|string',
            'dob' => 'required|date',
            'address' => 'required|string',
            'region' => 'required|string',
            'address' => 'required|string',
            'image_url' => 'required|string',
        ]);

        $user->name = $request->input('name');
        $user->phone_number = $request->input('phone_number');
        $user->dob = $request->input('dob');
        $user->gender = $request->input('gender');
        $user->region = $request->input('region');
        $user->address = $request->input('address');
        $user->image_url = $request->input('image_url');
        $user->save();

        return response()->json(['message' => 'User info updated successfully', 'data' => $user, 'status' => 200]);
    }

    /**
     * Change the password for a user.
     *
     * Update the user's password identified by their ID with a new password
     * after verifying the old password.
     *
     * @param  Request $request
     * @param  int $id The user's ID
     * @return \Illuminate\Http\Response
     */
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

    /**
     * Send a One-Time Password (OTP) for password reset to a user's phone number.
     *
     * Find a user by their phone number, generate and send an OTP for password reset.
     *
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
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

    /**
     * Reset the password for a user.
     *
     * Update the user's password identified by their ID with a new password.
     *
     * @param  Request $request
     * @param  int $id The user's ID
     * @return \Illuminate\Http\Response
     */
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

    /**
     * Get users by category ID.
     *
     * Retrieve a list of users who are associated with a specific category ID.
     *
     * @param  Category $category
     * @return \Illuminate\Http\Response
     */
    public function getUsersByCategoryId(Category $category)
    {
        $categoryId = $category->id;
        $users = User::whereHas('allocations', function ($query) use ($categoryId) {
            $query->whereHas('course.categories', function ($subQuery) use ($categoryId) {
                $subQuery->where('category_id', $categoryId);
            });
        })->get();

        $count = $users->count();

        return response()->json(['data' => $users, 'count' => $count, 'status' => 200]);
    }

    /**
     * Change the phone number for a user.
     *
     * Update the phone number for a user identified by their ID and send an OTP
     * (One-Time Password) for verification.
     *
     * @param  Request $request
     * @param  int $id The user's ID
     * @return \Illuminate\Http\Response
     */
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