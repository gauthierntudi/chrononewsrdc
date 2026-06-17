@extends('emails.layout')

@section('content')
    <p style="margin:0 0 8px;font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:{{ $colorRed }};">
        {{ $eyebrow }}
    </p>
    <h1 style="margin:0 0 18px;font-size:22px;line-height:1.3;font-weight:800;color:{{ $colorBlack }};">
        {{ $headline }}
    </h1>
    <p style="margin:0 0 16px;font-size:15px;line-height:1.55;color:#333;">
        <strong>Bonjour {{ $recipientName }},</strong>
    </p>
    <p style="margin:0 0 20px;font-size:15px;line-height:1.55;color:#555;">
        {!! $intro !!}
    </p>

    @if(!empty($amount))
    <div style="text-align:center;margin:0 0 22px;padding:20px;background:{{ $colorBlack }};border-radius:10px;">
        <p style="margin:0 0 4px;font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(255,255,255,0.7);">Montant payé</p>
        <p style="margin:0;font-size:30px;font-weight:800;color:{{ $colorWhite }};">{{ $amount }} USD</p>
    </div>
    @endif

    @if(!empty($rows))
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:0 0 20px;border:1px solid #ececec;border-radius:10px;overflow:hidden;">
        @foreach($rows as $row)
        <tr>
            <td style="padding:12px 16px;border-top:{{ $loop->first ? '0' : '1px solid #eee' }};font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#888;width:38%;vertical-align:top;">
                {{ $row['label'] }}
            </td>
            <td style="padding:12px 16px;border-top:{{ $loop->first ? '0' : '1px solid #eee' }};font-size:14px;font-weight:600;color:{{ $colorBlack }};vertical-align:top;">
                {!! $row['value'] !!}
            </td>
        </tr>
        @endforeach
    </table>
    @endif

    @if(!empty($highlight))
    <div style="margin:0 0 20px;padding:16px 18px;background-color:{{ $colorRed }};border-radius:8px;">
        <p style="margin:0 0 6px;font-size:12px;font-weight:700;color:{{ $colorWhite }};text-transform:uppercase;">{{ $highlight['title'] }}</p>
        <p style="margin:0;font-size:14px;line-height:1.5;color:{{ $colorWhite }};">{!! $highlight['body'] !!}</p>
    </div>
    @endif

    @if(!empty($ctaUrl) && !empty($ctaLabel))
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:8px auto 0;">
        <tr>
            <td style="border-radius:999px;background-color:{{ $colorBlue }};">
                <a href="{{ $ctaUrl }}" target="_blank" style="display:inline-block;padding:12px 24px;font-size:12px;font-weight:700;color:{{ $colorWhite }};text-decoration:none;text-transform:uppercase;">
                    {{ $ctaLabel }}
                </a>
            </td>
        </tr>
    </table>
    @endif

    @if(!empty($footnote))
    <p style="margin:24px 0 0;font-size:12px;line-height:1.5;color:#888;">{!! $footnote !!}</p>
    @endif
@endsection
