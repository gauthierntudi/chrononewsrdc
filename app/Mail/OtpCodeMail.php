<?php

namespace App\Mail;

use App\Support\Mail\ChrononewsMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $code,
        public int $expiresMinutes,
        public string $recipientName = '',
    ) {}

    public function build(): self
    {
        $siteName = ChrononewsMail::siteName();

        return $this
            ->subject("Votre code de connexion — {$siteName}")
            ->html(ChrononewsMail::render('emails.otp', [
                'subject' => "Votre code de connexion — {$siteName}",
                'code' => $this->code,
                'name' => $this->recipientName,
                'expiresMinutes' => $this->expiresMinutes,
            ]));
    }
}
