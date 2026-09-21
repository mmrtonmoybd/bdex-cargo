<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * The only status vocabulary the business speaks.
 *
 * Every carrier has its own status codes; adapters translate theirs into these
 * before anything is stored. That translation is the whole point — customers read
 * BD-EX milestones, never a carrier's wording, and the six milestones the public
 * site publishes are generated from these cases rather than maintained separately.
 */
enum NormalizedStatus: string implements HasColor, HasLabel
{
    case Booked = 'booked';
    case PickedUp = 'picked_up';
    case InWarehouse = 'in_warehouse';
    case InTransit = 'in_transit';
    case Customs = 'customs';
    case OutForDelivery = 'out_for_delivery';
    case Delivered = 'delivered';
    case Exception = 'exception';

    public function getLabel(): string
    {
        return (string) __('panel.status.'.$this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Delivered => 'success',
            self::Exception => 'danger',
            self::Customs => 'warning',
            default => 'gray',
        };
    }

    /** What the customer is told is happening, in plain words. */
    public function customerDetail(): string
    {
        return match ($this) {
            self::Booked => 'Confirmation and itemised quote issued.',
            self::PickedUp => 'Collected from the sender.',
            self::InWarehouse => 'Weighed, scanned and consolidated for the flight.',
            self::InTransit => 'On its way to the destination country.',
            self::Customs => 'Declarations filed and duty being cleared.',
            self::OutForDelivery => 'With the delivery driver.',
            self::Delivered => 'Delivered, with proof on record.',
            self::Exception => 'Held up — our team is on it.',
        };
    }

    /** Nothing more will happen to a shipment in one of these states. */
    public function isTerminal(): bool
    {
        return $this === self::Delivered;
    }

    /**
     * The six milestones the public journey shows, in order.
     *
     * Out-for-delivery and exception are real internal states but are folded into
     * the timeline rather than adding steps to it, which is why the marketing page
     * can render from this list and never claim a step that does not exist.
     *
     * @return array<int, self>
     */
    public static function publicJourney(): array
    {
        return [
            self::Booked,
            self::PickedUp,
            self::InWarehouse,
            self::InTransit,
            self::Customs,
            self::Delivered,
        ];
    }

    /** Where a status sits on the public timeline. */
    public function journeyPosition(): int
    {
        return match ($this) {
            self::Booked => 0,
            self::PickedUp => 1,
            self::InWarehouse => 2,
            self::InTransit => 3,
            self::Customs => 4,
            self::OutForDelivery, self::Delivered => 5,
            self::Exception => 3,
        };
    }
}
