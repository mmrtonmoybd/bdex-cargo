<?php

declare(strict_types=1);

namespace App\Services\Documents;

use App\Models\Document;
use App\Models\Invoice;
use App\Models\Order;
use App\Support\SiteSettings;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

/**
 * Issues an invoice for an order and renders it as a PDF.
 *
 * The figures come from the breakdown the pricing engine produced at booking, so
 * an invoice is a record of what was quoted rather than a fresh calculation that
 * might disagree with what the customer was told.
 */
final class InvoiceGenerator
{
    public function __construct(private readonly SiteSettings $settings) {}

    public function generate(Order $order): Invoice
    {
        $breakdown = $order->cost_breakdown ?? [];

        $invoice = Invoice::query()->updateOrCreate(
            ['order_id' => $order->id],
            [
                'invoice_number' => $order->invoice?->invoice_number ?? Invoice::nextNumber(),
                'line_items' => $breakdown['line_items'] ?? [],
                'subtotal' => $breakdown['subtotal'] ?? $order->total_cost_aud,
                'tax' => $breakdown['tax'] ?? 0,
                'total' => $breakdown['total'] ?? $order->total_cost_aud,
                'currency' => $order->payment_currency,
                'tax_rate_percent' => $breakdown['tax_rate_percent'] ?? 0,
                'tax_label' => $breakdown['tax_label'] ?? null,
                'issued_at' => now(),
            ],
        );

        $path = $this->render($order, $invoice);

        $invoice->forceFill(['pdf_path' => $path])->save();

        Document::query()->updateOrCreate(
            [
                'documentable_type' => $order->getMorphClass(),
                'documentable_id' => $order->id,
                'type' => Document::INVOICE,
            ],
            [
                'title' => "Invoice {$invoice->invoice_number}",
                'path' => $path,
                'mime_type' => 'application/pdf',
                'size_bytes' => Storage::disk('local')->size($path),
                // Customers may download their own invoice; the signed route checks
                // that it is theirs.
                'is_customer_visible' => true,
            ],
        );

        return $invoice;
    }

    private function render(Order $order, Invoice $invoice): string
    {
        $pdf = Pdf::loadView('documents.invoice', [
            'order' => $order->loadMissing(['boxes', 'items', 'shippingRoute']),
            'invoice' => $invoice,
            'settings' => $this->settings,
        ])->setPaper('a4');

        $path = "documents/invoices/{$invoice->invoice_number}.pdf";

        Storage::disk('local')->put($path, $pdf->output());

        return $path;
    }
}
