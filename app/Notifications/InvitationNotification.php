<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvitationNotification extends Notification
{
    use Queueable;
    private $invitation_pin;

    public function __construct($invitation_pin)
    {
        $this->invitation_pin = $invitation_pin;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Invitation')
            ->line('You are receiving this email because you are invited.')
            ->action('Accept Invitation', route('invitation-info', $this->invitation_pin));
    }

    public function toArray($notifiable): array
    {
        return [
            //
        ];
    }
}
