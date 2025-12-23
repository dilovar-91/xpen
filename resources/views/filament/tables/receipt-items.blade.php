
@if ($record->items->isEmpty())
    <span class="text-xs text-gray-400">Нет оплат</span>
@else
    <div class="space-y-1 text-xs">
        @foreach ($record->items as $item)
            <div class="flex justify-between gap-2 border-b pb-0.5">
                <span>{{ $item->date?->format('d.m.Y') }}</span>
                <span class="font-medium">
                    {{ number_format($item->amount, 2) }} ₽
                </span>
                <span class="text-gray-500 truncate">
                    {{ $item->comment }}
                </span>
            </div>
        @endforeach

        <div class="pt-1 text-right font-semibold">
            Итого: {{ number_format($record->items->sum('amount'), 2) }} ₽
        </div>
    </div>
@endif
