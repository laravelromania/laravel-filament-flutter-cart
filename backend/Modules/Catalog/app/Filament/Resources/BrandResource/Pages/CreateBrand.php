<?php

declare(strict_types=1);

namespace Modules\Catalog\Filament\Resources\BrandResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Catalog\Filament\Resources\BrandResource;

class CreateBrand extends CreateRecord
{
    protected static string $resource = BrandResource::class;
}
