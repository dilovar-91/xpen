<?php

namespace App\Filament\Pages;

use App\Models\Showroom;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Page
{
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Расходы и доходы';
    protected static ?string $title = 'Расходы и доходы';
    protected string $view = 'filament.pages.expense';
    protected static ?string $slug = 'expense';

    public function getShowrooms()
    {
        $user = Auth::user();

        // Если администратор — показываем все салоны
        if ($user->role === 'admin') {
            return Showroom::orderBy('sort', 'asc')->get();
        }

        // Иначе — только свой салон
        return Showroom::where('id', $user->showroom_id)->orderBy('sort', 'asc')->get();
    }
}
