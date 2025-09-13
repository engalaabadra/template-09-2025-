<?php

namespace App\Services\General\SendingNotificationMethods;

use App\Models\User;
use App\Models\Notification;
use App\Enums\ServiceResponseEnum;
use App\Exceptions\ApiResponseException;

class SendingNotificationsService implements SendingNotificationsServiceInterface
{
    /**
     * Send a notification to a user via Firebase Cloud Messaging (FCM).
     *
     * @param array $data The notification data (title, body, type, etc.)
     * @param int $user_id The ID of the target user
     * @param string|null $type Optional notification type
     * @return mixed The Firebase response or a ServiceResponse error
     */
    public function sendNotification($data, $user_id, $type = null)
    {
        // Find the user by ID
        $user = User::find($user_id);

        // Return 404 response if user not found or doesn't have a device token
        if (!$user || !$user->fcm_token) {
            throw new ApiResponseException(ServiceResponseEnum::NOT_FOUND);
        }

        // Get the Firebase device token
        $fcmToken = $user->fcm_token;

        // Attach user ID to the data payload
        $data['user_id'] = $user_id;

        // Determine notification type: use passed $type or fallback to $data['type']
        $typeNotification = $type ?? $data['type'];

        // If a type is explicitly provided, create the notification record
        if ($type) {
            $notification = Notification::create($data);
            $notification->type = $typeNotification;
        }

        // Get Firebase server key from config
        $serverKey = config('services.firebase.server_key');

        // Firebase endpoint URL
        $url = 'https://fcm.googleapis.com/fcm/send';

        // Construct the payload for FCM
        $notificationData = [
            "to" => $fcmToken,
            "data" => [
                "title"        => $data['title'],
                "body"         => $data['body'],
                "type"         => $typeNotification,
                "sound"        => "default",
                "click_action" => "Message"
            ],
            "notification" => [
                "title" => $data['title'],
                "body"  => $data['body'],
                "sound" => "default"
            ]
        ];

        // Encode the payload as JSON
        $encodedData = json_encode($notificationData);

        // Initialize a cURL session
        $ch = curl_init($url);

        // Set cURL options
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: key=' . $serverKey,
                'Content-Type: application/json',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false, // Disable SSL verification for development
            CURLOPT_POSTFIELDS     => $encodedData,
        ]);

        // Execute the cURL request
        $result = curl_exec($ch);

        // Handle error in cURL execution
        if ($result === false) {
            throw new ApiResponseException(ServiceResponseEnum::SERVER_ERROR, trans('messages.Error occurred while sending via Firebase'));

        }

        // Close the cURL session
        curl_close($ch);

        // If type wasn't passed, create the notification after sending
        if (!$type) {
            $notification = Notification::create([
                'title'   => $data['title'],
                'body'    => $data['body'],
                'user_id' => $user_id,
                'type'    => $typeNotification,
            ]);
        }

        // Return decoded Firebase response
        return json_decode($result);
    }
    
}
