<?php

namespace App\Http\Controllers;

use App\Models\User;
use Twilio\Rest\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Create a new user instance with phone number.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getStart(Request $request)
    {
        $data = $request->validate([
            'phone_number' => ['required', 'numeric', 'unique:users'],
        ]);
        
        /* Get credentials from .env */
        $token = getenv("TWILIO_AUTH_TOKEN");
        $twilio_sid = getenv("TWILIO_SID");
        $twilio_verify_sid = getenv("TWILIO_VERIFY_SID");
        $twilio = new Client($twilio_sid, $token);
        $twilio->verify->v2->services($twilio_verify_sid)
            ->verifications
            ->create($data['phone_number'], "sms");

        User::create([
            'name' => 'your name',
            'phone_number' => $data['phone_number'],
            'dob' => '2000-01-01',
            'password' => '11111111',
        ]);
        
        return response()->json(['phone_number' => $data['phone_number']]);
    }

    /**
     * Verify the phone number using the verification code.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    protected function verify(Request $request)
    {
        $data = $request->validate([
            'verification_code' => ['required', 'numeric'],
            'phone_number' => ['required', 'string'],
        ]);
        
        /* Get credentials from .env */
        $token = getenv("TWILIO_AUTH_TOKEN");
        $twilio_sid = getenv("TWILIO_SID");
        $twilio_verify_sid = getenv("TWILIO_VERIFY_SID");
        $twilio = new Client($twilio_sid, $token);
        $verification = $twilio->verify->v2->services($twilio_verify_sid)
            ->verificationChecks
            ->create(['code' => $data['verification_code'], 'to' => $data['phone_number']]);

        if ($verification->valid) {
            $user = User::where('phone_number', $data['phone_number'])->first();
            if ($user) {
                $user->isVerified = true;
                $user->save();
                return response()->json(['message' => 'Successfully Registered']);
            }
            return response()->json(['error' => 'User not found.'], 404);
        }
        
        return response()->json(['error' => 'Invalid verification code entered!'], 400);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createUser(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'dob' => 'required|date_format:Y-m-d',
            'phone_number' => 'nullable|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if ($user) {
            $user->name = $data['name'];
            $user->dob = $data['dob'];
            $user->password = Hash::make($data['password']);

            $user->save();

            Auth::login($user->first());
            
            return response()->json(['message' => 'Register Success']);
        }

        return response()->json(['error' => 'User not found.'], 404);
    }
}
