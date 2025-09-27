<div class="flex justify-center">
    <input 
        type="checkbox" 
        wire:click="toggleProductSelection({{ $getState()['id'] }})"
        @if($getState()['checked']) checked @endif
        class="fi-checkbox-input rounded border-none bg-white shadow-sm ring-1 ring-gray-950/10 checked:ring-0 focus:ring-2 focus:ring-primary-600 focus:checked:ring-primary-600 disabled:bg-gray-50 disabled:text-gray-50 disabled:checked:bg-current disabled:checked:text-gray-400 dark:bg-white/5 dark:ring-white/20 dark:checked:bg-primary-500 dark:focus:ring-primary-500 dark:disabled:bg-transparent dark:disabled:ring-white/10 dark:disabled:checked:bg-white/5 dark:disabled:checked:ring-white/10 text-primary-600 dark:text-primary-500"
    />
</div>
