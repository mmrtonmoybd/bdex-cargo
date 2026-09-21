<?php

declare(strict_types=1);

namespace App\Filament\Portal\Resources\Listings\Pages;

use App\Filament\Portal\Resources\Listings\ListingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateListing extends CreateRecord
{
    protected static string $resource = ListingResource::class;
}
