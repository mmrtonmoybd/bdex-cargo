<?php

declare(strict_types=1);

namespace App\Services\Documents;

use App\Models\Document;
use App\Models\Order;
use App\Support\SiteSettings;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

/**
 * The customs declaration both countries want, filled from the packing list.
 *
 * Internal and border use only — never customer-visible, because it carries
 * declared values and handling notes that belong between us and customs.
 */
final class CustomsDeclarationGenerator
{
    public function __construct(
        private readonly AirWaybillNumberGenerator $waybills,
        private readonly SiteSettings $settings,
    ) {}

    public function generate(Order $order): string
    {
        $order->loadMissing(['items.itemCategory', 'boxes', 'shippingRoute']);

        if (blank($order->air_waybill_number)) {
            $order->forceFill(['air_waybill_number' => $this->waybills->generate()])->save();
        }

        $pdf = Pdf::loadView('documents.customs', [
            'order' => $order,
            'settings' => $this->settings,
        ])->setPaper('a4');

        $path = "documents/customs/{$order->bdex_tracking_number}.pdf";

        Storage::disk('local')->put($path, $pdf->output());

        Document::query()->updateOrCreate(
            [
                'documentable_type' => $order->getMorphClass(),
                'documentable_id' => $order->id,
                'type' => Document::CUSTOMS_DECLARATION,
            ],
            [
                'title' => 'Customs declaration',
                'path' => $path,
                'mime_type' => 'application/pdf',
                'size_bytes' => Storage::disk('local')->size($path),
                'is_customer_visible' => false,
            ],
        );

        return $path;
    }
}
