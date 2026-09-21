@php($documentTitle = 'Tax invoice')
<x-documents.base :settings="$settings" :document-title="$documentTitle">
    <table class="row">
        <tr>
            <td style="width: 50%;">
                <strong>Billed to</strong><br>
                {{ $order->sender_name }}<br>
                <span class="muted">{!! nl2br(e($order->sender_address)) !!}</span><br>
                <span class="muted">{{ $order->sender_phone }}</span>
            </td>
            <td style="width: 50%;">
                <strong>Invoice</strong> <span class="mono">{{ $invoice->invoice_number }}</span><br>
                <strong>Issued</strong> {{ optional($invoice->issued_at)->format('d M Y') }}<br>
                <strong>Shipment</strong> <span class="mono">{{ $order->bdex_tracking_number }}</span><br>
                @if ($order->air_waybill_number)
                    <strong>Air waybill</strong> <span class="mono">{{ $order->air_waybill_number }}</span><br>
                @endif
                <strong>Route</strong> {{ $order->shippingRoute?->name }}
            </td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align:right">Amount ({{ $invoice->currency }})</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($invoice->line_items ?? [] as $line)
                <tr>
                    <td>
                        {{ $line['label'] ?? 'Charge' }}
                        @if (! empty($line['detail']))
                            <div class="muted">{{ $line['detail'] }}</div>
                        @endif
                    </td>
                    <td style="text-align:right" class="mono">{{ number_format((float) ($line['amount'] ?? 0), 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td class="muted">Freight and handling</td>
                    <td style="text-align:right" class="mono">{{ number_format((float) $invoice->total, 2) }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="row totals" style="margin-top:12px">
        <tr>
            <td style="width:60%"></td>
            <td>
                <table style="width:100%">
                    <tr><td>Subtotal</td><td style="text-align:right" class="mono">{{ number_format((float) $invoice->subtotal, 2) }}</td></tr>
                    @if ((float) $invoice->tax > 0)
                        <tr>
                            <td>{{ $invoice->tax_label ?? 'Tax' }} ({{ rtrim(rtrim(number_format((float) $invoice->tax_rate_percent, 2), '0'), '.') }}%)</td>
                            <td style="text-align:right" class="mono">{{ number_format((float) $invoice->tax, 2) }}</td>
                        </tr>
                    @endif
                    <tr class="grand">
                        <td>Total {{ $invoice->currency }}</td>
                        <td style="text-align:right" class="mono">{{ number_format((float) $invoice->total, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <p class="muted" style="margin-top:16px">
        Payment status: {{ $order->payment_status->getLabel() }}.
        Settle in Australia or Bangladesh, whichever is easier for you.
    </p>
</x-documents.base>
