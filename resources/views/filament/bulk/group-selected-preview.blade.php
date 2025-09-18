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
                    <span class="font-medium">{{ $record->value }}</span>
                    @if($record->is_group)
                        <x-filament::badge color="primary" class="!inline-flex !w-auto !px-2 !py-0.5">{{ __('eclipse-catalogue::property-value.ui.group_badge') }}</x-filament::badge>
                    @elseif($record->group)
                        <x-filament::badge color="warning" class="!inline-flex !w-auto !px-2 !py-0.5">{{ $record->group->value }}</x-filament::badge>
                    @endif
                </div>
            </li>
        @endforeach
    </ul>
</div>


