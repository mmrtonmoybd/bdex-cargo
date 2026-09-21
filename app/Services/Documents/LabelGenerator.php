<?php

declare(strict_types=1);

namespace App\Services\Documents;

use App\Models\Document;
use App\Models\Order;
use App\Models\OrderBox;
use App\Support\SiteSettings;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * Barcoded shipping labels — one per box, or fifty in one file.
 *
 * Every label carries the BD-EX tracking number and BD-EX branding only. A label
 * is the most public document in the chain; it is also the easiest place to leak
 * a carrier's identity, so nothing about the carrier reaches this class.
 */
final class LabelGenerator
{
    public function __construct(
        private readonly BarcodeRenderer $barcodes,
        private readonly AirWaybillNumberGenerator $waybills,
        private readonly SiteSettings $settings,
    ) {}

    /** One PDF containing a label for each box on the order. */
    public function forOrder(Order $order): string
    {
        return $this->render(collect([$this->prepare($order)]), "documents/labels/{$order->bdex_tracking_number}.pdf");
    }

    /**
     * One PDF containing every label for every order given — the batch print.
     *
     * @param  Collection<int, Order>  $orders
     */
    public function forOrders(Collection $orders): string
    {
        $prepared = $orders->map(fn (Order $order): array => $this->prepare($order));

        return $this->render($prepared, 'documents/labels/batch-'.now()->format('Ymd-His').'.pdf');
    }

    /**
     * Ensures the order has everything a label needs, then describes it.
     *
     * @return array{order: Order, boxes: array<int, array<string, mixed>>}
     */
    private function prepare(Order $order): array
    {
        $order->loadMissing('boxes', 'shippingRoute');

        if (blank($order->air_waybill_number)) {
            $order->forceFill(['air_waybill_number' => $this->waybills->generate()])->save();
        }

        // An order booked online may not have had its boxes entered yet; a label
        // still has to exist for the box the customer is about to hand over.
        if ($order->boxes->isEmpty()) {
            $order->boxes()->create(['position' => 1, 'weight_kg' => 0]);
            $order->load('boxes');
        }

        $boxes = $order->boxes->map(function (OrderBox $box) use ($order): array {
            if (blank($box->barcode)) {
                $box->forceFill([
                    'barcode' => $order->bdex_tracking_number.'-'.str_pad((string) $box->position, 2, '0', STR_PAD_LEFT),
                ])->save();
            }

            return [
                'box' => $box,
                'barcode' => $this->barcodes->dataUri((string) $box->barcode),
            ];
        })->all();

        return ['order' => $order, 'boxes' => $boxes];
    }

    /** @param Collection<int, array{order: Order, boxes: array<int, array<string, mixed>>}> $labels */
    private function render(Collection $labels, string $path): string
    {
        $pdf = Pdf::loadView('documents.labels', [
            'labels' => $labels,
            'settings' => $this->settings,
        ])->setPaper('a4');

        Storage::disk('local')->put($path, $pdf->output());

        foreach ($labels as $label) {
            /** @var Order $order */
            $order = $label['order'];

            Document::query()->create([
                'documentable_type' => $order->getMorphClass(),
                'documentable_id' => $order->id,
                'type' => Document::SHIPPING_LABEL,
                'title' => 'Shipping label',
                'path' => $path,
                'mime_type' => 'application/pdf',
                'is_customer_visible' => false,
            ]);
        }

        return $path;
    }
}
