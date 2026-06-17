<?php

namespace App\Notifications;

use App\Mail\OtpCodeMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OtpCodeNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $code,
        public int $expiresMinutes,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): OtpCodeMail
    {
        $name = trim((string) ($notifiable->nom ?? $notifiable->name ?? ''));

        return (new OtpCodeMail($this->code, $this->expiresMinutes, $name))
            ->to($notifiable->routeNotificationForMail());
    }
}
