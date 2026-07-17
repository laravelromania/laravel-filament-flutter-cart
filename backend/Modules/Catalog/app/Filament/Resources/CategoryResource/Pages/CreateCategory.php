<?php

declare(strict_types=1);

namespace Modules\Catalog\Filament\Resources\CategoryResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Catalog\Filament\Resources\CategoryResource;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;
}
