<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenantInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public \App\Models\TenantInvitation $invitation
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('emails.tenant_invitation.subject', ['tenant' => $this->invitation->tenant->name]))
            ->markdown('emails.tenant-invitation', [
                'recipientName' => $this->resolveRecipientName(),
                'acceptUrl' => route('invitations.email-accept', ['token' => $this->invitation->accept_token]),
                'declineUrl' => route('invitations.email-decline', ['token' => $this->invitation->decline_token]),
                'tenantName' => $this->invitation->tenant->name,
            ]);
    }

    /**
     * Resolve the recipient's name from User.
     */
    protected function resolveRecipientName(): string
    {
        $email = $this->invitation->email;

        if ($user = \App\Models\User::where('email', $email)->first()) {
            return (string) $user->name;
        }

        return $email;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
