<div class="space-y-3">
    <div class="text-sm text-gray-600 dark:text-gray-300">
        <div><span class="font-medium">Дата:</span> {{ $record->date }}</div>
        <div><span class="font-medium">Тип:</span> {{ $record->type_id === 1 ? 'Приход' : 'Расход' }}</div>
        <div><span class="font-medium">Приход:</span> {{ number_format($record->income, 2, '.', ' ') }}</div>
        <div><span class="font-medium">Расход:</span> {{ number_format($record->expense, 2, '.', ' ') }}</div>
        @if($record->comment)
            <div><span class="font-medium">Комментарий:</span> {{ $record->comment }}</div>
        @endif
    </div>

    <div class="pt-3 flex items-center gap-2">
        {{-- Кнопка "Изменить" внутри модалки: вызывает EditAction для текущей записи --}}
        <x-filament::button
            color="success"
            icon="heroicon-o-pencil-square"
            x-on:click="$dispatch('mount-action', { name: 'edit', arguments: { record: {{ (int) $record->getKey() }} } })"
        >
            Изменить
        </x-filament::button>

        {{-- При желании: кнопка на страницу редактирования --}}
        <x-filament::button
            color="gray"
            icon="heroicon-o-arrow-right"
            tag="a"
            href="{{ route('filament.admin.resources.expenses.edit', ['record' => $record]) }}"
        >
            Открыть страницу
        </x-filament::button>
    </div>
</div>
