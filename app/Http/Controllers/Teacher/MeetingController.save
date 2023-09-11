<?php

namespace App\Http\Controllers\Teacher;

use Google\Client as Google_Client;
use Illuminate\Support\Facades\Http;
use Google_Service_Calendar;
use Illuminate\Http\Request;
use Google_Service_Calendar_Event;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use Google_Service_Calendar_EventDateTime;
use Google_Service_Calendar_ConferenceData;
use Google_Service_Calendar_ConferenceSolutionKey;
use Google_Service_Calendar_CreateConferenceRequest;
use App\Models\Teacher;
use App\Models\Meeting;

class MeetingController extends Controller
{
    public function authenticate()
    {
        $client = new Google_Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));
        $client->addScope(Google_Service_Calendar::CALENDAR);
        $client->addScope('https://www.googleapis.com/auth/calendar.events');
        $client->setAccessType('offline'); // Request offline access

        $authUrl = $client->createAuthUrl();

        return response()->json(['auth_url' => $authUrl]);
    }

    public function create(Request $request)
    {
        $accessToken = $request->header('Authorization');
        $teacher = Teacher::where('access_token', $accessToken)->first();

        if (!$teacher) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $client = new Google_Client();
        $client->setAuthConfig('client_secrets.json');
        $client->setAccessToken($accessToken);
        $scopes = [
            Google_Service_Calendar::CALENDAR,
            'https://www.googleapis.com/auth/calendar.events',
            'https://www.googleapis.com/auth/calendar',
            'https://www.googleapis.com/auth/calendar.events.readonly',
            'https://www.googleapis.com/auth/calendar.readonly',
            'https://www.googleapis.com/auth/calendar.settings.readonly'
        ];
        $client->addScope($scopes);
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');

        if ($client->isAccessTokenExpired()) {
            $client->refreshToken($teacher->refresh_token);
            $accessToken = $client->getAccessToken();
        }

        $service = new Google_Service_Calendar($client);

            $event = new Google_Service_Calendar_Event();
            $startDateTime = new Google_Service_Calendar_EventDateTime();
            $endDateTime = new Google_Service_Calendar_EventDateTime();
            $event->setSummary('Meeting');
            $event->setDescription('Google Meeting');
            $startDateTime->setDateTime($request->input('start_time') . ':00');
            $endDateTime->setDateTime($request->input('end_time') . ':00');
            $startDateTime->setTimeZone('Asia/Yangon');
            $endDateTime->setTimeZone('Asia/Yangon');
            $event->setStart($startDateTime);
            $event->setEnd($endDateTime);

            $conferenceRequest = new Google_Service_Calendar_CreateConferenceRequest();
            $conferenceRequest->setRequestId(uniqid());
            $solution_key = new Google_Service_Calendar_ConferenceSolutionKey();
            $solution_key->setType("hangoutsMeet");
            $conferenceRequest->setConferenceSolutionKey($solution_key);

            $conference = new Google_Service_Calendar_ConferenceData();
            $conference->setCreateRequest($conferenceRequest);

            $event->setConferenceData($conference);

            $calendarId = 'primary';

            $event = $service->events->insert(
                $calendarId,
                $event,
                [ 'conferenceDataVersion' => 1 ]
            );

            $meetLink = $event->getHangoutLink();

            $teacher_id = $teacher->id;

            $existingMeeting = Meeting::where('teacher_id', $teacher->id)->first();

            if ($existingMeeting) {
                // Update the existing meeting link
                $existingMeeting->meet_link = $meetLink;
                $existingMeeting->save();

                $courses = $teacher->courses;
                $existingMeeting->courses()->attach($courses);

                return response()->json(['meetLink' => $meetLink, 'message' => "Meet Link Updated Successfully", 'status' => 200]);
            }

            // Store the meeting details in the database
            $meeting = new Meeting();
            $meeting->start_time = $request->input('start_time');
            $meeting->end_time = $request->input('end_time');
            $meeting->teacher_id = $teacher_id;
            $meeting->meet_link = $meetLink;
            $meeting->save();

            $courses = $teacher->courses; // Assuming you have a relationship between Teacher and Course models
            $meeting->courses()->attach($courses);

            return response()->json(['meetLink' => $meetLink, 'message' => "Meet Link created Successfully", 'status' => 200]);
    }

    public function test(Request $request)
    {
        $accessToken = $request->header('Authorization');
        $teacher = Teacher::where('access_token', $accessToken)->first();

        if (!$teacher) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Initialize the Google Client
        $client = new Google_Client();
        $client->setAccessToken($teacher->access_token);

        if ($client->isAccessTokenExpired()) {
            // Handle token refresh if needed
            $client->fetchAccessTokenWithRefreshToken($teacher->refresh_token);
            // Update the access token in the teacher table with $client->getAccessToken()
        }

        $service = new Google_Service_Calendar($client);

        // Create a new event
        $event = new Google_Service_Calendar_Event();
        $startDateTime = new Google_Service_Calendar_EventDateTime();
        $endDateTime = new Google_Service_Calendar_EventDateTime();

        $event->setSummary('Meeting');
        $event->setDescription('Google Meeting');
        $startDateTime->setDateTime($request->input('start_time') . ':00');
        $endDateTime->setDateTime($request->input('end_time') . ':00');
        $startDateTime->setTimeZone('Asia/Yangon');
        $endDateTime->setTimeZone('Asia/Yangon');
        $event->setStart($startDateTime);
        $event->setEnd($endDateTime);

        // Create conference data
        $conferenceRequest = new Google_Service_Calendar_CreateConferenceRequest();
        $conferenceRequest->setRequestId(uniqid());
        $solution_key = new Google_Service_Calendar_ConferenceSolutionKey();
        $solution_key->setType("hangoutsMeet");
        $conferenceRequest->setConferenceSolutionKey($solution_key);

        $conference = new Google_Service_Calendar_ConferenceData();
        $conference->setCreateRequest($conferenceRequest);
        $event->setConferenceData($conference);

        $calendarId = 'primary';

        // Insert the event into Google Calendar
        $event = $service->events->insert(
            $calendarId,
            $event,
            ['conferenceDataVersion' => 1]
        );

        $meetLink = $event->getHangoutLink();

        $teacher_id = $teacher->id;

        $existingMeeting = Meeting::where('teacher_id', $teacher->id)->first();

        if ($existingMeeting) {
            // Update the existing meeting link
            $existingMeeting->meet_link = $meetLink;
            $existingMeeting->save();

            return response()->json(['meetLink' => $meetLink, 'message' => "Meet Link Updated Successfully", 'status' => 200]);
        }

        // Store the meeting details in the database
        $meeting = new Meeting();
        $meeting->start_time = $request->input('start_time');
        $meeting->end_time = $request->input('end_time');
        $meeting->teacher_id = $teacher_id;
        $meeting->meet_link = $meetLink;
        $meeting->save();

        return response()->json(['meetLink' => $meetLink, 'message' => "Meet Link created Successfully", 'status' => 200]);
    }

    public function getMeetingLists()
    {
        $meetings = Meeting::with('teacher')->get();

        $meetingList = [];

        foreach ($meetings as $meeting) {
            $meetingItem = [
                'start_time' => $meeting->start_time,
                'end_time' => $meeting->end_time,
                'meet_link' => $meeting->meet_link,
                'teacher' => [
                    'name' => $meeting->teacher->name,
                ],
            ];

            $meetingList[] = $meetingItem;
        }

        return response()->json([
            'message' => 'List of Meetings with Teachers',
            'data' => $meetingList,
            'status' => 200,
        ]);
    }
}
