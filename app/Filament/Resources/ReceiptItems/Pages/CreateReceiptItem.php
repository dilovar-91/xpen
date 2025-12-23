<?php

namespace App\Filament\Resources\ReceiptItems\Pages;

use App\Filament\Resources\ReceiptItems\ReceiptItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateReceiptItem extends CreateRecord
{
    protected static string $resource = ReceiptItemResource::class;
}
