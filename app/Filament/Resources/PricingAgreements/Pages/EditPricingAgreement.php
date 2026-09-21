<?php

namespace App\Filament\Resources\PricingAgreements\Pages;

use App\Filament\Resources\PricingAgreements\PricingAgreementResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPricingAgreement extends EditRecord
{
    protected static string $resource = PricingAgreementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
