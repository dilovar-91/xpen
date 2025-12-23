<?php

namespace App\Filament\Resources\ReceiptItems;

use App\Filament\Resources\ReceiptItems\Pages\CreateReceiptItem;
use App\Filament\Resources\ReceiptItems\Pages\EditReceiptItem;
use App\Filament\Resources\ReceiptItems\Pages\ListReceiptItems;
use App\Filament\Resources\ReceiptItems\Schemas\ReceiptItemForm;
use App\Filament\Resources\ReceiptItems\Tables\ReceiptItemsTable;
use App\Models\ReceiptItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReceiptItemResource extends Resource
{
    protected static ?string $model = ReceiptItem::class;


    protected static bool $shouldRegisterNavigation = false;


    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Receipt';

    public static function form(Schema $schema): Schema
    {
        return ReceiptItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReceiptItemsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            //'index' => ListReceiptItems::route('/'),
            //'create' => CreateReceiptItem::route('/create'),
           // 'edit' => EditReceiptItem::route('/{record}/edit'),
        ];
    }
}
