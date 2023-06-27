<?php

namespace App\Http\Controllers;

use Google_Client;
use Google_Service_Calendar;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Google_Service_Calendar_EventDateTime;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_ConferenceSolutionKey;
use Google_Service_Calendar_ConferenceData;
use Google_Service_Calendar_CreateConferenceRequest;

class MeetingController extends Controller
{
    public function create(Request $request)
    {
        $client = new Google_Client();
        $client->setAuthConfig('client_secrets.json');
        $client->addScope(Google_Service_Calendar::CALENDAR_EVENTS);

        $accessToken = $request->header('Authorization');
        $client->setAccessToken($accessToken);

        if ($client->isAccessTokenExpired()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            $accessToken = $client->getAccessToken();
        }

        $service = new Google_Service_Calendar($client);

        $event = new Google_Service_Calendar_Event();
        $startDateTime = new EventDateTime();
        $endDateTime = new EventDateTime();
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

        return response()->json(['meetLink' => $meetLink], 200);
    }
}
