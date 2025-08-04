
<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div 
        x-data="imageManager({
            state: $wire.{{ $applyStateBindingModifiers("entangle('{$getStatePath()}')") }},
            getLocale: () => $wire.activeLocale || 'en',
        })"
        wire:key="image-manager-{{ str_replace('.', '-', $getStatePath()) }}"
        class="eclipse-image-manager"
        style="display: flex; flex-direction: column; gap: 1rem;"
    >
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            @if($getAction('upload'))
                {{ $getAction('upload') }}
            @endif
            @if($getAction('urlUpload'))
                {{ $getAction('urlUpload') }}
            @endif
        </div>
        <div x-cloak wire:ignore>
        <div x-show="state && state.length > 0" style="display: flex; flex-direction: column; gap: 1rem;">
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 12px;">
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
                            x-show="dropTargetIndex === index && draggingIndex !== index"
                            x-cloak
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            class="absolute inset-0 bg-primary-500/10 dark:bg-primary-400/10 z-10 pointer-events-none"
                        >
                            <div class="absolute inset-0 border-2 border-dashed border-primary-500 dark:border-primary-400 rounded-lg m-2"></div>
                        </div>
                        
                        <div class="eclipse-image-card-container" style="position: relative;">
                            <img
                                :src="image.thumb_url || image.url"
                                :alt="image.file_name"
                                class="eclipse-image-card-img"
                                @click="openImageModal(index)"
                            />
                            <div
                                class="absolute z-20"
                                style="top: 8px; left: 8px;"
                            >
                                <template x-if="!image.is_cover">
                                    <x-filament::button
                                        size="xs"
                                        color="primary"
                                        x-on:click.stop="$wire.mountFormComponentAction('{{ $getStatePath() }}', 'setCover', { arguments: { uuid: image.uuid } })"
                                        class="shadow-sm"
                                    >
                                        Set as Cover
                                    </x-filament::button>
                                </template>
                                <template x-if="image.is_cover">
                                    <x-filament::button 
                                        size="xs" 
                                        color="success"
                                        class="shadow-sm"
                                        disabled
                                    >
                                        ✓ Cover
                                    </x-filament::button>
                                </template>
                            </div>
                        </div>
                        <div class="p-3">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate" 
                               x-text="getLocalizedName(image)"></p>
                            <p class="text-xs text-gray-600 dark:text-gray-400 line-clamp-1" 
                               x-text="getLocalizedDescription(image)"
                               x-show="getLocalizedDescription(image)"></p>
                            <div class="flex flex-wrap items-center gap-2 mt-2">
                                <x-filament::button
                                    size="xs"
                                    color="gray"
                                    x-on:click="$wire.mountFormComponentAction('{{ $getStatePath() }}', 'editImage', { arguments: { uuid: image.uuid, selectedLocale: getLocale() } })"
                                >
                                    <x-slot name="icon">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </x-slot>
                                    Edit
                                </x-filament::button>
                                
                                <x-filament::button
                                    size="xs"
                                    color="danger"
                                    x-on:click="$wire.mountFormComponentAction('{{ $getStatePath() }}', 'deleteImage', { arguments: { uuid: image.uuid } })"
                                >
                                    <x-slot name="icon">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </x-slot>
                                    Delete
                                </x-filament::button>
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
        
        <div x-show="lightboxOpen" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="lightboxOpen = false"
             @keydown.escape.window="lightboxOpen = false"
             class="eclipse-image-lightbox-overlay"
             x-cloak>
            
            <div class="eclipse-image-lightbox-container" @click.stop>
                <button type="button" @click.stop="lightboxOpen = false" class="eclipse-image-lightbox-close">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
                
                <div class="eclipse-image-lightbox-image-wrapper">
                    <img :src="lightboxImage" 
                         :alt="lightboxAlt"
                         class="eclipse-image-lightbox-image">
                    <div class="eclipse-image-lightbox-info" x-show="lightboxName || lightboxDescription">
                        <p class="eclipse-image-lightbox-title" x-text="lightboxName" x-show="lightboxName"></p>
                        <p class="eclipse-image-lightbox-description" x-text="lightboxDescription" x-show="lightboxDescription"></p>
                    </div>
                </div>
                <template x-if="state && state.length > 1">
                    <div>
                        <button type="button" @click.stop.prevent="previousImage()" class="eclipse-image-lightbox-nav prev">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        
                        <button type="button" @click.stop.prevent="nextImage()" class="eclipse-image-lightbox-nav next">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>
        </div>
        
    </div>
    
    @once
    <style>
        [x-cloak] { 
            display: none !important; 
        }

        .eclipse-image-manager [draggable="true"] {
            cursor: move;
        }

        .eclipse-image-manager [draggable="true"]:active {
            cursor: grabbing;
        }

        .eclipse-image-lightbox-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 9999!important;
            background-color: rgba(0, 0, 0, 0.95);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .eclipse-image-lightbox-container {
            position: relative;
            max-width: 90vw;
            max-height: 90vh;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999999999 !important;
        }

        .eclipse-image-lightbox-close {
            position: absolute;
            top: -50px;
            right: 0;
            color: white;
            background: none;
            border: none;
            cursor: pointer;
            padding: 10px;
            opacity: 0.8;
            transition: opacity 0.2s;
        }

        .eclipse-image-lightbox-close:hover {
            opacity: 1;
        }

        .eclipse-image-lightbox-close svg {
            width: 32px;
            height: 32px;
        }

        .eclipse-image-lightbox-image-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #1f2937;
            border-radius: 8px;
            overflow: hidden;
            max-width: 90vw;
            max-height: 85vh;
        }

        .eclipse-image-lightbox-image {
            max-width: 100%;
            max-height: 85vh;
            width: auto;
            height: auto;
            object-fit: contain;
            display: block;
        }

        .eclipse-image-lightbox-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border: none;
            border-radius: 50%;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .eclipse-image-lightbox-nav:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .eclipse-image-lightbox-nav.prev {
            left: -60px;
        }

        .eclipse-image-lightbox-nav.next {
            right: -60px;
        }

        .eclipse-image-lightbox-nav svg {
            width: 24px;
            height: 24px;
        }

        .eclipse-image-lightbox-info {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.9), transparent);
            padding: 24px;
            color: white;
            border-radius: 0 0 8px 8px;
        }

        .eclipse-image-lightbox-title {
            font-size: 18px;
            font-weight: 600;
            margin: 0 0 8px 0;
        }

        .eclipse-image-lightbox-description {
            font-size: 14px;
            opacity: 0.9;
            margin: 0;
            line-height: 1.5;
        }

        .eclipse-image-card-container {
            height: 150px;
            background-color: #f3f4f6;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .eclipse-image-card-img {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
        }
    </style>
    @endonce
    
    <script>
        function imageManager({ state, getLocale }) {
            return {
                state: state || [],
                getLocale: getLocale,
                
                init() {
                    if (!Array.isArray(this.state)) {
                        this.state = [];
                    }
                    
                    this.draggingIndex = null;
                    this.dropTargetIndex = null;
                    this.dragCounter = 0;
                    this.lightboxOpen = false;
                    this.lightboxIndex = 0;
                    this.lightboxImage = '';
                    this.lightboxAlt = '';
                    this.lightboxName = '';
                    this.lightboxDescription = '';
                    
                    // Add keyboard navigation listeners
                    document.addEventListener('keydown', (e) => {
                        if (this.lightboxOpen) {
                            if (e.key === 'ArrowLeft') {
                                e.preventDefault();
                                this.previousImage();
                            } else if (e.key === 'ArrowRight') {
                                e.preventDefault();
                                this.nextImage();
                            }
                        }
                    });
                },
                
                getLocalizedName(image) {
                    const currentLocale = this.getLocale();
                    if (!image.name || typeof image.name !== 'object') {
                        return image.file_name || '';
                    }
                    return image.name[currentLocale] || image.name['en'] || image.file_name || '';
                },
                
                getLocalizedDescription(image) {
                    const currentLocale = this.getLocale();
                    if (!image.description || typeof image.description !== 'object') {
                        return '';
                    }
                    return image.description[currentLocale] || image.description['en'] || '';
                },
                
                openImageModal(index) {
                    this.lightboxIndex = index;
                    const image = this.state[index];
                    this.lightboxImage = image.url;
                    this.lightboxAlt = image.file_name;
                    this.lightboxName = this.getLocalizedName(image);
                    this.lightboxDescription = this.getLocalizedDescription(image);
                    this.lightboxOpen = true;
                },
                
                previousImage() {
                    this.lightboxIndex = (this.lightboxIndex - 1 + this.state.length) % this.state.length;
                    this.updateLightboxImage();
                },
                
                nextImage() {
                    this.lightboxIndex = (this.lightboxIndex + 1) % this.state.length;
                    this.updateLightboxImage();
                },
                
                updateLightboxImage() {
                    const image = this.state[this.lightboxIndex];
                    this.lightboxImage = image.url;
                    this.lightboxAlt = image.file_name;
                    this.lightboxName = this.getLocalizedName(image);
                    this.lightboxDescription = this.getLocalizedDescription(image);
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