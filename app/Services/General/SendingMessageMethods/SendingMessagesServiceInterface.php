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

interface SendingMessagesServiceInterface
{
    public function sendSms(string $phoneNumber, string $message, string $sender);
    public function reminderSms(string $phoneNumber, int $countryId, $data);
    public function sendResetSms(string $phoneNumber, int $countryId, string $code);
    public function sendingMessage(array $data, string $msg = null);
    public function sendToPhoneWattsapp(string $phone_no, string $message);
}
