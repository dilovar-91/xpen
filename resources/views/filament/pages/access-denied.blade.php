<x-filament-panels::page>
    <div class="flex flex-col items-center justify-center h-96 text-center">
        <x-heroicon-o-lock-closed class="w-16 h-16 text-danger-500 mb-4" />
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
            Доступ запрещён
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">
            У вас нет прав для просмотра этого салона.
        </p>

        <x-filament::button
            tag="a"
            color="primary"
            href="{{ route('filament.admin.pages.expense') }}"
            class="mt-6"
        >
            Вернуться на главную
        </x-filament::button>
    </div>
</x-filament-panels::page>
