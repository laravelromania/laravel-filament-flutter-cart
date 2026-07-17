<?php

declare(strict_types=1);

namespace Modules\Catalog\Filament\Resources\AttributeResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Catalog\Filament\Resources\AttributeResource;

class ListAttributes extends ListRecords
{
    protected static string $resource = AttributeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
