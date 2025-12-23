@vite('resources/css/app.css')

<div class="flex flex-wrap items-end justify-between gap-2 mb-4 p-2
    bg-white dark:bg-gray-900
    rounded-lg shadow-sm
    border border-gray-200 dark:border-gray-700
    transition-colors duration-300">

    {{-- Левая часть — фильтр --}}
    <div class="flex flex-wrap items-end gap-2">

        {{-- Дата от --}}
        <div class="flex flex-col">
            <input
                wire:model.live="dateFrom"
                id="dateFrom"
                type="date"
                class="w-full p-2 rounded-md border border-gray-300 dark:border-gray-700
                       bg-white dark:bg-gray-800
                       text-gray-900 dark:text-gray-100
                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
            >
        </div>

        {{-- Дата до --}}
        <div class="flex flex-col">
            <input
                wire:model.live="dateTo"
                id="dateTo"
                type="date"
                class="w-full p-2 rounded-md border border-gray-300 dark:border-gray-700
                       bg-white dark:bg-gray-800
                       text-gray-900 dark:text-gray-100
                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
            >
        </div>

        {{-- Тип операции --}}
        <div class="flex flex-col">
            <select
                wire:model.live="type"
                id="type"
                class="w-full p-2 rounded-md border border-gray-300 dark:border-gray-700
                       bg-white dark:bg-gray-800
                       text-gray-900 dark:text-gray-100
                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
            >
                <option value="">Все типы</option>
                <option value="1">Частичная</option>
                <option value="2">Полная</option>
            </select>
        </div>



        {{-- Кнопки фильтра --}}
        <x-filament::button wire:click="setToday" color="success" icon="heroicon-o-check">
            Сегодня
        </x-filament::button>

        <x-filament::button wire:click="resetWeek" color="primary" icon="heroicon-o-check">
            Неделя
        </x-filament::button>

        <x-filament::button wire:click="clearDates" color="danger" icon="heroicon-o-check">
            Все время
        </x-filament::button>
    </div>

</div>
