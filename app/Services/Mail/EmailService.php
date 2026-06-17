<?php

namespace App\Services\Mail;

use App\Support\Mail\ChrononewsMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    public function send(string $to, string $subject, string $htmlBody, string $textBody = ''): bool
    {
        if (trim($to) === '') {
            return false;
        }

        try {
            Mail::html($htmlBody, function ($message) use ($to, $subject, $textBody): void {
                $message->to($to)->subject($subject);

                if ($textBody !== '') {
                    $message->text($textBody);
                }
            });

            return true;
        } catch (\Throwable $e) {
            report($e);
            Log::error('Échec envoi email', ['to' => $to, 'subject' => $subject, 'error' => $e->getMessage()]);

            return false;
        }
    }

    public function sendContactEmail(string $nom, string $email, string $sujet, string $message): bool
    {
        $siteName = ChrononewsMail::siteName();
        $to = (string) config('chrononews.brand.contact_email', 'contact@fintechmedias.cd');

        $html = ChrononewsMail::render('emails.contact', [
            'subject' => "Contact — {$siteName}",
            'senderName' => $nom,
            'senderEmail' => $email,
            'subjectLine' => $sujet,
            'messageBody' => $message,
            'sentAt' => now()->timezone(config('chrononews.timezone', 'Africa/Lubumbashi'))->format('d/m/Y H:i'),
        ]);

        $text = "Nouveau message de contact\n\nNom : {$nom}\nEmail : {$email}\nSujet : {$sujet}\n\n{$message}";

        return $this->send($to, "Nouveau message de contact — {$siteName} : {$sujet}", $html, $text);
    }

    public function sendOtp(string $email, string $otp, string $nom = '', int $expirationMinutes = 10): bool
    {
        $siteName = ChrononewsMail::siteName();

        $html = ChrononewsMail::render('emails.otp', [
            'subject' => "Votre code de connexion — {$siteName}",
            'code' => $otp,
            'name' => $nom,
            'expiresMinutes' => $expirationMinutes,
        ]);

        $text = "Bonjour".($nom ? " {$nom}" : '').",\n\nVotre code : {$otp}\nValide {$expirationMinutes} minutes.";

        return $this->send($email, "Votre code de connexion — {$siteName}", $html, $text);
    }

    public function sendPaymentConfirmation(
        string $email,
        string $nom,
        string $articleTitle,
        string $montant,
        string $transactionId,
        string $methode,
        string $dateTime,
    ): bool {
        return $this->sendTransactionEmail(
            email: $email,
            nom: $nom,
            subject: 'Confirmation de paiement — '.ChrononewsMail::siteName(),
            eyebrow: 'Paiement confirmé',
            headline: 'TRANSACTION VALIDÉE',
            intro: 'Nous confirmons la réception de votre paiement. Votre article est en cours de traitement par notre équipe éditoriale.',
            amount: $montant,
            rows: $this->paymentRows($articleTitle, 'Article', $transactionId, $methode, $dateTime),
            highlight: [
                'title' => 'Prochaine étape',
                'body' => 'Votre article est en attente de validation. Vous recevrez un email dès sa publication.',
            ],
            textIntro: "Paiement confirmé pour « {$articleTitle} » — {$montant} USD",
        );
    }

    public function sendPublicitePaymentConfirmation(
        string $email,
        string $nom,
        string $publiciteTitle,
        string $format,
        string $montant,
        string $transactionId,
        string $methode,
        string $dateTime,
        string $dateDebut,
        string $dateFin,
    ): bool {
        $rows = $this->paymentRows($publiciteTitle, 'Publicité', $transactionId, $methode, $dateTime);
        $rows[] = ['label' => 'Format', 'value' => e($format)];
        $rows[] = ['label' => 'Diffusion', 'value' => 'Du '.e($dateDebut).' au '.e($dateFin)];

        return $this->sendTransactionEmail(
            email: $email,
            nom: $nom,
            subject: 'Paiement publicité confirmé — '.ChrononewsMail::siteName(),
            eyebrow: 'Publicité',
            headline: 'CAMPAGNE ENREGISTRÉE',
            intro: 'Votre paiement publicitaire a bien été reçu. Notre équipe va valider votre campagne.',
            amount: $montant,
            rows: $rows,
            highlight: [
                'title' => 'Prochaine étape',
                'body' => 'Votre publicité est en attente de validation. Vous serez notifié dès son activation.',
            ],
            ctaUrl: ChrononewsMail::siteUrl().'/dashboard',
            ctaLabel: 'Voir mes publicités',
            textIntro: "Paiement publicité « {$publiciteTitle} » confirmé",
        );
    }

    public function sendArticlePurchaseConfirmation(
        string $email,
        string $nom,
        string $articleTitle,
        string $montant,
        string $transactionId,
        string $methode,
        string $dateTime,
    ): bool {
        return $this->sendTransactionEmail(
            email: $email,
            nom: $nom,
            subject: 'Votre article est prêt — '.ChrononewsMail::siteName(),
            eyebrow: 'Accès article',
            headline: 'BONNE LECTURE',
            intro: 'Votre paiement a été confirmé. L\'article est maintenant accessible sur notre site.',
            amount: $montant,
            rows: $this->paymentRows($articleTitle, 'Article', $transactionId, $methode, $dateTime),
            textIntro: "Accès article « {$articleTitle} » confirmé",
        );
    }

    public function sendSubscriptionConfirmation(
        string $email,
        string $nom,
        string $planName,
        string $montant,
        string $transactionId,
        string $methode,
        string $dateTime,
        string $endDate,
    ): bool {
        $rows = $this->paymentRows('Abonnement '.$planName, 'Formule', $transactionId, $methode, $dateTime);
        $rows[] = ['label' => 'Actif jusqu\'au', 'value' => e($endDate)];

        return $this->sendTransactionEmail(
            email: $email,
            nom: $nom,
            subject: 'Abonnement activé — '.ChrononewsMail::siteName(),
            eyebrow: 'Abonnement',
            headline: 'BIENVENUE AU CLUB',
            intro: 'Votre abonnement est actif. Profitez d\'un accès illimité à nos contenus premium.',
            amount: $montant,
            rows: $rows,
            textIntro: "Abonnement « {$planName} » actif jusqu'au {$endDate}",
        );
    }

    public function sendArticleValidation(string $email, string $nom, string $articleTitle): bool
    {
        return $this->sendStatusEmail(
            email: $email,
            nom: $nom,
            subject: 'Article publié — '.ChrononewsMail::siteName(),
            eyebrow: 'Publication',
            headline: 'ARTICLE EN LIGNE',
            intro: 'Félicitations ! Votre article a été validé et est désormais visible par nos lecteurs.',
            itemTitle: $articleTitle,
            ctaUrl: ChrononewsMail::siteUrl().'/dashboard',
            ctaLabel: 'Accéder au dashboard',
            ctaColor: ChrononewsMail::color('blue'),
            statusColor: ChrononewsMail::color('blue'),
            textIntro: "Article publié : {$articleTitle}",
        );
    }

    public function sendArticleRejection(string $email, string $nom, string $articleTitle, string $rejectReason): bool
    {
        return $this->sendStatusEmail(
            email: $email,
            nom: $nom,
            subject: 'Article non publié — '.ChrononewsMail::siteName(),
            eyebrow: 'Éditorial',
            headline: 'ARTICLE NON RETENU',
            intro: 'Après examen, votre article n\'a pas été approuvé pour publication.',
            itemTitle: $articleTitle,
            reason: $rejectReason,
            reasonLabel: 'Raison du rejet',
            ctaUrl: ChrononewsMail::siteUrl().'/dashboard',
            ctaLabel: 'Modifier mon article',
            textIntro: "Article refusé : {$articleTitle}",
        );
    }

    public function sendAdValidation(string $email, string $nom, string $adTitle): bool
    {
        return $this->sendStatusEmail(
            email: $email,
            nom: $nom,
            subject: 'Publicité validée — '.ChrononewsMail::siteName(),
            eyebrow: 'Publicité active',
            headline: 'CAMPAGNE LANCÉE',
            intro: 'Votre publicité a été validée et est maintenant diffusée sur notre plateforme.',
            itemTitle: $adTitle,
            ctaUrl: ChrononewsMail::siteUrl().'/dashboard',
            ctaLabel: 'Mon espace publicitaire',
            ctaColor: ChrononewsMail::color('blue'),
            statusColor: ChrononewsMail::color('blue'),
            textIntro: "Publicité active : {$adTitle}",
        );
    }

    public function sendAdRejection(string $email, string $nom, string $adTitle, string $rejectReason): bool
    {
        return $this->sendStatusEmail(
            email: $email,
            nom: $nom,
            subject: 'Publicité refusée — '.ChrononewsMail::siteName(),
            eyebrow: 'Publicité',
            headline: 'CAMPAGNE NON VALIDÉE',
            intro: 'Votre publicité n\'a pas été approuvée. Vous pouvez la modifier et la soumettre à nouveau.',
            itemTitle: $adTitle,
            reason: $rejectReason,
            reasonLabel: 'Raison du refus',
            ctaUrl: ChrononewsMail::siteUrl().'/dashboard',
            ctaLabel: 'Modifier ma publicité',
            textIntro: "Publicité refusée : {$adTitle}",
        );
    }

    /** @param  array<int, array{label: string, value: string}>  $rows */
    protected function sendTransactionEmail(
        string $email,
        string $nom,
        string $subject,
        string $eyebrow,
        string $headline,
        string $intro,
        ?string $amount,
        array $rows,
        ?array $highlight = null,
        ?string $ctaUrl = null,
        ?string $ctaLabel = null,
        string $textIntro = '',
    ): bool {
        $html = ChrononewsMail::render('emails.transaction', [
            'subject' => $subject,
            'eyebrow' => $eyebrow,
            'headline' => $headline,
            'recipientName' => $nom,
            'intro' => e($intro),
            'amount' => $amount,
            'rows' => $rows,
            'highlight' => $highlight,
            'ctaUrl' => $ctaUrl,
            'ctaLabel' => $ctaLabel,
            'footnote' => '<strong>'.e(ChrononewsMail::siteName()).'</strong><br>Si vous n\'êtes pas à l\'origine de cette transaction, contactez-nous immédiatement.',
        ]);

        return $this->send($email, $subject, $html, $textIntro."\n\n".strip_tags($intro));
    }

    protected function sendStatusEmail(
        string $email,
        string $nom,
        string $subject,
        string $eyebrow,
        string $headline,
        string $intro,
        string $itemTitle,
        ?string $reason = null,
        ?string $reasonLabel = null,
        ?string $ctaUrl = null,
        ?string $ctaLabel = null,
        ?string $ctaColor = null,
        ?string $statusColor = null,
        string $textIntro = '',
    ): bool {
        $html = ChrononewsMail::render('emails.status', [
            'subject' => $subject,
            'eyebrow' => $eyebrow,
            'headline' => $headline,
            'recipientName' => $nom,
            'intro' => e($intro),
            'itemTitle' => $itemTitle,
            'reason' => $reason,
            'reasonLabel' => $reasonLabel,
            'ctaUrl' => $ctaUrl,
            'ctaLabel' => $ctaLabel,
            'ctaColor' => $ctaColor,
            'statusColor' => $statusColor,
        ]);

        return $this->send($email, $subject, $html, $textIntro);
    }

    /** @return array<int, array{label: string, value: string}> */
    protected function paymentRows(
        string $item,
        string $itemLabel,
        string $transactionId,
        string $methode,
        string $dateTime,
    ): array {
        $icon = ChrononewsMail::paymentMethodIcon($methode);
        $methodLabel = ChrononewsMail::paymentMethodLabel($methode);
        $methodValue = $icon !== ''
            ? '<img src="'.e($icon).'" alt="" width="22" height="22" style="vertical-align:middle;margin-right:6px;border-radius:4px;">'.e($methodLabel)
            : e($methodLabel);

        return [
            ['label' => $itemLabel, 'value' => e($item)],
            ['label' => 'Transaction', 'value' => e($transactionId)],
            ['label' => 'Méthode', 'value' => $methodValue],
            ['label' => 'Date', 'value' => e($dateTime)],
            ['label' => 'Statut', 'value' => '<span style="color:'.e(ChrononewsMail::color('red')).';font-weight:700;">Confirmé</span>'],
        ];
    }
}
