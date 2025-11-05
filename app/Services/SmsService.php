<?php

namespace App\Services;

class SmsService
{
    public function send($phone, $message)
    {
        // integrate SMS provider here
        // e.g Safaricom API / Twilio later
        return true;
    }
}
