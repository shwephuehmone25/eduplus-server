<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Admin;
use Twilio\Rest\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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
                'phone_number' => $data['phone_number']
            ]);
        } catch (ValidationException $e) {

            // Validation failed
            return response()->json([
                'error' => $e->errors(),
            ], 422);
        } 
        // catch (\Exception $e) {
        //     // Other exceptions, including Twilio errors or database errors
        //     DB::rollBack(); 
            
        //     return response()->json([
        //         'error' => 'An error occurred while processing your request.',
        //     ], 500);
        // }
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
    public function createUser(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'dob' => 'required|date_format:Y-m-d',
            'phone_number' => 'nullable|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        $user = User::create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => Hash::make($data->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return response()->noContent();
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
        $token = $admin->createToken('admin-token')->accessToken;

        // Return the token as a response
        return response()->json(['token' => $token], 201);
    }
}
