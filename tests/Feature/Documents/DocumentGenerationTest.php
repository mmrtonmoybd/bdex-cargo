<?php

declare(strict_types=1);

use App\Models\Carrier;
use App\Models\CarrierConsignment;
use App\Models\Order;
use App\Models\OrderBox;
use App\Services\Documents\CustomsDeclarationGenerator;
use App\Services\Documents\InvoiceGenerator;
use App\Services\Documents\LabelGenerator;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;

beforeEach(function (): void {
    $this->seed(SiteSettingSeeder::class);
});

it('issues an invoice with the breakdown the customer was quoted', function (): void {
    $order = Order::factory()->create([
        'cost_breakdown' => [
            'line_items' => [
                ['label' => 'General goods', 'amount' => 170.0, 'detail' => '17.00/kg (NSW) × 10kg'],
                ['label' => 'Airport processing fee', 'amount' => 35.0],
            ],
            'subtotal' => 205.0,
            'tax' => 0.0,
            'total' => 205.0,
            'tax_rate_percent' => 0,
        ],
    ]);

    $invoice = app(InvoiceGenerator::class)->generate($order);

    expect($invoice->invoice_number)->toStartWith('INV-'.now()->format('Y'))
        ->and((float) $invoice->total)->toBe(205.0)
        ->and(Storage::disk('local')->exists($invoice->pdf_path))->toBeTrue();

    // Regenerating keeps the number: an invoice is a record, not a draft.
    $again = app(InvoiceGenerator::class)->generate($order->fresh());

    expect($again->invoice_number)->toBe($invoice->invoice_number);
});

it('gives every box a barcode and the order a BD-EX air waybill', function (): void {
    $order = Order::factory()->create();

    foreach ([1, 2, 3] as $position) {
        OrderBox::factory()->create(['order_id' => $order->id, 'position' => $position, 'barcode' => null]);
    }

    app(LabelGenerator::class)->forOrder($order);

    $fresh = $order->fresh(['boxes']);

    expect($fresh->air_waybill_number)->toStartWith('BDEX-')
        ->and($fresh->boxes)->toHaveCount(3);

    foreach ($fresh->boxes as $box) {
        expect($box->barcode)->toStartWith($order->bdex_tracking_number);
    }
});

it('prints a batch of labels as one file', function (): void {
    $orders = Order::factory()->count(4)->create();
    $orders->each(fn (Order $order) => OrderBox::factory()->create(['order_id' => $order->id, 'barcode' => null]));

    $path = app(LabelGenerator::class)->forOrders($orders->fresh());

    expect(Storage::disk('local')->exists($path))->toBeTrue();

    foreach ($orders as $order) {
        expect($order->documents()->where('type', 'shipping_label')->value('path'))->toBe($path);
    }
});

it('keeps customs declarations away from customers', function (): void {
    $order = Order::factory()->create();
    $order->items()->create(['description' => 'Cotton shirts', 'quantity' => 4, 'weight_kg' => 2.5, 'declared_value' => 80]);

    app(CustomsDeclarationGenerator::class)->generate($order);

    expect($order->documents()->where('type', 'customs_declaration')->sole()->is_customer_visible)->toBeFalse();
});

it('never prints a carrier on a document a customer holds', function (): void {
    $carrier = Carrier::factory()->create(['name' => 'FedEx']);
    $consignment = CarrierConsignment::factory()->for($carrier)->create(['carrier_tracking_number' => '794658123456']);

    $order = Order::factory()->create(['carrier_consignment_id' => $consignment->id]);
    OrderBox::factory()->create(['order_id' => $order->id, 'barcode' => null]);

    $invoice = app(InvoiceGenerator::class)->generate($order);
    $labelPath = app(LabelGenerator::class)->forOrder($order->fresh());

    // A label is the most public document in the chain, and the easiest place to
    // give the arrangement away.
    $parser = new Parser;

    foreach ([$invoice->pdf_path, $labelPath] as $path) {
        $text = $parser->parseContent(Storage::disk('local')->get($path))->getText();

        expect($text)->not->toContain('FedEx')
            ->and($text)->not->toContain('794658123456')
            ->and($text)->toContain('BD-EX');
    }
});
