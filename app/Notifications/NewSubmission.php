<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Office alert for any new website submission (proposal, vendor pack, alarm ticket, application, message). */
class NewSubmission extends Notification
{
    /**
     * @param  list<string>  $lines
     */
    public function __construct(
        public readonly string $subject,
        public readonly array $lines,
        public readonly string $url,
        public readonly ?string $replyTo = null,
        public readonly ?string $replyName = null,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)->subject($this->subject);
        foreach ($this->lines as $line) {
            $mail->line($line);
        }
        $mail->action('Open in the dashboard', $this->url);
        if ($this->replyTo) {
            $mail->replyTo($this->replyTo, $this->replyName);
        }

        return $mail;
    }
}
