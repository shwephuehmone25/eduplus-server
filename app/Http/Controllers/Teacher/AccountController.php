<?php

namespace App\Http\Controllers\Teacher;

use App\Models\Teacher;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\Controller;

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
            $newTeacher = Socialite::driver('google')->stateless()->user();
        } catch (ClientException $e) {
            
            return response()->json(['error' => 'Invalid credentials provided.'], 422);
        }
        $teacher = Teacher::query()
            ->firstOrCreate(
                [
                    'email' => $newTeacher->getEmail(),
                ],
                [
                    'email_verified_at' => now(),
                    'name' => $newTeacher->getName(),
                    'google_id' => $newTeacher->getId(),
                    'avatar' => $newTeacher->getAvatar(),
                ]
            );

        return response()->json([
            'teacher' => $teacher,
            'access_token' => $teacher->createToken('google-token')->plainTextToken,
            'token_type' => 'Bearer',
        ]);
    }
}
