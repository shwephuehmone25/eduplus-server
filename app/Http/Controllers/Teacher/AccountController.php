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

    public function googleLogin(Request $request)
    {
        // Validate the incoming data
        $validator = Validator::make($request->all(), [
            'google_id' => 'required',
            'name' => 'required',
            'email' => 'required|email|ends_with:@ilbcedu.com',
            'avatar' => 'nullable|url', 
            'access_token' => 'required', 
            'refresh_token' => 'nullable',
        ]);

        if ($validator->fails()) {

            return response()->json(['error' => $validator->errors()], 422);
        }

        // Check if a teacher with the given email exists
        $existingTeacher = Teacher::where('email', $request->input('email'))->first();

        if ($existingTeacher) {
            // Update existing teacher's Google ID and avatar
            $existingTeacher->update([
                'google_id' => $request->input('google_id'),
                'avatar' => $request->input('avatar'),
                'access_token' => $request->input('access_token'),
                'refresh_token' => $request->input('refresh_token'),
            ]);

            return response()->json(['message' => 'Google data updated successfully'], 200);
        }

        // Create a new teacher record
        $teacher = new Teacher([
            'google_id' => $request->input('google_id'),
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'email_verified_at' => now(),
            'avatar' => $request->input('avatar'),
            'access_token' => $request->input('access_token'),
            'refresh_token' => $request->input('refresh_token'),
        ]);

        $teacher->save();

        return response()->json(['message' => 'Google data stored successfully'], 201);
    }
}
