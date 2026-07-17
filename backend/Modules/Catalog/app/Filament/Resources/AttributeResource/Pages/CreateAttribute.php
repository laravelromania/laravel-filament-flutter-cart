<?php

declare(strict_types=1);

namespace Modules\Catalog\Filament\Resources\AttributeResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Catalog\Filament\Resources\AttributeResource;

class CreateAttribute extends CreateRecord
{
    protected static string $resource = AttributeResource::class;
}
