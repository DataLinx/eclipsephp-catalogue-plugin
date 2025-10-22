@php($items = $getState() ?? [])

<div class="space-y-3 max-h-72 overflow-auto">
    <div class="text-sm font-medium">
        {{ __('eclipse-catalogue::property-value.grouping.selected_values') }}
    </div>
    <ul class="ms-1 space-y-2">
        @foreach($items as $record)
            <li>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-gray-400 select-none">-</span>
                    <span class="font-medium">{{ is_array($record) ? ($record['value'] ?? '') : ($record->value ?? '') }}</span>
                    @if(is_array($record) ? ($record['is_group'] ?? false) : ($record->is_group ?? false))
                        <x-filament::badge color="primary" class="!inline-flex !w-auto !px-2 !py-0.5">{{ __('eclipse-catalogue::property-value.ui.group_badge') }}</x-filament::badge>
                    @elseif(is_array($record) ? !empty($record['group_value'] ?? null) : !empty(optional($record->group)->value))
                        <x-filament::badge color="warning" class="!inline-flex !w-auto !px-2 !py-0.5">{{ is_array($record) ? ($record['group_value'] ?? '') : (optional($record->group)->value ?? '') }}</x-filament::badge>
                    @endif
                </div>
            </li>
        @endforeach
    </ul>
</div>


