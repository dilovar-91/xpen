<?php

namespace App\Filament\Resources\ReceiptItems\Pages;

use App\Filament\Resources\ReceiptItems\ReceiptItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReceiptItem extends EditRecord
{
    protected static string $resource = ReceiptItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
