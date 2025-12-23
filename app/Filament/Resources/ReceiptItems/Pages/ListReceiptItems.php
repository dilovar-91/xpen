<?php

namespace App\Filament\Resources\ReceiptItems\Pages;

use App\Filament\Resources\ReceiptItems\ReceiptItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReceiptItems extends ListRecords
{
    protected static string $resource = ReceiptItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
