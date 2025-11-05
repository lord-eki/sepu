<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    public function create($userId, $title, $message, $type = 'general', $channel = 'system')
    {
        return Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'channel' => $channel,
            'status' => 'pending',
        ]);
    }
}
