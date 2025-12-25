<?php

namespace App\Filament\Resources\Receipts\Schemas;

use App\Models\Receipt;
use App\Models\Showroom;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class ReceiptForm
{
    public static function configure(Schema $schema): Schema
    {

        // Определяем showroom: сначала из route, затем из пользователя
        $showroomParam = request()->route('showroom');
        $showroomId = $showroomParam instanceof Showroom ? $showroomParam->id : (int)$showroomParam;
        if (!$showroomId && auth()->check()) {
            $showroomId = auth()->user()->showroom_id;
        }
        return $schema
            ->components([
                Grid::make(2)->columnSpanFull()->schema([
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

                            $formatted = '+7 (' .
                                substr($digits, 1, 3) . ') ' .
                                substr($digits, 4, 3) . '-' .
                                substr($digits, 7, 2) . '-' .
                                substr($digits, 9, 2);

                            $set('phone', $formatted);
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

                    FileUpload::make('file')
                        ->label('Файл расписки')
                        ->disk('public')
                        ->directory('receipts')
                        ->preserveFilenames()
                        ->downloadable()
                        ->openable()
                        ->maxSize(10240) // 10 MB
                        ->acceptedFileTypes([
                            'application/pdf',
                            'image/*',
                        ]),
                ]),

                Textarea::make('comment')
                    ->label('Комментарий')
                    ->columnSpanFull(),
            ]);
    }
}
