@php($documentTitle = 'Customs declaration')
<x-documents.base :settings="$settings" :document-title="$documentTitle">
    <table class="row">
        <tr>
            <td style="width:50%">
                <strong>Consignor</strong><br>
                {{ $order->sender_name }}<br>
                <span class="muted">{!! nl2br(e($order->sender_address)) !!}</span><br>
                <span class="muted">{{ $order->sender_phone }}</span>
            </td>
            <td style="width:50%">
                <strong>Consignee</strong><br>
                {{ $order->receiver_name }}<br>
                <span class="muted">{!! nl2br(e($order->receiver_address)) !!}</span><br>
                <span class="muted">{{ $order->receiver_phone }}</span>
            </td>
        </tr>
    </table>

    <table class="row" style="margin-top:12px">
        <tr>
            <td><strong>Shipment</strong> <span class="mono">{{ $order->bdex_tracking_number }}</span></td>
            <td><strong>Air waybill</strong> <span class="mono">{{ $order->air_waybill_number }}</span></td>
            <td><strong>Route</strong> {{ $order->shippingRoute?->name }}</td>
            <td><strong>Boxes</strong> {{ $order->boxes->count() }}</td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th>Description of goods</th>
                <th>Category</th>
                <th style="text-align:right">Qty</th>
                <th style="text-align:right">Weight</th>
                <th style="text-align:right">Declared value</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>
                        {{ $item->description }}
                        @if ($item->flagged_prohibited)
                            <div class="chip">REVIEW — matched "{{ $item->flagged_term }}"</div>
                        @endif
                    </td>
                    <td class="muted">{{ $item->itemCategory?->name ?? '—' }}</td>
                    <td style="text-align:right">{{ $item->quantity }} {{ $item->unit }}</td>
                    <td style="text-align:right" class="mono">{{ rtrim(rtrim(number_format((float) $item->weight_kg, 2), '0'), '.') }} kg</td>
                    <td style="text-align:right" class="mono">{{ number_format((float) $item->declared_value, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top:18px">
        I declare that the particulars given above are true and complete, and that the consignment
        contains no prohibited or restricted goods.
    </p>

    <table class="row" style="margin-top:26px">
        <tr>
            <td style="width:50%">_______________________________<br><span class="muted">Consignor signature</span></td>
            <td style="width:50%">_______________________________<br><span class="muted">Date</span></td>
        </tr>
    </table>
</x-documents.base>
