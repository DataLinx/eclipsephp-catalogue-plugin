@php($rec = $getRecord())

<div class="flex flex-wrap items-center gap-1">
    @if($rec->is_group)
        <x-filament::badge color="primary">{{ __('eclipse-catalogue::property-value.ui.group_badge') }}</x-filament::badge>
    @endif

    @if($rec->group)
        <x-filament::badge color="warning">{{ $rec->group->value }}</x-filament::badge>
    @endif

    @php($aliases = $rec->members()->count())
    @if($rec->is_group)
        <x-filament::badge color="gray">{{ $aliases }}</x-filament::badge>
    @endif
</div>


