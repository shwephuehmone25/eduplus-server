<?php

namespace App\Http\Controllers\Teacher;

use Exception;
use Google_Client;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class AccountController extends Controller
{
    // public function redirectToGoogle(): JsonResponse
    // {
    //     return response()->json([
    //         'url' => Socialite::driver('google')->stateless()->redirect()->getTargetUrl(),
    //     ]);
    // }  

    public function redirectToGoogle(): JsonResponse
{
    $url = Socialite::driver('google')
        ->stateless()
        ->with(['access_type' => 'offline'])
        ->redirect()
        ->getTargetUrl();

    return response()->json([
        'url' => $url,
    ]);
}

    public function handleGoogleCallback(): JsonResponse
    {
        try {
            /** @var SocialiteTeacher $socialiteTeacher */
            $socialiteTeacher = Socialite::driver('google')->stateless()->user();
            
            // Validate email domain
            $email = $socialiteTeacher->getEmail();
            $allowedDomain = '@ilbcedu.com';
            if (strpos($email, $allowedDomain) === false) {
                
                return response()->json(['error' => 'Unauthorized email domain.'], 403);
            }
        } catch (ClientException $e) {

            return response()->json(['error' => 'Invalid credentials provided.'], 422);
        }

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

            if ($teacher) {
                // Update existing teacher's Google ID and avatar
                $teacher->update([
                    'google_id' => $socialiteTeacher->getId(),
                    'avatar' => $socialiteTeacher->getAvatar(),
                    'access_token' => $socialiteTeacher->token,
                    'refresh_token' => $socialiteTeacher->refreshToken
                ]);
            }

            $teacher->access_token = $socialiteTeacher->token;
            $teacher->refresh_token = $socialiteTeacher->refreshToken;
            $teacher->save();

        return response()->json([
            'teacher' => $teacher,
            // 'access_token' => $teacher->createToken('google-token')->plainTextToken,
            'google_access_token'   => $socialiteTeacher->token,
            'token_type' => 'Bearer',
        ]);
    }  

    public function googleLogin(Request $request)
    {    
        try {
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
    
            // Find teacher by email
            $teacher = Teacher::where('email', $request->input('email'))->first();
    
            if ($teacher) {
                // Update existing teacher's Google ID and avatar
                $teacher->update([
                    'google_id' => $request->input('google_id'),
                    'avatar' => $request->input('avatar'),
                    'access_token' => $request->input('access_token'),
                    'refresh_token' => $request->input('refresh_token'),
                ]);
    
                // Generate a new API token for the authenticated teacher
                $token = $teacher->createToken('teacher-token')->plainTextToken;

                return response()->json([
                    'token' => $token,
                    'data' => $teacher,
                    'message' => 'Google data updated successfully',
                    'status' => 200,
                ]);
            } else {
                // Create a new teacher record
                $newTeacher = [
                    'google_id' => $request->input('google_id'),
                    'name' => $request->input('name'),
                    'email' => $request->input('email'),
                    'email_verified_at' => now(),
                    'avatar' => $request->input('avatar'),
                    'access_token' => $request->input('access_token'),
                    'refresh_token' => $request->input('refresh_token'),
                ];
    
                $teacher = Teacher::create($newTeacher);

                // Generate a new API token for the authenticated teacher
                $token = $teacher->createToken('teacher-token')->plainTextToken;
    
                return response()->json([
                    'token' => $token,
                    'data' => $teacher,
                    'message' => 'Google data stored successfully',
                    'status' => 201,
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
