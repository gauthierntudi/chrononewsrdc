<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $subject ?? $brandName }}</title>
    <!--[if mso]>
    <style type="text/css">
        body, table, td { font-family: Arial, sans-serif !important; }
    </style>
    <![endif]-->
</head>
<body style="margin:0;padding:0;background-color:#ececec;font-family:'Montserrat','Poppins',Arial,sans-serif;color:#1a1a1a;-webkit-font-smoothing:antialiased;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#ececec;padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width:600px;width:100%;background-color:{{ $colorWhite }};border-radius:12px;overflow:hidden;box-shadow:0 8px 24px rgba(17,17,17,0.08);">
                {{-- Bandeau rouge charte --}}
                <tr>
                    <td style="height:4px;background-color:{{ $colorRed }};font-size:0;line-height:0;">&nbsp;</td>
                </tr>
                {{-- En-tête --}}
                <tr>
                    <td style="background-color:{{ $colorBlack }};padding:28px 24px;text-align:center;">
                        <a href="{{ $brandUrl }}" target="_blank" style="text-decoration:none;">
                            <img src="{{ $brandLogo }}" alt="{{ $brandName }}" width="200" style="display:block;margin:0 auto;border:0;max-width:200px;height:auto;">
                        </a>
                        <p style="margin:14px 0 0;color:{{ $colorWhite }};font-size:11px;letter-spacing:0.14em;text-transform:uppercase;opacity:0.85;">
                            {{ $brandTagline }}
                        </p>
                    </td>
                </tr>
                {{-- Contenu --}}
                <tr>
                    <td style="padding:36px 32px 28px;background-color:{{ $colorWhite }};">
                        @yield('content')
                    </td>
                </tr>
                {{-- Pied de page --}}
                <tr>
                    <td style="background-color:{{ $colorBlackSoft }};padding:24px 20px;text-align:center;">
                        @if(!empty($socialLinks))
                        <p style="margin:0 0 14px;font-size:12px;">
                            @foreach($socialLinks as $network => $url)
                                <a href="{{ $url }}" target="_blank" style="color:{{ $colorWhite }};text-decoration:none;margin:0 8px;font-weight:600;text-transform:capitalize;">{{ $network }}</a>
                                @if(!$loop->last)<span style="color:rgba(255,255,255,0.35);">·</span>@endif
                            @endforeach
                        </p>
                        @endif
                        <p style="margin:0 0 12px;color:rgba(255,255,255,0.72);font-size:11px;line-height:1.5;">
                            Nouvelle Galerie Présidentielle, Premier niveau, local 1M3-B<br>
                            Kinshasa — RD Congo
                        </p>
                        <p style="margin:0 0 12px;font-size:11px;line-height:1.6;">
                            @foreach($footerLinks as $label => $url)
                                <a href="{{ $url }}" target="_blank" style="color:{{ $colorWhite }};text-decoration:none;margin:0 6px;">{{ $label }}</a>
                                @if(!$loop->last)<span style="color:rgba(255,255,255,0.35);">|</span>@endif
                            @endforeach
                        </p>
                        <p style="margin:0;color:rgba(255,255,255,0.55);font-size:10px;">
                            © {{ $year }} {{ $brandName }}. Tous droits réservés.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
