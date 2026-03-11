<?php

namespace App\Filament\Resources\Expenses\Pages;

use App\Filament\Resources\Expenses\ExpenseResource;
use App\Models\Expense;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Form;
use Illuminate\Support\Facades\Request;

class ListExpenses extends ListRecords
{
    protected static string $resource = ExpenseResource::class;

    public ?string $dateFrom = null;
    public ?string $dateTo = null;

    public function mount(): void
    {
        $today = Carbon::today()->toDateString(); // формат YYYY-MM-DD
        $this->dateFrom = $today;
        $this->dateTo = $today;
    }

    public function resetDates(): void
    {
        $today = now()->toDateString();
        $this->dateFrom = $today;
        $this->dateTo = $today;
    }

    public function resetTwoDates(): void
    {
        $today = now()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();
        $this->dateFrom = $yesterday;
        $this->dateTo = $today;
    }

    public function clearDates(): void
    {
        $this->dateFrom = null;
        $this->dateTo = null;
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->makeAction('addIncome'),
            $this->makeAction('addExpense'),
        ];
    }

    public function getHeading(): string | \Illuminate\Contracts\Support\Htmlable | null
    {
        return null; // убираем заголовок страницы
    }



    protected function makeAction(string $type)
    {
        return match ($type) {
            'addIncome' => Action::make('addIncome')
                ->label('Добавить приход')
                ->icon('heroicon-o-arrow-up-circle')
                ->color('success')
                ->slideOver()
                ->visible(fn () => auth()->user()?->role !== 'guest')
                ->form(fn () => ExpenseResource::getExpenseForm(1))
                ->action(fn (array $data) => Expense::create($data)),
            'addExpense' => Action::make('addExpense')
                ->label('Добавить расход')
                ->icon('heroicon-o-arrow-down-circle')
                ->color('danger')
                ->visible(fn () => auth()->user()?->role !== 'guest')
                ->slideOver()
                ->schema(fn () => ExpenseResource::getExpenseForm(
                    1,
                    Request::route('showroom_id') ?? Request::query('showroom_id')
                ))
                ->action(fn (array $data) => Expense::create($data)),


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
