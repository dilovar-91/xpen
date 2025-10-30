# 💰 Система учёта доходов и расходов (Laravel + Livewire + Filament + Tailwind)

Это веб-приложение для **учёта финансов** — доходов, расходов и анализа операций.  
Построено на **Laravel**, с использованием **Livewire** для реактивных интерфейсов и **Filament UI** для удобных элементов управления.  
Интерфейс полностью адаптивен и поддерживает **тёмную тему** 🌙.

---

## 🚀 Основные возможности

- 💵 **Учёт доходов и расходов**  
  Добавляйте, редактируйте и удаляйте финансовые операции.
- 📅 **Фильтры по дате и типу**  
  Быстро выбирайте период: сегодня, вчера, за всё время.
- 📊 **Аналитика и статистика** *(при необходимости)*  
  Графики и таблицы с итогами по периодам и категориям.
- 🌗 **Поддержка тёмной темы**  
  Автоматическая адаптация интерфейса под системную тему.
- ⚡ **Мгновенные обновления через Livewire**  
  Данные обновляются без перезагрузки страницы.
- 🧱 **Filament UI**  
  Единый стиль и готовые UI-компоненты (кнопки, формы и т.д.)
- 🎨 **TailwindCSS**  
  Современный и отзывчивый дизайн с плавными переходами.

---

## 🛠️ Технологии

| Технология | Назначение |
|-------------|-------------|
| **Laravel** | Основной backend-фреймворк |
| **Livewire** | Реактивный frontend без JavaScript |
| **Filament UI** | Готовые UI-компоненты для Laravel |
| **TailwindCSS** | Utility-first стилизация |
| **Vite** | Быстрая сборка и обновление фронтенда |

---

## 📂 Описание интерфейса фильтров

Панель фильтрации позволяет выбрать период и тип операций:

- Поля выбора дат (`dateFrom`, `dateTo`)
- Выпадающий список типов операций (Все / Приход / Расход)
- Три быстрые кнопки:
    - 🟩 **Сегодня**
    - 🟦 **Вчера и сегодня**
    - 🟥 **Все время**

Пример шаблона:
```blade
<div class="flex flex-wrap items-end justify-between gap-2 mb-4 p-2 
    bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 
    rounded-lg shadow-sm transition-colors duration-300">

    <div class="flex flex-wrap items-end gap-2">
        <input wire:model.live="dateFrom" type="date" class="input-field">
        <input wire:model.live="dateTo" type="date" class="input-field">

        <select wire:model.live="type" class="input-field">
            <option value="">Все типы</option>
            <option value="1">Приход</option>
            <option value="2">Расход</option>
        </select>

        <x-filament::button wire:click="resetDates" color="success">Сегодня</x-filament::button>
        <x-filament::button wire:click="resetTwoDates" color="primary">Вчера и сегодня</x-filament::button>
        <x-filament::button wire:click="clearDates" color="danger">Все время</x-filament::button>
    </div>
</div>
