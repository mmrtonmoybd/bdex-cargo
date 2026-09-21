<?php

namespace App\Filament\Resources\PricingAgreements\Pages;

use App\Filament\Resources\PricingAgreements\PricingAgreementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPricingAgreements extends ListRecords
{
    protected static string $resource = PricingAgreementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
