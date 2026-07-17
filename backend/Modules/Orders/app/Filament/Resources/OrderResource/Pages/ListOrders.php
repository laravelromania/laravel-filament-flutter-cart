<?php

declare(strict_types=1);

namespace Modules\Orders\Filament\Resources\OrderResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\Orders\Filament\Resources\OrderResource;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    // No create action: orders are born from checkout, never hand-created.
    protected function getHeaderActions(): array
    {
        return [];
    }
}
