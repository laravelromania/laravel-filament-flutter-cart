<?php

declare(strict_types=1);

namespace Modules\Orders\Filament\Resources\OrderResource\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\Orders\Filament\Resources\OrderResource;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            OrderResource::changeStatusAction(),
            OrderResource::invoiceAction(),
            EditAction::make(),
        ];
    }
}
