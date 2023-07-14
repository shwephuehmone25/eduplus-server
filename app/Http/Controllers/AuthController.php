<?php

namespace App\Http\Controllers;

use Exception;
use Twilio\Rest\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Create a new user instance after a valid registration.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    protected function create(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'numeric', 'unique:users'],
            'dob' => ['date_format:d-m-Y','before:today','nullable'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        /* Get credentials from .env */
        $token = getenv("TWILIO_AUTH_TOKEN");
        $twilio_sid = getenv("TWILIO_SID");
        $twilio_verify_sid = getenv("TWILIO_VERIFY_SID");
        $twilio = new Client($twilio_sid, $token);

        try {
            $verification = $twilio->verify->v2->services($twilio_verify_sid)
                ->verificationChecks
                ->create(['code' => $data['verification_code'], 'to' => $data['phone_number']]);
        } catch (\Exception $e) {

            return response()->json(['error' => 'Failed to verify the code.'], 500);
        }

        User::create([
            'name' => $data['name'],
            'phone_number' => $data['phone_number'],
            'dob' => $data['dob'],
            'password' => Hash::make($data['password']),
        ]);

        return response()->json(['message' => 'Verification code sent successfully.']);
    }

    /**
     * Verify the user's phone number.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
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

        try {
            $verification = $twilio->verify->v2->services($twilio_verify_sid)
                ->verificationChecks
                ->create(['code' => $data['verification_code'], 'to' => $data['phone_number']]);
        } catch (\Exception $e) {
            
            return response()->json(['error' => 'Failed to verify the code.'], 500);
        }

        if ($verification->valid) {
            $user = User::where('phone_number', $data['phone_number'])->first();
            $user->isVerified = true;
            $user->save();

            /* Authenticate user */
            Auth::login($user);

            return response()->json(['message' => 'Phone number verified.']);
        }

        return response()->json(['error' => 'Invalid verification code entered.'], 422);
    }
}
