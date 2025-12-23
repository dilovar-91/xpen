<?php

namespace App\Filament\Resources\Receipts\Schemas;

use App\Models\Receipt;
use App\Models\Showroom;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
                Select::make('type_id')->label('Тип погашение')->options([1 => 'Частичная', 2 => 'Полная',])->required(),




                // Салон — автозаполнение, только для админа редактируем
                Select::make('showroom_id')
                    ->label('Салон')
                    ->relationship('showroom', 'name')
                    ->default($showroomId)
                    ->disabled(fn() => auth()->user()->role !== 'admin')
                    ->dehydrated(true)
                    ->required(),

                // ФИО клиента
                TextInput::make('full_name')
                    ->label('ФИО')
                    ->required()
                    ->maxLength(255),

                // Телефон
                TextInput::make('phone')
                    ->label('Телефон')
                    ->reactive()
                    ->afterStateUpdated(function ($state, $set) {
                        if (! $state) {
                            return;
                        }

                        // Оставляем только цифры
                        $digits = preg_replace('/\D/', '', $state);

                        // Если начинается с 8 → меняем на 7
                        if (str_starts_with($digits, '8')) {
                            $digits = '7' . substr($digits, 1);
                        }

                        // Если начинается с 7 → норм
                        if (! str_starts_with($digits, '7')) {
                            $digits = '7' . $digits;
                        }

                        // Обрезаем до 11 цифр
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



                // Сумма чека
                TextInput::make('full_price')
                    ->label('Сумма')
                    ->required()

                    ->numeric()
                    ->minValue(0),

                // Дата погашения
                DatePicker::make('repayment_date')
                    ->label('Дата погашения'),

                // Комментарий
                Textarea::make('comment')
                    ->label('Комментарий')
                    ->columnSpanFull(),
            ]);
    }
}
