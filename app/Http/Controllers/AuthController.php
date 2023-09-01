<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Admin;
use App\Models\Otp;
use Twilio\Rest\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Validator;
use App\Notifications\AccountVerification;
    
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
        try {
            $data = $request->validate([
                'phone_number' => ['required', 'numeric', 'unique:users'],
            ]);
    
            /* Get credentials from .env */
            $token = getenv("TWILIO_AUTH_TOKEN");
            $twilio_sid = getenv("TWILIO_SID");
            $twilio_verify_sid = getenv("TWILIO_VERIFY_SID");
            $twilio = new Client($twilio_sid, $token);
    
            // Send verification code via Twilio
            $twilio->verify->v2->services($twilio_verify_sid)
                ->verifications
                ->create($data['phone_number'], "sms");
    
            // Create a new user
            DB::beginTransaction();
            User::create([
                'name' => 'your name',
                'phone_number' => $data['phone_number'],
                'dob' => '2000-01-01',
                'password' => '11111111',
            ]);
            DB::commit();
    
            return response()->json([
                'message' => 'Register Success!',
                'data' => $data['phone_number']
            ]);
        } catch (ValidationException $e) {

            // Validation failed
            return response()->json([
                'error' => $e->errors(),
                'status' => 422]);
        } 
    }

    /**
     * Verify the phone number using the verification code.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify(Request $request)
    {
        $data = $request->validate([
            'verification_code' => ['required', 'numeric'],
            'phone_number' => ['required', 'string'],
        ]);
    
        // Custom verification logic
        $isValidCode = $this->customVerificationLogic($data['verification_code'], $data['phone_number']);
    
        if ($isValidCode) {
            $user = User::where('phone_number', $data['phone_number'])->first();
            if ($user) {
                $user->isVerified = true;
                $user->save();

                // Save the OTP in the 'otps' table
                $otp = new Otp([
                    'user_id' => $user->id,
                    'otp' => $data['verification_code'],
                    'is_verified' => true,
                ]);
                $otp->save();
    
                // Send the AccountVerification notification
                $user->notify(new AccountVerification);
    
                return response()->json(['message' => 'Successfully Registered']);
            }
            return response()->json(['error' => 'User not found.', 'status' => 404]);
        }
    
        return response()->json(['error' => 'Invalid verification code entered!', 'status' => 400]);
    }

    private function customVerificationLogic($verificationCode, $phoneNumber)
    {
        // Retrieve the OTP from the database based on the phone number
        $user = User::where('phone_number', $phoneNumber)->first();

        if (!$user) {

            return false; // User not found, verification fails
        }
        
        $validVerificationCode = $user->otp;

        // Compare the provided verification code with the OTP from the database
        return $verificationCode === $validVerificationCode;
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createUser(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'dob' => 'required|date_format:Y-m-d',
            'phone_number' => 'nullable|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            'gender' => 'required|in:male,female,other',
            'region' => 'required'
        ]);

        $user = Auth::user();

        $user = User::create([
            'name' => $data['name'],
            'password' => Hash::make($data['password']),
            'phone_number' => $data['phone_number'],
            'dob' => $data['dob'],
            'gender' => $data['gender'],
            'region' => $data['region']
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Create a new admin with the provided data
        $data = $validator->validated();
        $data['password'] = bcrypt($data['password']); // Hash the password before saving
        $admin = Admin::create($data);

        // Generate a new API token for the registered admin
        $token = $admin->createToken('admin-token')->plainTextToken;

        // Return the token as a response
        return response()->json(['token' => $token, 'data' => $data, 'status' => 201]);
    }
}
