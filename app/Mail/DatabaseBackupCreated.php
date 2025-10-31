<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DatabaseBackupCreated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $metadata)
    {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('بک‌آپ پایگاه داده آماده شد'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.backups.database-created',
            with: [
                'fileName' => $this->metadata['name'] ?? 'backup.sql',
                'size' => $this->formatSize($this->metadata['size'] ?? 0),
                'createdAt' => ($this->metadata['created_at'] ?? now())->format('Y-m-d H:i'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        if (! isset($this->metadata['path']) || ! file_exists($this->metadata['path'])) {
            return [];
        }

        return [
            Attachment::fromPath($this->metadata['path'])
                ->as($this->metadata['name'] ?? 'backup.sql')
                ->withMime($this->metadata['mime'] ?? 'application/octet-stream'),
        ];
    }

    protected function formatSize(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        }

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' B';
    }
}
