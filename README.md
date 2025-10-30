# 💼 Laravel Livewire Filters UI (with Dark Mode Support)

This project provides a **responsive and theme-adaptive filter panel** built with **Laravel**, **Livewire**, **TailwindCSS**, and **Filament UI**.  
It allows users to filter records by date range and transaction type, featuring a clean, minimal design with **light / dark theme** support.

---

## 🚀 Features

- 📅 **Date filters** — choose a start and end date (`dateFrom`, `dateTo`)
- 🔄 **Quick filter buttons** — “Today”, “Yesterday and Today”, “All Time”
- 🧾 **Type selector** — filter by transaction type (Income / Expense)
- 🌗 **Full dark mode support** (Tailwind `dark:` classes)
- ⚡ **Reactive Livewire components** for instant UI updates
- 🎨 **Responsive design** with smooth transitions
- 🧱 Built on **Filament UI buttons** for consistent styling

---

## 🛠️ Tech Stack

| Tool | Purpose |
|------|----------|
| **Laravel** | Backend framework |
| **Livewire** | Reactive UI and state management |
| **TailwindCSS** | Utility-first CSS styling |
| **Filament UI** | Elegant Laravel admin components |
| **Vite** | Fast asset bundling and hot-reload |

---

## 🧩 Component Overview

The main component displays a filter toolbar with:
- Two date inputs (`dateFrom`, `dateTo`)
- A select dropdown for `type`
- Three quick-action buttons bound to Livewire methods:
    - `resetDates()` → set to **Today**
    - `resetTwoDates()` → set to **Yesterday and Today**
    - `clearDates()` → set to **All Time**

---

## 🌙 Dark Mode Support

The UI automatically adapts to dark mode using Tailwind’s `dark:` variants:
```html
<div class="bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">
