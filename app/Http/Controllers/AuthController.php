<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Admin;
use App\Models\Otp;
use Twilio\Rest\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Validator;
use App\Notifications\AccountVerification;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Auth;

class AuthController extends Controller
{
    /**
     * Create a new user instance with phone number.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => ['required', 'numeric', 'unique:users'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
                'status' => 422
            ]);
        }

        $user = User::create([
            'name' => 'your name',
            'dob' => '2000-01-01',
            'password' => 11111111,
            'phone_number' => $request->input('phone_number'),
            'address' => 'default',
            'region' => 'default',
            'image_url' => 'https://eduplus-test.s3.ap-southeast-1.amazonaws.com/students/user_default.jpg'
        ]);

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
     * Verify the phone number using the verification code.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify(Request $request, $userId)
    {
        $data = $request->validate([
            'verification_code' => ['required', 'numeric'],
        ]);

        $user = User::find($userId);

        $verificationCode   = Otp::where('user_id', $user->id)->where('otp', $data['verification_code'])->first();

        if($verificationCode){
            $user->isVerified = true;
            $user->save();

            return response()->json(['message' => 'Verification successful!', 'user_id' => $user->id, 'status' => 200]);
        }

        return response()->json(['error' => 'Invalid verification code entered!', 'status' => 400]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createUser(Request $request, $userId)
    {
        $user = User::find($userId);

        if($user->isVerified === 1)
        {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'dob' => 'required|date_format:Y-m-d',
                'password' => 'required|string|min:8|confirmed',
                'gender' => 'required|in:male,female,other',
                'region' => 'required',
                'address' => 'required'
            ]);

            $user->update([
                'name' => $data['name'],
                'password' => Hash::make($data['password']),
                'dob' => $data['dob'],
                'address' => $data['address'],
                'gender' => $data['gender'],
                'region' => $data['region'],
            ]);

            $token = $user->createToken('student-token')->plainTextToken;

            $response = [
                'user' => $user,
                'token' => $token,
                'gender_options' => User::getGenderOptions(),
                'region_values' => User::getRegionValues()
            ];

            event(new Registered($user));

            return response()->json(['data' => $response , 'status' => 201]);
        }else{

            return response()->json(['message' => 'Please verify first']);
        }
    }

    /**
     * Handle an admin registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function registerAsAdmin(Request $request)
    {
        // Validate the incoming registration request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:admins',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:super_admin,normal_admin', // Validate the role
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Create a new admin with the provided data
        $data = $validator->validated();
        $data['password'] = bcrypt($data['password']);

        $admin = Admin::create([
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
        ]);

        // Generate a new API token for the registered admin
        $token = $admin->createToken('admin-token')->plainTextToken;

        // Return the token as a response
        return response()->json(['token' => $token, 'data' => $admin, 'status' => 201]);
    }
}
