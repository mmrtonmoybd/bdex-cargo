@php($documentTitle = 'Shipping label')
<x-documents.base :settings="$settings" :document-title="$documentTitle">
    @foreach ($labels as $label)
        @php($order = $label['order'])
        @foreach ($label['boxes'] as $index => $entry)
            <div style="border:2px solid #08160f; border-radius:6px; padding:14px; margin-bottom:12px;">
                <table class="row">
                    <tr>
                        <td style="width:62%">
                            <div class="chip">{{ $order->shippingRoute?->code }}</div>
                            <div style="font-size:17px; font-weight:bold; margin-top:6px">{{ $settings->get('business_name', 'BD-EX Cargo') }}</div>
                        </td>
                        <td style="width:38%; text-align:right">
                            <div class="muted" style="font-size:9px; letter-spacing:1px">BOX</div>
                            <div style="font-size:22px; font-weight:bold">
                                {{ $entry['box']->position }} / {{ count($label['boxes']) }}
                            </div>
                        </td>
                    </tr>
                </table>

                <table class="row" style="margin-top:10px">
                    <tr>
                        <td style="width:50%">
                            <div class="muted" style="font-size:9px; letter-spacing:1px">FROM</div>
                            <strong>{{ $order->sender_name }}</strong><br>
                            <span class="muted">{!! nl2br(e($order->sender_address)) !!}</span><br>
                            <span class="muted">{{ $order->sender_phone }}</span>
                        </td>
                        <td style="width:50%">
                            <div class="muted" style="font-size:9px; letter-spacing:1px">TO</div>
                            <strong style="font-size:13px">{{ $order->receiver_name }}</strong><br>
                            {!! nl2br(e($order->receiver_address)) !!}<br>
                            {{ $order->receiver_phone }}
                        </td>
                    </tr>
                </table>

                <table class="row" style="margin-top:10px">
                    <tr>
                        <td style="width:65%">
                            <img src="{{ $entry['barcode'] }}" style="width:100%; height:52px">
                            <div class="mono" style="letter-spacing:2px; font-size:12px">{{ $entry['box']->barcode }}</div>
                        </td>
                        <td style="width:35%; text-align:right">
                            <div class="muted" style="font-size:9px; letter-spacing:1px">WEIGHT</div>
                            <div style="font-size:15px; font-weight:bold">{{ rtrim(rtrim(number_format((float) $entry['box']->weight_kg, 2), '0'), '.') }} kg</div>
                            @if ($order->air_waybill_number)
                                <div class="muted" style="font-size:9px; letter-spacing:1px; margin-top:6px">AWB</div>
                                <div class="mono">{{ $order->air_waybill_number }}</div>
                            @endif
                            @if ($order->is_fragile)
                                <div class="chip" style="margin-top:6px">FRAGILE</div>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        @endforeach
    @endforeach
</x-documents.base>
