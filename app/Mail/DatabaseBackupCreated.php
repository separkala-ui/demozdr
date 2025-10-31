<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class DatabaseBackupCreated extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var array<string, mixed>
     */
    protected array $backupData;

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(array $metadata)
    {
        $this->backupData = $metadata;
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
        $createdAt = $this->backupData['created_at'] ?? now();
        if (is_string($createdAt)) {
            $createdAt = Carbon::parse($createdAt);
        }

        return new Content(
            markdown: 'emails.backups.database-created',
            with: [
                'fileName' => $this->backupData['name'] ?? 'backup.sql',
                'size' => $this->formatSize($this->backupData['size'] ?? 0),
                'createdAt' => $createdAt,
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
        if (! isset($this->backupData['path']) || ! file_exists($this->backupData['path'])) {
            return [];
        }

        return [
            Attachment::fromPath($this->backupData['path'])
                ->as($this->backupData['name'] ?? 'backup.sql')
                ->withMime($this->backupData['mime'] ?? 'application/octet-stream'),
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
