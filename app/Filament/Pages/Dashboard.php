<?php

namespace App\Filament\Pages;

use App\Models\Showroom;
use Filament\Pages\Page;

class Dashboard extends Page
{
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Расходы и доходы';
    protected static ?string $title = 'Расходы и доходы';
    protected string $view = 'filament.pages.expense';
    protected static ?string $slug = 'expense';

    /** Получаем список салонов */
    public function getShowrooms()
    {
        return Showroom::query()->select('id', 'name')->get();
    }
}
