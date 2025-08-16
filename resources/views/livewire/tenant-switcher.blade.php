<div class="tenant-switcher">
    <div class="mb-4">
        <label for="tenant-select" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ $label }}
        </label>
        <select 
            id="tenant-select"
            wire:model.live="selectedTenant"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
        >
            <option value="">{{ $placeholder }}</option>
            @foreach($tenants as $tenant)
                <option value="{{ $tenant->id }}" @if($tenant->id === $currentTenant?->id) selected @endif>
                    {{ $tenant->name }}
                    @if($tenant->id === $currentTenant?->id)
                        ({{ __('eclipse-catalogue::price-list.labels.current') }})
                    @endif
                </option>
            @endforeach
        </select>
    </div>
</div>