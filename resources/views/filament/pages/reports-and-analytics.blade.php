<x-filament-panels::page>
    <div style="width: 100%" class="p-6 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
        <form wire:submit="submitFilters">
            {{ $this->form }}
        </form>
    </div>
</x-filament-panels::page>