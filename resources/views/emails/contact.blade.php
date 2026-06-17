@extends('emails.layout')

@section('content')
    <p style="margin:0 0 8px;font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:{{ $colorRed }};">
        Nouveau message
    </p>
    <h1 style="margin:0 0 18px;font-size:22px;line-height:1.3;font-weight:800;color:{{ $colorBlack }};">
        CONTACT SITE WEB
    </h1>
    <p style="margin:0 0 16px;font-size:15px;line-height:1.55;color:#333;">
        <strong>Bonjour Admin,</strong>
    </p>
    <p style="margin:0 0 20px;font-size:15px;line-height:1.55;color:#555;">
        Un visiteur a envoyé un message via le formulaire de contact de <strong>{{ $brandName }}</strong>.
    </p>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:0 0 20px;background:#f7f7f7;border-left:4px solid {{ $colorRed }};border-radius:0 8px 8px 0;">
        <tr>
            <td style="padding:16px 18px;font-size:14px;line-height:1.6;color:#333;">
                <p style="margin:0 0 8px;"><strong>Nom :</strong> {{ $senderName }}</p>
                <p style="margin:0 0 8px;"><strong>Email :</strong> {{ $senderEmail }}</p>
                <p style="margin:0 0 8px;"><strong>Sujet :</strong> {{ $subjectLine }}</p>
                <p style="margin:0;"><strong>Date :</strong> {{ $sentAt }}</p>
            </td>
        </tr>
    </table>
    <p style="margin:0 0 8px;font-size:13px;font-weight:700;color:{{ $colorBlack }};text-transform:uppercase;">Message</p>
    <div style="padding:16px;background:#fff;border:1px solid #e8e8e8;border-radius:8px;font-size:14px;line-height:1.6;color:#444;white-space:pre-line;">{{ $messageBody }}</div>
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:24px auto 0;">
        <tr>
            <td style="border-radius:999px;background-color:{{ $colorRed }};">
                <a href="mailto:{{ $senderEmail }}" style="display:inline-block;padding:12px 24px;font-size:12px;font-weight:700;color:{{ $colorWhite }};text-decoration:none;text-transform:uppercase;">
                    Répondre à {{ $senderName }}
                </a>
            </td>
        </tr>
    </table>
@endsection
