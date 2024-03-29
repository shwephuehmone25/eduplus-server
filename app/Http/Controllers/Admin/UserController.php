<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Otp;
use App\Models\User;
use App\Models\Image;
use App\Models\Phone;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\StudentModule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Notifications\AccountVerification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;


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

    return response()->json(['data' => $users], 200);
    }

    /**
     * count total authors
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function countAuthors() 
    {
    $users = User::where('isVerified', 1)->count();

    return response()->json(['data' => $users], 200);
    }

    /**
     * Get all users
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllUsers()
    {
        $users = User::all();

        return response()->json(['data' => $users, 'status' => 200], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showUserDetails($id)
    {
        $user = User::findOrFail($id);
        $user['phone_number'] = $user->phone->phone_number;

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
    public function editProfileDetail(Request $request, $userId)
    {
        $user = User::find($userId);

        if(!$user)
        {
            return response()->json(['message' => 'User not found!'], 404);
        }

        $request->validate([
            'name' => 'required|string',
            'image_url' => 'required|string'
        ]);

        $user->name = $request->input('name');
        $user->image_url = $request->input('image_url');
        $user->save();

        return response()->json(['message' => 'User profile updated successfully!'], 200);
    }

    /**
     * Change the password for a user.)
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

        $phone = Phone::where('phone_number', $request->input('phone_number'))->first();

        if(!$phone || !$phone->user)
        {
            return response()->json(['message' => 'Phone number cannot be found!', 'status' => 404]);
        }

        if ($phone->user && $phone->phone_status === 'verified') {
            $phone->update([
                'phone_number' => $request->input('phone_number'),
            ]);
        } 

        $phone_id = $phone->id;

        $existingPhoneId = Otp::where('phone_id', $phone_id)->first();
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        if($existingPhoneId){
            $existingPhoneId->update([
                'otp' => $otp,
                'expired_at' => Carbon::now()->addSeconds(600)
            ]);
        }else{
            $data = Otp::create([
                'otp' => $otp,
                'phone_id' => $phone_id,
                'expired_at' => Carbon::now()->addSeconds(600),
            ]);
        }

        if ($phone)
        {
            $phone->notify(new AccountVerification($otp));
        }

        $user_id = $phone->user->id;

        return response()->json([
            'message' => 'OTP sent successfully!',
            'phone_id' => $phone_id,
            'user_id'  => $user_id,
            'expired_at' => $data->expired_at ?? $existingPhoneId->expired_at,
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
    public function verifyCurrentPhone(Request $request, $id){
        $user = User::find($id);

        $validator = Validator::make($request->all(), [
            'current_phone' => ['required', 'numeric'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
                'status' => 422
            ]);
        }

        $current_phone = Phone::where('phone_number', $request->input('current_phone'))->first();

        if(!$current_phone || !$current_phone->user)
        {
            return response()->json(['message' => 'Current Phone number is incorrect!', 'status' => 404]);
        }

        if ($current_phone->id == $user->phone_id) {
            $current_phone->update([
                'phone_number' => $request->input('current_phone')
            ]);
        } 

        $phone_id = $current_phone->id;

        $existingPhoneId = Otp::where('phone_id', $phone_id)->first();
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        if($existingPhoneId){
            $existingPhoneId->update([
                'otp' => $otp,
                'expired_at' => Carbon::now()->addSeconds(600)
            ]);
        }else{
            $data = Otp::create([
                'otp' => $otp,
                'phone_id' => $phone_id,
                'expired_at' => Carbon::now()->addSeconds(600),
            ]);
        }

        if ($current_phone)
        {
            $current_phone->notify(new AccountVerification($otp));
        }

        return response()->json([
            'message' => 'OTP sent successfully!',
            'phone_id' => $phone_id,
            'expired_at' => $data->expired_at ?? $existingPhoneId->expired_at,
            'status' => 200
        ]);
    }


    public function updatePhone(Request $request, $id) {
        $phoneId = $request->input('phone_id');
        
        $user = User::find($id);
        $phone = Phone::find($phoneId);
    
        if (!$phone) {
            return response()->json([
                'message' => 'Phone number not found',
                'status'  => 404
            ]);
        }
    
        $validator = Validator::make($request->all(), [
            'phone_number' => ['required', 'numeric'],
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
                'status' => 422
            ]);
        }
    
        if ($phone->phone_status === 'verified') {
            $user->phone_id = $phone->id;
            $user->save();
    
            return response()->json([
                'message' => 'Phone number updated successfully!',
                'user'    => $user,
                'status'  => 200
            ]);
        }
    }    
    

    
    public function restrict(Request $request, $id)
    {

        $user = User::findOrFail($id);

        if(!$user){
            return response()->json(['warning' =>  'User not found', 'status' => 404]);
        }

        $user->status = $request->status;
        $user->save();
        return response()->json(['message' => 'User status updated successfully!', 'status' => 200]);
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);

        if(!$user){
            return response()->json(['error' => 'User not found!', 'status' => 404]);
        }

        $user->delete();
    }

    public function registrationsChart(): JsonResponse
    {
        $users = User::selectRaw('DATE_FORMAT(created_at, "%M") as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderByRaw('MIN(MONTH(created_at))') 
            ->get();

        return response()->json(['data' => $users]);
    }

    public function createUserByAdmin(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|numeric',
            'name'         => 'required',
            'dob'          => 'required',
            'gender'       => 'required|in:male,female,other',
            'region'       => 'required',
            'address'      => 'required'
        ]);

        $existingPhone = Phone::where('phone_number', $request->input('phone_number'))->first();
        
        if ($existingPhone) {
            if ($existingPhone->user && $existingPhone->phone_status === 'verified') {
                return response()->json([
                    'error' => 'Phone number is already in use by a user!',
                    'status' => 422
                ]);
            } else {
                $existingPhone->update([
                    'phone_number' => $request->input('phone_number'),
                    'phone_status' => 'verified'
                ]);
            }
        } else {
            $phone = Phone::create([
                'phone_number' => $request->input('phone_number'),
                'phone_status' => 'verified'
            ]);

            $phone->save();
        }

        $user = new User([
            'name'      => $request->input('name'),
            'dob'       => $request->input('dob'),
            'gender'    => $request->input('gender'),
            'region'    => $request->input('region'),
            'address'   => $request->input('address'),
            'password'  => Hash::make('P@ssword123')  
        ]);
        $phone->user()->save($user);

        return response()->json(['message' => 'User Created successfully!', 'password' => 'P@ssowrd123'], 201);
    }
    

    public function moduleFinish(Request $request, $id)
    {
        $studentModule = StudentModule::findOrFail($id);

        if(!$studentModule)
        {
            return response()->json(['message' => 'This student and module cannot be found'], 404);
        }

        $studentModule->is_complete = ($studentModule->is_complete == false) ? true : false;
        $studentModule->save();

        return response()->json(['message' => 'Student module status changed successfully!'], 200);
    }

    public function displayStudentModule()
    {
        $data = StudentModule::with('course', 'rank')->get();

        return response()->json(['data' => $data], 200);
    }
}