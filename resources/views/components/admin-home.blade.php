<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
        Добро пожаловать в панель управления 💼
    </h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="p-4 bg-white dark:bg-gray-900 shadow rounded-xl">
            <p class="text-gray-600 dark:text-gray-400 text-sm">Всего расходов</p>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mt-1">
                {{ number_format(\App\Models\Expense::sum('expense'), 2, '.', ' ') }} ₽
            </h2>
        </div>

        <div class="p-4 bg-white dark:bg-gray-900 shadow rounded-xl">
            <p class="text-gray-600 dark:text-gray-400 text-sm">Всего приходов</p>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mt-1">
                {{ number_format(\App\Models\Expense::sum('income'), 2, '.', ' ') }} ₽
            </h2>
        </div>

        <div class="p-4 bg-white dark:bg-gray-900 shadow rounded-xl">
            <p class="text-gray-600 dark:text-gray-400 text-sm">Баланс</p>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mt-1">
                {{ number_format(\App\Models\Expense::sum('income') - \App\Models\Expense::sum('expense'), 2, '.', ' ') }} ₽
            </h2>
        </div>
    </div>
</div>
