<?php

namespace App\Http\Controllers\Teacher;

use Exception;
use App\Models\Teacher;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class AccountController extends Controller
{
    public function redirectToGoogle()
    {
        return response()->json([
            'url' => Socialite::driver('google')->stateless()->redirect()->getTargetUrl(),
        ]);
    }

    public function handleGoogleCallback(Request $request)
{
    try {
        // Obtain user details from Google using Socialite
        $newTeacher = Socialite::driver('google')->stateless()->user();

        // Exchange authorization code for an access token
        $client = new Client();

        $response = $client->post('https://oauth2.googleapis.com/token', [
            'form_params' => [
                'code' => $newTeacher->token,
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'redirect_uri' => 'http://localhost:8000/auth/google/callback',
                'grant_type' => 'authorization_code',
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        $accessToken = $data['access_token'];

    } catch (Exception $e) {
        
        return response()->json(['error' => 'Google authentication failed.'], 401);
    }

    // Validate the user data
    $validator = Validator::make(
        ['email' => $newTeacher->getEmail()],
        ['email' => 'required|email|unique:teachers,email']
    );

    if ($validator->fails()) {
        throw new UnprocessableEntityHttpException('Teacher with this email already exists.');
    }

    // Create or retrieve the teacher record
    $teacher = Teacher::firstOrCreate(
        ['email' => $newTeacher->getEmail()],
        [
            'email_verified_at' => now(),
            'name' => $newTeacher->getName(),
            'google_id' => $newTeacher->getId(),
            'avatar' => $newTeacher->getAvatar(),
        ]
    );

    return response()->json([
        'teacher' => $teacher,
        'access_token' => $accessToken,
        'token_type' => 'Bearer',
    ]);
}
}
