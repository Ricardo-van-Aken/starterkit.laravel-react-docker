<?php

namespace App\Mail;

use App\Models\TenantInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class TenantInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public TenantInvitation $invitation
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('emails.tenant_invitation.subject', ['tenant' => $this->invitation->tenant->name]),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.tenant-invitation',
            with: [
                'recipientName' => $this->resolveRecipientName(),
                'acceptUrl' => route('invitations.email-accept', ['token' => $this->invitation->accept_token]),
                'declineUrl' => route('invitations.email-decline', ['token' => $this->invitation->decline_token]),
            ],
        );
    }

    /**
     * Resolve the recipient's name from User or AccountInvitation.
     */
    protected function resolveRecipientName(): string
    {
        $email = $this->invitation->email;

        if ($user = \App\Models\User::where('email', $email)->first()) {
            return (string) $user->name;
        }

        return $email;
    }
}
