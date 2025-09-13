<?php

namespace App\Services\General\SendingMessageMethods;

use App\Jobs\ExternalApiJob;
use App\Mail\SendingMessage;
use Nexmo\Laravel\Facade\Nexmo;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\General;
use App\Models\Geocode\Country;
use App\Exceptions\ApiResponseException;

class SendingMessagesService implements SendingMessagesServiceInterface
{
    /**
     * Msegat username from config
     *
     * @var string
     */
    protected string $username;

    /**
     * Msegat API key (password) from config
     *
     * @var string
     */
    protected string $password;

    /**
     * Msegat SMS API endpoint
     *
     * @var string
     */
    protected string $api;

    /**
     * Default sender name for SMS
     *
     * @var string
     */
    protected string $defaultSender;

    /**
     * Constructor - Load credentials and set default values
     */
    public function __construct()
    {
        $this->username      = config('services.msegat.username');
        $this->password      = config('services.msegat.password');
        $this->api           = 'https://www.msegat.com/gw/sendsms.php';
        $this->defaultSender = 'Template';
    }

    /**
     * Send SMS using Msegat API
     *
     * @param string $phoneNumber
     * @param string $message
     * @param string $sender
     * @return bool
     */
    protected function sendSms(string $phoneNumber, string $message, string $sender): bool
    {
        // Send a POST request to the Msegat API
        $response = Http::post($this->api, [
            'userName'   => $this->username,
            'apiKey'     => $this->password,
            'numbers'    => $phoneNumber,
            'userSender' => $sender,
            'msg'        => $message,
        ]);

        // Return true if the request was successful
        return $response->ok();
    }

    /**
     * Send a reminder SMS for an appointment
     *
     * @param string $phoneNumber
     * @param int $countryId
     * @param object $data
     * @return bool
     */
    public function reminderSms(string $phoneNumber, int $countryId, $data): bool
    {
        // Get full phone number with country code
        $number = ltrim(Country::whereId($countryId)->value('phone_code'), '+') . $phoneNumber;

        // Format reminder message
        $message = '';

        // Send the SMS using  sender name
        return $this->sendSms($number, $message, '');
    }

    /**
     * Send password reset SMS
     *
     * @param string $phoneNumber
     * @param int $countryId
     * @param string $code
     * @return bool
     */
    public function sendResetSms(string $phoneNumber, int $countryId, string $code): bool
    {
        // Get full phone number with country code
        $number = ltrim(Country::whereId($countryId)->value('phone_code'), '+') . $phoneNumber;

        // Create reset message
        $message = trans('messages.change password') . $code;

        // Send the SMS using Talbinah sender name
        return $this->sendSms($number, $message, '');
    }

    /**
     * Send email or SMS based on provided data
     *
     * @param array $data
     * @param string|null $msg
     * @return mixed
     */
    public function sendingMessage(array $data, string $msg = null)
    {
        // If email is provided, send mail
        if (isset($data['email'])) {
            return Mail::to($data['email'])->send(new General($data));

            // Optional: Use job instead of direct mail
            // dispatch(new SendMessageJob($data));
        }

        // If phone and message are provided, send SMS
        if (isset($data['phone_no']) && $msg) {
            return $this->sendSms($data['phone_no'], $msg, $this->defaultSender);
        }

        // If neither email nor phone are provided, return error
        if (!isset($data['email']) || !isset($data['phone_no'])) {
            throw new ApiResponseException();
        }
    }

    /**
     * Placeholder method for sending WhatsApp messages
     *
     * @param string $phone_no
     * @param string $message
     * @return void
     */
    public function sendToPhoneWattsapp(string $phone_no, string $message)
    {
        // Future implementation for WhatsApp messages
    }
}
