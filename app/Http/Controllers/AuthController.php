<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Otp;
use App\Models\User;
use App\Models\Admin;
use App\Models\Phone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Validator;
use App\Notifications\AccountVerification;
use Google\Service\Testing\UsesFeature;

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
            'phone_number' => ['required', 'numeric'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
                'status' => 422
            ]);
        }

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
                    'phone_status' => 'invalidate'
                ]);
                $phone_id = $existingPhone->id;
            }
        } else {
            $phone = Phone::create([
                'phone_number' => $request->input('phone_number'),
            ]);

            $phone_id = $phone->id;
        }

        $existingPhoneId = Otp::where('phone_id', $phone_id)->first();
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        if ($existingPhoneId) {
            $existingPhoneId->update([
                'otp' => $otp,
                'expired_at' => Carbon::now()->addSeconds(600)
            ]);
        } else {
            $data = Otp::create([
                'otp' => $otp,
                'phone_id' => $phone_id,
                'expired_at' => Carbon::now()->addSeconds(600),
            ]);
        }

        if ($phone_id) {
            $phone = Phone::find($phone_id);
            $phone->notify(new AccountVerification($otp));
        }

        return response()->json([
            'message' => 'OTP sent successfully!',
            'phone_id' => $phone_id,
            'phone_number'  => $phone->phone_number ?? $existingPhone->phone_number,
            'status' => 200,
            'expired_at' => $data->expired_at ?? $existingPhoneId->expired_at
        ], 200);
    }

    /**
     * Verify the phone number using the verification code.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify(Request $request, $phoneId)
    {
        $data = $request->validate([
            'verification_code' => ['required', 'numeric'],
        ]);

        $phone = Phone::find($phoneId);

        $verificationCode = Otp::where('phone_id', $phone->id)
            ->where('otp', $data['verification_code'])
            ->where('expired_at', '>', Carbon::now())
            ->first();

        if ($verificationCode) {
            $phone->phone_status = 'verified';
            $phone->save();

            return response()->json(['message' => 'Verification successful!', 'phone_id' => $phone->id, 'status' => 200], 200);
        } elseif ($verificationCode && $verificationCode->expired_at <= Carbon::now()) {
            return response()->json(['message' => 'Expired Verification Code entered!', 'status' => '400'], 400);
        }

        return response()->json(['error' => 'Invalid verification code entered!', 'status' => 400], 400);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createUser(Request $request, $phoneId)
    {
        $phone = Phone::find($phoneId);

        if ($phone->phone_status === 'verified') {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'dob' => 'required',
                'password' => 'required|string|min:8|confirmed',
                'gender' => 'required|in:male,female,other',
                'region' => 'required',
                'address' => 'required'
            ]);

            $existingPhoneId = User::where('phone_id', $phoneId)->first();
            if ($existingPhoneId) {
                return response()->json(['message' => 'User account already created!']);
            } else {
                $user = User::create([
                    'phone_id' => $phone->id,
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
                ];

                event(new Registered($user));

                return response()->json(['data' => $response, 'status' => 201], 201);
            }
        } else {

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
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:admins',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:super_admin,normal_admin',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['password'] = bcrypt($data['password']);

        $admin = Admin::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
        ]);

        $token = $admin->createToken('admin-token')->plainTextToken;

        return response()->json(['token' => $token, 'data' => $admin, 'status' => 201], 201);
    }
}
