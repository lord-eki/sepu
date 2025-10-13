<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\User;
use App\Services\SmsService;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $notification;
    protected $user;

    public function __construct(Notification $notification, User $user)
    {
        $this->notification = $notification;
        $this->user = $user;
    }

    public function handle(SmsService $smsService, EmailService $emailService)
    {
        try {
            $this->notification->update(['status' => 'pending']);

            switch ($this->notification->channel) {
                case 'sms':
                    $result = $smsService->sendSms(
                        $this->user->phone,
                        $this->notification->message
                    );
                    break;
                
                case 'email':
                    $result = $emailService->sendEmail(
                        $this->user->email,
                        $this->notification->title,
                        $this->notification->message
                    );
                    break;
                
                default:
                    $result = true; // System notification
                    break;
            }

            $this->notification->update([
                'status' => $result ? 'sent' : 'failed',
                'sent_at' => now()
            ]);

        } catch (\Exception $e) {
            Log::error('Notification failed: ' . $e->getMessage());
            $this->notification->update(['status' => 'failed']);
            throw $e;
        }
    }

    public function failed(\Exception $exception)
    {
        $this->notification->update(['status' => 'failed']);
        Log::error('SendNotificationJob failed: ' . $exception->getMessage());
    }
}
