@extends('emails.layout')

@section('content')
    <p style="margin:0 0 8px;font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:{{ $colorRed }};">
        Code de vérification
    </p>
    <h1 style="margin:0 0 18px;font-size:22px;line-height:1.3;font-weight:800;color:{{ $colorBlack }};">
        VOTRE CODE OTP
    </h1>
    <p style="margin:0 0 16px;font-size:15px;line-height:1.55;color:#333;">
        Bonjour{{ $name ? ' '.$name : '' }},
    </p>
    <p style="margin:0 0 20px;font-size:15px;line-height:1.55;color:#555;">
        Utilisez le code ci-dessous pour accéder à votre compte {{ $brandName }}.
    </p>
    <div style="text-align:center;margin:24px 0;padding:22px 16px;background-color:#f8f8f8;border:2px dashed {{ $colorRed }};border-radius:10px;">
        <span style="font-size:34px;font-weight:800;letter-spacing:8px;color:{{ $colorBlack }};font-family:'Courier New',monospace;">
            {{ $code }}
        </span>
    </div>
    <p style="margin:0;font-size:14px;line-height:1.55;color:#555;">
        Ce code expire dans <strong>{{ $expiresMinutes }} minutes</strong>.<br>
        Si vous n'avez pas demandé ce code, ignorez cet email.
    </p>
    <p style="margin:24px 0 0;font-size:12px;color:#888;">
        Merci de votre confiance,<br>
        <strong>L'équipe {{ $brandName }}</strong>
    </p>
@endsection
