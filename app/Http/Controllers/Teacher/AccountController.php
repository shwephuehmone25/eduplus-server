<?php

namespace App\Http\Controllers\Teacher;

use Exception;
use Google\Client;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class AccountController extends Controller
{
    public function redirectToGoogle(): JsonResponse
    {
        return response()->json([
            'url' => Socialite::driver('google')->stateless()->redirect()->getTargetUrl(),
        ]);
    }  

    public function handleGoogleCallback(): JsonResponse
    {
        try {
            /** @var SocialiteTeacher $socialiteTeacher */
            $socialiteTeacher = Socialite::driver('google')->stateless()->user();
        } catch (ClientException $e) {
            return response()->json(['error' => 'Invalid credentials provided.'], 422);
        }
        // dd($socialiteTeacher);

        /** @var Teacher $teacher */
        $teacher =Teacher::query()
            ->firstOrCreate(
                [
                    'email' => $socialiteTeacher->getEmail(),
                ],
                [
                    'email_verified_at' => now(),
                    'name' => $socialiteTeacher->getName(),
                    'google_id' => $socialiteTeacher->getId(),
                    'avatar' => $socialiteTeacher->getAvatar(),
                ]
            );

            $teacher->access_token = $socialiteTeacher->token;
            $teacher->refresh_token = $socialiteTeacher->refreshToken;
            $teacher->save();

        return response()->json([
            'teacher' => $teacher,
            'access_token' => $teacher->createToken('google-token')->plainTextToken,
            'google_access_token'   => $socialiteTeacher->token,
            'token_type' => 'Bearer',
            'access_type'   => 'offline'
        ]);
    }  
}
