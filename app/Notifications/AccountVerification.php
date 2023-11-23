<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\Smspoh\SmspohMessage;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\Arr;
use NotificationChannels\Smspoh\Exceptions\CouldNotSendNotification;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;

class AccountVerification extends Notification
{
    protected HttpClient $client;
    protected string $endpoint;
    protected string $sender;
    protected $otp;

    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    // public function __construct($token = null, $httpClient = null)
    // {
    //     $this->token = $token;
    //     $this->client = $httpClient ?: new HttpClient(); // Initialize the $client property with a new HttpClient if it's null

    //     $this->endpoint = config('services.smspoh.endpoint', 'https://smspoh.com/api/v2/send');
    // }

    public function via($notifiable)
    {
        return ["smspoh"];
    }

    public function toSmspoh($notifiable)
    {
        return (new SmspohMessage)->content("Your ILBC-Saungpokki verification code is {$this->otp}");
    }
    /**
     * Send text message.
     *
     * <code>
     * $message = [
     *   'sender'   => '',
     *   'to'       => '',
     *   'message'  => '',
     *   'test'     => '',
     * ];
     * </code>
     *
     * @link https://smspoh.com/rest-api-documentation/send?version=2
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     *
     * @throws CouldNotSendNotification
     */
    public function send(array $message)
    {
        try {
            $response = $this->client->request('POST', $this->endpoint, [
                'headers' => [
                    'Authorization' => "Bearer {$this->token}",
                ],
                'json' => [
                    'sender' => Arr::get($message, 'sender'),
                    'to' => Arr::get($message, 'to'),
                    'message' => Arr::get($message, 'message'),
                    'test' => Arr::get($message, 'test', false),
                ],
            ]);

            return json_decode((string) $response->getBody(), true);
        } catch (ClientException $e) {
            throw CouldNotSendNotification::smspohRespondedWithAnError($e);
        } catch (GuzzleException $e) {
            throw CouldNotSendNotification::couldNotCommunicateWithSmspoh($e);
        }
    }
}
