<?php

declare(strict_types=1);

namespace App\Filament\Portal\Resources\Shipments\Pages;

use App\Filament\Portal\Resources\Shipments\ShipmentResource;
use App\Services\Tracking\CustomerFacingOrder;
use Filament\Resources\Pages\Page;

/**
 * One shipment's timeline, rendered from the same payload the public tracking
 * page uses — so a customer sees the identical story whether they are signed in
 * or not, and neither surface can drift into naming a carrier.
 */
class ViewShipment extends Page
{
    protected static string $resource = ShipmentResource::class;

    protected string $view = 'filament.portal.shipment';

    public int|string $record;

    /** @var array<string, mixed> */
    public array $tracking = [];

    public function mount(int|string $record): void
    {
        $this->record = $record;

        $order = ShipmentResource::getEloquentQuery()->findOrFail($record);

        $facing = app(CustomerFacingOrder::class);

        $this->tracking = $facing->payload($facing->find($order->bdex_tracking_number));
    }

    public function getTitle(): string
    {
        return (string) ($this->tracking['tracking_number'] ?? 'Shipment');
    }
}
