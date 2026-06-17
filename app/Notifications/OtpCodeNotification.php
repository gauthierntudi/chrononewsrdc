<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
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

    public function toMail(object $notifiable): MailMessage
    {
        $name = $notifiable->name ?: 'Utilisateur';

        return (new MailMessage)
            ->subject('Votre code de connexion — '.config('chrononews.name'))
            ->greeting('Bonjour '.$name.',')
            ->line('Voici votre code de vérification :')
            ->line('**'.$this->code.'**')
            ->line("Ce code expire dans {$this->expiresMinutes} minutes.")
            ->line('Si vous n\'avez pas demandé ce code, ignorez cet email.');
    }
}
