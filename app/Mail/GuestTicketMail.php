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

class GuestTicketMail extends Mailable
{
    use Queueable, SerializesModels;

    public $guest;
    public $meeting;
    public $qrData;

    /**
     * Create a new message instance.
     */
    public function __construct(Guest $guest, Meeting $meeting)
    {
        $this->guest = $guest;
        $this->meeting = $meeting;
        
        // Tạo chuỗi dữ liệu mã hóa cho QR Code
        $this->qrData = json_encode([
            'm' => $meeting->id,
            'g' => $guest->id
        ]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Vé mời tham dự sự kiện: ' . $this->meeting->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            // ĐÂY LÀ ĐIỂM QUAN TRỌNG NHẤT ĐÃ ĐƯỢC SỬA LẠI
            view: 'emails.guest_ticket',
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