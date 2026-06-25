<?php

namespace App\Services\General\SendingNotificationMethods;

interface SendingNotificationsServiceInterface{
    public function sendNotification($data, $user_id, $type = null);
}
