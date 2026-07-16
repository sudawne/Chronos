<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Guest;
use App\Models\Meeting;

class RequestPhotoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $guest;
    public $meeting;
    public $secureUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Guest $guest, Meeting $meeting, $secureUrl)
    {
        $this->guest = $guest;
        $this->meeting = $meeting;
        $this->secureUrl = $secureUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📝 Yêu cầu cung cấp ảnh khuôn mặt tham dự: ' . $this->meeting->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.request_photo', 
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