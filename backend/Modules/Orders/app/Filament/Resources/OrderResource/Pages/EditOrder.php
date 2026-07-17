<?php

declare(strict_types=1);

namespace Modules\Orders\Filament\Resources\OrderResource\Pages;

use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Orders\Filament\Resources\OrderResource;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            OrderResource::changeStatusAction(),
            OrderResource::generateAwbAction(),
            OrderResource::invoiceAction(),
            ViewAction::make(),
        ];
    }
}
