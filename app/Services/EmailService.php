<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;

class EmailService
{
    public function send($email, $subject, $message)
    {
        Mail::raw($message, function ($msg) use ($email, $subject) {
            $msg->to($email)->subject($subject);
        });
        return true;
    }
}
