@php
    $currentLocale = $getSelectedLocale();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div 
        x-data="imageManager({
            state: $wire.{{ $applyStateBindingModifiers("entangle('{$getStatePath()}')") }},
            currentLocale: @js($currentLocale),
        })"
        wire:key="image-manager-{{ $getStatePath() }}-{{ $currentLocale }}"
        class="space-y-4"
    >
        <div class="flex items-center gap-2">
            @if($getAction('upload'))
                {{ $getAction('upload') }}
            @endif
            @if($getAction('urlUpload'))
                {{ $getAction('urlUpload') }}
            @endif
        </div>
        <div x-show="state && state.length > 0" class="space-y-4">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <template x-for="(image, index) in state" :key="image.uuid">
                    <div
                        class="relative group bg-white dark:bg-gray-800 rounded-lg shadow-sm border overflow-hidden transition-all duration-300"
                        :class="{
                            'opacity-50': draggingIndex === index,
                            'border-gray-200 dark:border-gray-700 hover:shadow-md': dropTargetIndex !== index,
                            'border-primary-500 dark:border-primary-400 shadow-lg': dropTargetIndex === index && draggingIndex !== index,
                            'cursor-move': true
                        }"
                        draggable="true"
                        @dragstart="dragStart($event, index)"
                        @dragenter.prevent="dragEnter($event, index)"
                        @dragover.prevent="dragOver($event, index)"
                        @dragleave="dragLeave($event, index)"
                        @drop="drop($event, index)"
                        @dragend="dragEnd($event)"
                    >
                        <div
                            class="absolute top-2 left-2 z-20 opacity-0 group-hover:opacity-100 transition-opacity cursor-move"
                            :class="{'opacity-100': draggingIndex === index}"
                        >
                            <div class="bg-white/90 dark:bg-gray-800/90 rounded p-1.5 shadow-sm">
                                <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                                </svg>
                            </div>
                        </div>
                        <div
                            x-show="image.is_cover"
                            class="absolute top-2 right-2 z-20 bg-primary-500 text-white text-xs px-2 py-1 rounded-full font-medium shadow-sm"
                        >
                            Cover
                        </div>
                        
                        <div 
                            x-show="dropTargetIndex === index && draggingIndex !== index"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            class="absolute inset-0 bg-primary-500/10 dark:bg-primary-400/10 z-10 pointer-events-none"
                        >
                            <div class="absolute inset-0 border-2 border-dashed border-primary-500 dark:border-primary-400 rounded-lg m-2"></div>
                        </div>
                        
                        <div class="aspect-square bg-gray-100 dark:bg-gray-900 cursor-pointer" @click="openImageModal(index)">
                            <img
                                :src="image.thumb_url || image.url"
                                :alt="image.file_name"
                                class="w-full h-full object-cover aspect-square"
                            />
                        </div>
                        <div class="p-3 space-y-2">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate" 
                               x-text="getLocalizedName(image)"></p>
                            <p class="text-xs text-gray-600 dark:text-gray-400 line-clamp-2" 
                               x-text="getLocalizedDescription(image)"
                               x-show="getLocalizedDescription(image)"></p>
                            <div class="flex flex-wrap items-center gap-1">
                                <button
                                    type="button"
                                    @click="$wire.mountFormComponentAction('{{ $getStatePath() }}', 'editImage', { arguments: { uuid: image.uuid, selectedLocale: currentLocale } })"
                                    class="text-xs text-primary-600 dark:text-primary-400 hover:underline font-medium inline-flex items-center gap-1"
                                >
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Edit
                                </button>
                                
                                <button
                                    type="button"
                                    @click="handleSetCover(image)"
                                    class="text-xs font-medium inline-flex items-center gap-1"
                                    :class="image.is_cover ? 'text-gray-500 dark:text-gray-400 cursor-not-allowed' : 'text-primary-600 dark:text-primary-400 hover:underline'"
                                    :disabled="image.is_cover"
                                >
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                    </svg>
                                    <span x-text="image.is_cover ? 'Is Cover' : 'Set as Cover'"></span>
                                </button>
                                
                                <button
                                    type="button"
                                    @click="$wire.mountFormComponentAction('{{ $getStatePath() }}', 'deleteImage', { arguments: { uuid: image.uuid } })"
                                    class="text-xs text-danger-600 dark:text-danger-400 hover:underline font-medium inline-flex items-center gap-1"
                                >
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
        <div x-show="!state || state.length === 0" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No images uploaded yet</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Click "Upload Files" or "Add from URL" to get started</p>
        </div>
        
    </div>
    
    <style>
        .grid > div {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        [draggable="true"] {
            cursor: move;
        }
        
        [draggable="true"]:active {
            cursor: grabbing;
        }
        
        .dragging {
            opacity: 0.5;
        }
    </style>
    
    <script>
        function imageManager({ state, currentLocale }) {
            return {
                state: state || [],
                currentLocale: currentLocale || 'en',
                
                init() {
                    if (!Array.isArray(this.state)) {
                        this.state = [];
                    }
                    
                    this.draggingIndex = null;
                    this.dropTargetIndex = null;
                    this.dragCounter = 0;
                },
                
                getLocalizedName(image) {
                    if (!image.name || typeof image.name !== 'object') {
                        return image.file_name || '';
                    }
                    return image.name[this.currentLocale] || image.name['en'] || image.file_name || '';
                },
                
                getLocalizedDescription(image) {
                    if (!image.description || typeof image.description !== 'object') {
                        return '';
                    }
                    return image.description[this.currentLocale] || image.description['en'] || '';
                },
                
                openImageModal(index) {
                    window.open(this.state[index].url, '_blank');
                },
                
                handleSetCover(image) {
                    if (image.is_cover) return;
                    this.$wire.mountFormComponentAction('{{ $getStatePath() }}', 'setCover', { arguments: { uuid: image.uuid } });
                },
                
                dragStart(event, index) {
                    this.draggingIndex = index;
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/html', event.target.innerHTML);
                    
                },
                
                dragEnter(event, index) {
                    if (this.draggingIndex !== null && this.draggingIndex !== index) {
                        this.dragCounter++;
                        this.dropTargetIndex = index;
                        this.showDropPreview(index);
                    }
                },
                
                dragOver(event, index) {
                    if (event.preventDefault) {
                        event.preventDefault();
                    }
                    event.dataTransfer.dropEffect = 'move';

                    if (this.draggingIndex !== null && this.draggingIndex !== index && this.dropTargetIndex !== index) {
                        this.dropTargetIndex = index;
                        this.showDropPreview(index);
                    }
                    
                    return false;
                },
                
                dragLeave(event, index) {
                    this.dragCounter--;
                    if (this.dragCounter === 0) {
                        this.dropTargetIndex = null;
                    }
                },
                
                showDropPreview(targetIndex) {
                    if (this.draggingIndex === null || targetIndex === this.draggingIndex) return;

                    const draggedItem = this.state[this.draggingIndex];
                    const newState = [...this.state];

                    newState.splice(this.draggingIndex, 1);

                    newState.splice(targetIndex, 0, draggedItem);

                    this.state = newState;

                    this.draggingIndex = targetIndex;
                },
                
                drop(event, dropIndex) {
                    if (event.stopPropagation) {
                        event.stopPropagation();
                    }
                    
                    const newOrder = this.state.map(item => item.uuid);
                    this.$wire.mountFormComponentAction('{{ $getStatePath() }}', 'reorder', { items: newOrder });
                    
                    return false;
                },
                
                dragEnd(event) {
                    this.draggingIndex = null;
                    this.dropTargetIndex = null;
                    this.dragCounter = 0;
                }
            };
        }
    </script>
</x-dynamic-component>