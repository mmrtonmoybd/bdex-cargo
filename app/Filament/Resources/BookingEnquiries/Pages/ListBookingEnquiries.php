<?php

namespace App\Filament\Resources\BookingEnquiries\Pages;

use App\Filament\Resources\BookingEnquiries\BookingEnquiryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBookingEnquiries extends ListRecords
{
    protected static string $resource = BookingEnquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
