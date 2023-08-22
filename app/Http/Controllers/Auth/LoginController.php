<?php

namespace App\Http\Controllers\Auth;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Admin;
use App\Models\Teacher;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('guest')->except('logout');
    //     $this->middleware('guest:admin')->except('logout');
    // }
   /**
     * Handle an admin login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function loginAsAdmin(Request $request)
    {
        // Validate the incoming login request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|ends_with:@ilbcedu.com',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            
            return response()->json(['error' => 'Invalid login credentials'], 422);
        }

        // Attempt to authenticate the admin using the given credentials
        $credentials = $validator->validated();
        $admin = Admin::where('email', $credentials['email'])->first();

        if ($admin && password_verify($credentials['password'], $admin->password)) {
            // Generate a new API token for the authenticated admin
            $token = $admin->createToken('admin-token')->accessToken;

            // Retrieve the role from the admin model
            $role = $admin->role;

            // Return the token and role as a response
            return response()->json(['token' => $token, 'role' => $role, 'status' => 200]);
        }

        return response()->json(['error' => 'Unauthorized', 'status' => 401]);
    }

    /**
     * Handle a teacher login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function loginAsTeacher(Request $request)
    {
        // Validate the incoming login request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|ends_with:@ilbcedu.com|exists:teachers,email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            
            return response()->json(['error' => 'Invalid login credentials'], 422);
        }

        // Attempt to authenticate the admin using the given credentials
        $credentials = $validator->validated();
        $teacher = Teacher::where('email', $credentials['email'])->first();

        if ($teacher && password_verify($credentials['password'], $teacher->password)) {
           // Calculate the expiration time (e.g., 7 days from now)
            $expiresAt = Carbon::now()->addDays(7);

            // Set the 'expires_at' value in the teachers table
            $teacher->expires_at = $expiresAt;
            $teacher->save();

            // Generate a new token manually
            $token = bin2hex(random_bytes(32)); // Generates a random token
            
            // Return the token and teacher as a response
            $data = [
                'token' => $token,
                'teacher' => $teacher,
                ];

           // Return the token and role as a response
         return response()->json(['data' => $data, 'status' => 200]);
        }

        return response()->json(['error' => 'Unauthorized', 'status' => 401]);
    }

    /**
     * Handle as student login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function loginAsStudent(Request $request)
    {
        $validator = Validator::make($request->only('email', 'password'), [
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'min:6', 'max:255', 'string'],
        ]);
        if ($validator->fails())

            return response()->json($validator->errors(), 400);
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = $request->user();
            $data =  [
                //'token' => $user->createToken('student-token')->plainTextToken,
                'user' => $user,
            ];

            return response()->json([
             'data' =>  $data,
             'message' => "Login Success", 
             'status' => 200,
            ]);
        }
    }
}
