<?php

namespace App\Filament\Resources\Receipts\Pages;

use App\Filament\Resources\Receipts\ReceiptResource;
use App\Models\Receipt;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;

class ListReceipts extends ListRecords
{
    protected static string $resource = ReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }


    protected function makeAction(string $type)
    {
        return match ($type) {
            'addIncome' => Action::make('addIncome')
                ->label('Добавить приход')
                ->icon('heroicon-o-arrow-up-circle')
                ->color('success')
                ->visible(fn () => auth()->user()?->role !== 'guest')
                ->slideOver()
                ->visible(fn () => auth()->user()?->role !== 'guest')
                ->form(fn () => ReceiptResource::getReceiptForm(1))
                ->action(fn (array $data) => Receipt::create($data)),


            // 🔹 Редактирование записи (модалка)
            'editRecord' => EditAction::make('editRecord')
                ->label('Изменить')
                ->icon('heroicon-o-pencil-square')
                ->color('primary')
                ->button()
                ->visible(fn () => auth()->user()?->role !== 'guest')
                ->slideOver()
                ->size('xs')
                ->successNotificationTitle('Изменения сохранены')
                ->modalHeading('Редактирование записи'),
        };
    }
}
