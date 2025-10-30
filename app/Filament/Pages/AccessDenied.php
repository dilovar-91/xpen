<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Pages\Page;

class AccessDenied extends Page
{
    protected string $view = 'filament.pages.access-denied';


    protected static string|null|\BackedEnum $navigationIcon = null;
    protected static bool $shouldRegisterNavigation = false;


    public function getHeaderActions(): array
    {
        return [
            Action::make('home')
                ->label('Главная')
                ->url(route('filament.admin.pages.expense')), // ✅ тут можно
        ];
    }
}
