<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    public function create(
        $userId,
        $title,
        $message,
        $type = 'general',
        $channel = 'system',
        $metadata = []
    ) {
        return Notification::create([
            'user_id'   => $userId,
            'title'     => $title,
            'message'   => $message,
            'type'      => $type,
            'channel'   => $channel,
            'status'    => 'sent',
            'is_read'   => false,
            'sent_at'   => now(),
            'metadata'  => json_encode($metadata),
        ]);
    }
}