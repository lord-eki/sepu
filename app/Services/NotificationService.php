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

            // ✅ FIXED STATUS FLOW
            'status'    => 'sent',

            // ✅ IMPORTANT DEFAULTS
            'is_read'   => false,
            'sent_at'   => now(),

            // ✅ VERY IMPORTANT FOR LOANS / GUARANTORS
            'metadata'  => json_encode($metadata),
        ]);
    }
}