<?php

namespace App\Http\Controllers\Auth;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Admin;
use App\Models\Phone;
use App\Models\Teacher;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('guest:admin')->except('logout');
    }
   /**
     * Handle an admin login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function loginAsAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|ends_with:@ilbcedu.com',
            'password' => 'required',
        ]);

        if ($validator->fails()) {

            return response()->json(['error' => 'Invalid login credentials'], 422);
        }

        $credentials = $validator->validated();
        $admin = Admin::where('email', $credentials['email'])->first();

        if ($admin && password_verify($credentials['password'], $admin->password)) {
    
            $token = $admin->createToken('admin-token')->plainTextToken;

            return response()->json(['token' => $token, 'data' => $admin, 'status' => 200]);
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
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|ends_with:@ilbcedu.com|exists:teachers,email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {

            return response()->json(['error' => 'Invalid login credentials'], 422);
        }

        $credentials = $validator->validated();
        $teacher = Teacher::where('email', $credentials['email'])->first();

        if ($teacher && password_verify($credentials['password'], $teacher->password)) 
        {
            $token = $teacher->createToken('teacher-token')->plainTextToken;

            return response()->json(['token' => $token, 'data' => $teacher, 'status' => 200]);
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
            $validator = Validator::make($request->only('phone_number', 'password'), [
            'phone_number' => ['required', 'regex:/^[0-9]+$/'],
            'password' => ['required', 'min:6', 'max:255', 'string'],
        ]);

        if ($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $credentials = $validator->validated();

        //Phone number without country code

        $phoneNumber = ltrim($credentials['phone_number'], '+0');
        $phone = Phone::where('phone_number', 'LIKE', '%' . $phoneNumber)->first();
        $user = $phone->user;

        if (!$user) {
            return response()->json(['error' => 'User not found', 'status' => 404]);
        }

        if ($user->status === 0) {
            Auth::logout();
            return response()->json(['message' => 'Your account is restricted, please contact the administrator']);
        }

        if (password_verify($credentials['password'], $user->password)) 
        {
            $token = $user->createToken('user-token')->plainTextToken;

            $responseData = [
                'token' => $token,
                'user' => $user,
                'status' => 200
            ];

            return response()->json(['data' => $responseData]);
        }

        return response()->json(['error' => 'Unauthorized', 'status' => 401]);
    }
}
