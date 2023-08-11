<?php

namespace App\Http\Controllers\Teacher;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use Google_Service_Calendar;
use Google_Service_Calendar_EventDateTime;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_ConferenceSolutionKey;
use Google_Service_Calendar_ConferenceData;
use Google_Service_Calendar_CreateConferenceRequest;
use Illuminate\Support\Facades\DB;

class MeetingController extends Controller
{
    public function create(Request $request, $course_id)
    {
        $user = Socialite::driver('google')->userFromToken($request->header('Authorization'));

        $client = new Google_Client();
        $client->setAccessToken($user->token);
        $client->addScope(Google_Service_Calendar::CALENDAR_EVENTS);
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');

        if ($client->isAccessTokenExpired()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
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
            ['conferenceDataVersion' => 1]
        );

        $meetLink = $event->getHangoutLink();

        $assignCourse = DB::table('teacher_courses')->where('course_id', $course_id)->first();

        // Store the meeting details in the database
        $meeting = new Meeting();
        $meeting->start_time = $request->input('start_time'); 
        $meeting->end_time = $request->input('end_time'); 
        $meeting->course_id = $assignCourse->course_id; 
        $meeting->meet_link = $meetLink;
        $meeting->save();

        return response()->json(['meetLink' => $meetLink], 200);
    }
}
