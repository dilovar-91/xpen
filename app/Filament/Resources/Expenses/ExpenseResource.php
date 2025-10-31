<?php

namespace App\Filament\Resources\Expenses;

use App\Filament\Resources\Expenses\Pages\CreateExpense;
use App\Filament\Resources\Expenses\Pages\EditExpense;
use App\Filament\Resources\Expenses\Pages\ListExpenses;
use App\Filament\Resources\Expenses\Schemas\ExpenseForm;
use App\Filament\Resources\Expenses\Tables\ExpensesTable;
use App\Models\Expense;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $recordTitleAttribute = 'Expense';

    public ?int $showroomId ;



    public function mount(): void
    {
        $this->showroomId = request()->route('showroom') ?? 0;
    }



    public static function form(Schema $schema): Schema
    {
        return ExpenseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExpensesTable::configure($table);
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
            //
            //'create' => CreateExpense::route('/create'),
            'showroom' => Pages\ListExpensesByShowroom::route('/showroom/{showroom}'),
            //'edit' => EditExpense::route('/{record}/edit'),
            'index' => ListExpenses::route('/'),
        ];
    }



    public static function getExpenseForm(int $type): array
    {
        // ✅ Определяем салон прямо тут
        $showroomParam = request()->route('showroom');

        $showroomId = $showroomParam instanceof \App\Models\Showroom
            ? $showroomParam->id
            : (int) $showroomParam;

        // Если и это null, попробуем взять showroom_id пользователя
        if (! $showroomId && auth()->check()) {
            $showroomId = auth()->user()->showroom_id;
        }


        return [
            Hidden::make('type_id')
                ->default($type),

            // ✅ Салон — заполняется автоматически и недоступен для редактирования
            Select::make('showroom_id')
                ->relationship('showroom', 'name')
                ->label('Салон')
                ->default($showroomId)
                ->disabled(fn () => auth()->user()->role !== 'admin')
                ->dehydrated(true)
                ->required(),

            DatePicker::make('date')
                ->label('Дата')
                ->default(now()->toDateString()) // 👈 можно задать сегодняшнюю дату
                ->required(),

            TextInput::make('income')
                ->label('Приход')
                ->numeric()
                ->visible($type === 1),

            TextInput::make('expense')
                ->label('Расход')
                ->numeric()
                ->visible($type === 2),

            TextInput::make('balance')
                ->label('Остаток на конец дня')
                ->numeric(),

            Textarea::make('comment')
                ->label('Комментарий')
                ->columnSpanFull(),
        ];
    }

}
