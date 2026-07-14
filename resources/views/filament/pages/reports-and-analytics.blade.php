<x-filament-panels::page>
    <div class="p-6 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
        <form wire:submit="submitFilters">
            {{ $this->form }}
            
            <div class="mt-4 flex justify-end">
                <x-filament::button type="submit" icon="heroicon-m-funnel" color="primary">
                    Apply Filters
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>