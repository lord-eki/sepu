<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GuarantorRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $loan;
    public $guarantor;
    public $member;

    public function __construct($loan, $guarantor = null, $member = null)
    {
        $this->loan = $loan;
        $this->guarantor = $guarantor;
        $this->member = $member;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Guarantor Approval Request')
            ->greeting('Hello ' . $notifiable->name)
            ->line('You have been requested to guarantee a loan application.')
            ->line('Loan Amount: KES ' . number_format($this->loan->applied_amount))
            ->line('Borrower: ' . ($this->member->first_name . ' ' . $this->member->last_name))
            ->action('Review Request', url('/guarantor-requests/' . $this->loan->id))
            ->line('Please approve or reject this request in your dashboard.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'loan_id' => $this->loan->id,
            'loan_number' => $this->loan->loan_number,
            'amount' => $this->loan->applied_amount,
            'message' => 'You have a guarantor request pending approval',
            'borrower' => $this->member->first_name . ' ' . $this->member->last_name,
        ];
    }
}