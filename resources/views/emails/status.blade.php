@extends('emails.layout')

@section('content')
    <p style="margin:0 0 8px;font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:{{ $statusColor ?? $colorRed }};">
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

    @if(!empty($itemTitle))
    <p style="margin:0 0 8px;padding:14px 16px;background:#f7f7f7;border-left:4px solid {{ $colorRed }};border-radius:0 8px 8px 0;font-size:15px;font-weight:700;color:{{ $colorBlack }};">
        « {{ $itemTitle }} »
    </p>
    @endif

    @if(!empty($reason))
    <p style="margin:16px 0 8px;font-size:13px;font-weight:700;color:{{ $colorBlack }};text-transform:uppercase;">{{ $reasonLabel ?? 'Motif' }}</p>
    <div style="padding:14px 16px;background:#fff5f5;border-left:4px solid {{ $colorRed }};border-radius:0 8px 8px 0;font-size:14px;line-height:1.6;color:#8b1a1a;white-space:pre-line;">{{ $reason }}</div>
    @endif

    @if(!empty($ctaUrl) && !empty($ctaLabel))
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:24px auto 0;">
        <tr>
            <td style="border-radius:999px;background-color:{{ $ctaColor ?? $colorRed }};">
                <a href="{{ $ctaUrl }}" target="_blank" style="display:inline-block;padding:12px 24px;font-size:12px;font-weight:700;color:{{ $colorWhite }};text-decoration:none;text-transform:uppercase;">
                    {{ $ctaLabel }}
                </a>
            </td>
        </tr>
    </table>
    @endif

    <p style="margin:24px 0 0;font-size:12px;color:#888;">
        L'équipe {{ $brandName }}
    </p>
@endsection
