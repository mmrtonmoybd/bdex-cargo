{{-- Shared shell for every generated document. Brand values come from settings,
     so a logo or colour change reaches the paperwork too. --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 18mm 14mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #08160f; }
        h1 { font-size: 20px; margin: 0 0 2px; letter-spacing: -0.4px; }
        .muted { color: #5e6f67; }
        .mono { font-family: DejaVu Sans Mono, monospace; }
        .head { border-bottom: 2px solid {{ $settings->get('color_signal', '#25e39a') }}; padding-bottom: 10px; margin-bottom: 16px; }
        .row { width: 100%; }
        .row td { vertical-align: top; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data th { text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #5e6f67; border-bottom: 1px solid #e2eae5; padding: 6px 4px; }
        table.data td { padding: 6px 4px; border-bottom: 1px solid #eef3f0; }
        .totals td { padding: 4px; }
        .totals .grand { font-size: 15px; font-weight: bold; }
        .chip { display: inline-block; background: #eef3f0; border-radius: 10px; padding: 3px 8px; font-size: 9px; letter-spacing: 1px; text-transform: uppercase; }
        .footer { margin-top: 22px; border-top: 1px solid #e2eae5; padding-top: 8px; font-size: 9px; color: #5e6f67; }
    </style>
</head>
<body>
<div class="head">
    <table class="row">
        <tr>
            <td>
                <h1>{{ $settings->get('business_name', 'BD-EX Cargo') }}</h1>
                <div class="muted">{{ $settings->get('tagline') }}</div>
            </td>
            <td style="text-align: right;">
                <div class="chip">{{ $documentTitle }}</div>
                @if ($settings->get('contact_phone_au'))
                    <div class="muted" style="margin-top:6px">{{ $settings->get('contact_phone_au') }}</div>
                @endif
                @if ($settings->get('contact_email'))
                    <div class="muted">{{ $settings->get('contact_email') }}</div>
                @endif
            </td>
        </tr>
    </table>
</div>

{{ $slot }}

<div class="footer">
    {{ $settings->get('business_name', 'BD-EX Cargo') }} — Australia ⇄ Bangladesh air freight and courier.
    Quote your BD-EX number in any correspondence about this shipment.
</div>
</body>
</html>
