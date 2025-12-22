@vite('resources/css/app.css')
<x-filament-panels::page>
    @php
        $showrooms = $this->getShowrooms();
    @endphp

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        @foreach ($showrooms as $showroom)
            <a
                href="{{ route('filament.admin.resources.expenses.showroom', ['showroom' => $showroom->id]) }}"
                class="flex flex-col items-center justify-center gap-2 bg-blue-500 hover:bg-blue-600 transition rounded-xl p-6 shadow-md"
            >
                <x-heroicon-o-building-office class="h-8 w-8 text-yellow-300" />
                <span class="text-lg font-semibold text-white">{{ $showroom->name }}</span>
            </a>


            <a
                href="{{ route('filament.admin.resources.expenses.showroom-receipt', ['showroom' => $showroom->id]) }}"
                class="flex flex-col items-center justify-center gap-2 bg-blue-500 hover:bg-blue-600 transition rounded-xl p-6 shadow-md"
            >
                <x-heroicon-o-building-office class="h-8 w-8 text-yellow-300" />
                <span class="text-lg font-semibold text-white">Расписка {{ $showroom->name }}</span>
            </a>
        @endforeach
    </div>
</x-filament-panels::page>
