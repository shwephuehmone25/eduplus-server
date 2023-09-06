<?php

namespace App\Http\Controllers\Teacher;

use Google_Service_Calendar;
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
    public function redirectToGoogle(): JsonResponse
    {
        $scopes = [
            Google_Service_Calendar::CALENDAR,
            'https://www.googleapis.com/auth/calendar.events',
            'https://www.googleapis.com/auth/calendar',
            'https://www.googleapis.com/auth/calendar.events.readonly',
            'https://www.googleapis.com/auth/calendar.readonly',
            'https://www.googleapis.com/auth/calendar.settings.readonly'
        ];

        $url = Socialite::driver('google')
            ->scopes($scopes)
            ->stateless()
            ->with(['access_type' => 'offline'])
            ->redirect()
            ->getTargetUrl();

        return response()->json([
            'url' => Socialite::driver('google')->stateless()->redirect()->getTargetUrl(),
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

    /**
     * Check if a teacher with a given email exists.
     *
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkUserExists(Request $request)
    {
       // Retrieve user input (you may want to validate this data)
       $email = $request->input('email');

       // Check if the user already exists in the database
       $existingTeacher = Teacher::where('email', $email)->first();

       if ($existingTeacher) {

           // Teacher already exists
           return response()->json([
               'message' => 'User already exists.',
           ]);
       } else {

           return response()->json([
               'message' => 'User does not exist.',
           ]);
        }    
    }

    public function googleLogin(Request $request)
    {    
        // try {
        //     // Validate the incoming data
        //     $validator = Validator::make($request->all(), [
        //         'google_id' => 'required',
        //         'name' => 'required',
        //         'email' => 'required|email|ends_with:@ilbcedu.com',
        //         'avatar' => 'nullable|url',
        //         'access_token' => 'required',
        //         'refresh_token' => 'nullable',
        //     ]);
    
        //     if ($validator->fails()) {
                
        //         return response()->json(['error' => $validator->errors()], 422);
        //     }
    
        //     // Find teacher by email
        //     $teacher = Teacher::where('email', $request->input('email'))->first();
    
        //     if ($teacher) {
        //         // Update existing teacher's Google ID and avatar
        //         $teacher->update([
        //             'google_id' => $request->input('google_id'),
        //             'avatar' => $request->input('avatar'),
        //             'access_token' => $request->input('access_token'),
        //             'refresh_token' => $request->input('refresh_token'),
        //         ]);
    
        //         // Generate a new API token for the authenticated teacher
        //         $token = $teacher->createToken('teacher-token')->plainTextToken;

        //         return response()->json([
        //             'token' => $token,
        //             'data' => $teacher,
        //             'message' => 'Google data updated successfully',
        //             'status' => 200,
        //         ]);
        //     } else {
        //         // // Create a new teacher record
        //         // $newTeacher = [
        //         //     'google_id' => $request->input('google_id'),
        //         //     'name' => $request->input('name'),
        //         //     'email' => $request->input('email'),
        //         //     'email_verified_at' => now(),
        //         //     'avatar' => $request->input('avatar'),
        //         //     'access_token' => $request->input('access_token'),
        //         //     'refresh_token' => $request->input('refresh_token'),
        //         // ];
    
        //         // $teacher = Teacher::create($newTeacher);

        //         // // Generate a new API token for the authenticated teacher
        //         // $token = $teacher->createToken('teacher-token')->plainTextToken;
    
        //         return response()->json([
        //             // 'token' => $token,
        //             // 'data' => $teacher,
        //             'message' => 'Unauthorized',
        //             'status' => 401,
        //         ]);
        //     }
        // } 
        // catch (Exception $e) {
        //     return response()->json([
        //         'error' => 'An error occurred',
        //         'message' => $e->getMessage(),
        //     ], 500);
        // }
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
    
            // Find or create a teacher by email
            $teacher = Teacher::firstOrCreate(['email' => $request->input('email')], [
                'google_id' => $request->input('google_id'),
                'name' => $request->input('name'),
                'email_verified_at' => now(),
                'avatar' => $request->input('avatar'),
            ]);
    
            // Update the access_token and refresh_token (if provided)
            $teacher->update([
                'access_token' => $request->input('access_token'),
                'refresh_token' => $request->input('refresh_token'),
            ]);
    
            // Authenticate the teacher
            Auth::login($teacher);
    
            // Generate a new API token for the authenticated teacher
            $token = $teacher->createToken('teacher-token')->plainTextToken;
    
            return response()->json([
                'token' => $token,
                'data' => $teacher,
                'message' => 'Google data updated successfully',
                'status' => 200,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
