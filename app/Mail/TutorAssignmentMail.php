<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TutorAssignmentMail extends Mailable
{
    use Queueable, SerializesModels;

    public $recipient;

    public $otherUser;

    public $role;

    public $isReallocated;

    /**
     * Create a new message instance.
     */
    public function __construct($recipient, $otherUser, $role, $isReallocated =false)
    {
        $this->recipient = $recipient;
        $this->otherUser = $otherUser;
        $this->role = $role;
        $this->isReallocated = $isReallocated;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tutor Assignment Notification',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
         'emails.tutor_assignment',
         with: [
            'recipientName' => $this->recipient->name,
            'otherUserName' => $this->otherUser->name,
            'role' => $this->role,
            'isReallocated' => $this->isReallocated,
        ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
