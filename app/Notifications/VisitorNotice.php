<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Plain email to a visitor: a confirmation with their reference, or the approved vendor-pack link. */
class VisitorNotice extends Notification
{
    /**
     * @param  list<string>  $lines
     */
    public function __construct(
        public readonly string $subject,
        public readonly string $greeting,
        public readonly array $lines,
        public readonly ?string $actionText = null,
        public readonly ?string $actionUrl = null,
        public readonly ?string $footer = null,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)->subject($this->subject)->greeting($this->greeting);
        foreach ($this->lines as $line) {
            $mail->line($line);
        }
        if ($this->actionText && $this->actionUrl) {
            $mail->action($this->actionText, $this->actionUrl);
        }
        if ($this->footer) {
            $mail->line($this->footer);
        }

        return $mail->salutation('Sheriff Security Services');
    }
}
