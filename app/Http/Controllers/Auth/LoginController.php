<?php

namespace App\Http\Controllers\Auth;

use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;

class LoginController extends Controller
{
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
            'email' => 'required|email',
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

            // Return the token as a response
            return response()->json(['token' => $token], 200);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }
}
