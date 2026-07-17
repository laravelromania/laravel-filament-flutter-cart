<?php

declare(strict_types=1);

namespace Modules\Catalog\Filament\Resources\AttributeResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Catalog\Filament\Resources\AttributeResource;

class EditAttribute extends EditRecord
{
    protected static string $resource = AttributeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
