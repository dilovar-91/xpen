<?php

namespace App\Filament\Resources\Receipts;

use App\Filament\Resources\Expenses\Pages\ListReceiptDetail;
use App\Filament\Resources\Receipts\Pages\CreateReceipt;
use App\Filament\Resources\Receipts\Pages\EditReceipt;
use App\Filament\Resources\Receipts\Pages\ListReceipts;
use App\Filament\Resources\Receipts\Schemas\ReceiptForm;
use App\Filament\Resources\Receipts\Tables\ReceiptsTable;
use App\Models\Receipt;
use App\Models\Showroom;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReceiptResource extends Resource
{
    protected static ?string $model = Receipt::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Расписка';

    public static function form(Schema $schema): Schema
    {
        return ReceiptForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReceiptsTable::configure($table);
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
            //'index' => ListReceipts::route('/'),
          //  'create' => CreateReceipt::route('/create'),
            // 'edit' => EditReceipt::route('/{record}/edit'),
            'receipt-detail' => ListReceiptDetail::route('/showroom-receipt/{id}/detail'),
        ];
    }




    public static function getReceiptForm(int $type): array
    {
        $showroomParam = request()->route('showroom');
        $showroomId = $showroomParam instanceof Showroom
            ? $showroomParam->id
            : (int) $showroomParam;

        if (! $showroomId && auth()->check()) {
            $showroomId = auth()->user()->showroom_id;
        }

        return [
            Grid::make(2)->schema([
                Select::make('type_id')
                    ->label('Тип погашения')
                    ->options([
                        1 => 'По частям',
                        2 => 'Полная',
                    ])
                    ->required(),

                Select::make('showroom_id')
                    ->label('Салон')
                    ->relationship('showroom', 'name')
                    ->default($showroomId)
                    ->disabled(fn () => auth()->user()->role !== 'admin')
                    ->dehydrated(true)
                    ->required(),

                TextInput::make('car_mark')
                    ->label('Марка')
                    ->required()
                    ->maxLength(255),

                TextInput::make('car_model')
                    ->label('Модель')
                    ->required()
                    ->maxLength(255),

                TextInput::make('vin_number')
                    ->label('VIN номер')
                    ->required()
                    ->maxLength(255),

                TextInput::make('full_name')
                    ->label('ФИО')
                    ->required()
                    ->maxLength(255),

                TextInput::make('phone')
                    ->label('Телефон')
                    ->reactive()
                    ->afterStateUpdated(function ($state, $set) {
                        if (! $state) {
                            return;
                        }

                        $digits = preg_replace('/\D/', '', $state);

                        if (str_starts_with($digits, '8')) {
                            $digits = '7' . substr($digits, 1);
                        }

                        if (! str_starts_with($digits, '7')) {
                            $digits = '7' . $digits;
                        }

                        $digits = substr($digits, 0, 11);

                        if (strlen($digits) < 11) {
                            return;
                        }

                        $set('phone',
                            '+7 (' . substr($digits, 1, 3) . ') ' .
                            substr($digits, 4, 3) . '-' .
                            substr($digits, 7, 2) . '-' .
                            substr($digits, 9, 2)
                        );
                    })
                    ->placeholder('+7 (___) ___-__-__')
                    ->required()
                    ->rule('regex:/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/'),

                TextInput::make('full_price')
                    ->label('Сумма')
                    ->required()
                    ->numeric()
                    ->minValue(0),

                DatePicker::make('repayment_date')
                    ->label('Дата погашения'),
            ]),

            Textarea::make('comment')
                ->label('Комментарий')
                ->columnSpanFull(),
        ];
    }
}
