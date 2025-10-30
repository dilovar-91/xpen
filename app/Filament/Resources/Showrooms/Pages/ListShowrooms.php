<?php

namespace App\Filament\Resources\Showrooms\Pages;

use App\Filament\Resources\Showrooms\ShowroomResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShowrooms extends ListRecords
{
    protected static string $resource = ShowroomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
