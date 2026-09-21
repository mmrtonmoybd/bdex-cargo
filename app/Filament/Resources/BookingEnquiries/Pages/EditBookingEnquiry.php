<?php

namespace App\Filament\Resources\BookingEnquiries\Pages;

use App\Filament\Resources\BookingEnquiries\BookingEnquiryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBookingEnquiry extends EditRecord
{
    protected static string $resource = BookingEnquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
