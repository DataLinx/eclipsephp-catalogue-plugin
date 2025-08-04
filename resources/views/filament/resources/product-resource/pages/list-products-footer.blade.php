<div x-data="productGalleryLightbox()" x-init="init()">
    <!-- Lightbox Modal -->
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="isOpen = false"
         @keydown.escape.window="isOpen = false"
         class="product-list-lightbox-overlay"
         x-cloak>
        
        <div class="product-list-lightbox-container" @click.stop>
            <button type="button" @click.stop="isOpen = false" class="product-list-lightbox-close">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            
            <div class="product-list-lightbox-image-wrapper">
                <img :src="currentImage.url" 
                     :alt="getLocalizedName()"
                     class="product-list-lightbox-image">
                <div class="product-list-lightbox-info" x-show="getLocalizedName() || getLocalizedDescription()">
                    <p class="product-list-lightbox-title" x-text="getLocalizedName()" x-show="getLocalizedName()"></p>
                    <p class="product-list-lightbox-description" x-text="getLocalizedDescription()" x-show="getLocalizedDescription()"></p>
                </div>
            </div>
            <template x-if="products && products.length > 1">
                <div>
                    <button type="button" @click.stop.prevent="previous()" class="product-list-lightbox-nav prev">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    
                    <button type="button" @click.stop.prevent="next()" class="product-list-lightbox-nav next">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
            </template>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
    
    .product-list-lightbox-overlay {
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

    .product-list-lightbox-container {
        position: relative;
        max-width: 90vw;
        max-height: 90vh;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999999999 !important;
    }

    .product-list-lightbox-close {
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

    .product-list-lightbox-close:hover {
        opacity: 1;
    }

    .product-list-lightbox-close svg {
        width: 32px;
        height: 32px;
    }

    .product-list-lightbox-image-wrapper {
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

    .product-list-lightbox-image {
        max-width: 100%;
        max-height: 85vh;
        width: auto;
        height: auto;
        object-fit: contain;
        display: block;
    }

    .product-list-lightbox-nav {
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

    .product-list-lightbox-nav:hover {
        background-color: rgba(255, 255, 255, 0.2);
    }

    .product-list-lightbox-nav.prev {
        left: -60px;
    }

    .product-list-lightbox-nav.next {
        right: -60px;
    }

    .product-list-lightbox-nav svg {
        width: 24px;
        height: 24px;
    }

    .product-list-lightbox-info {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(to top, rgba(0, 0, 0, 0.9), transparent);
        padding: 24px;
        color: white;
        border-radius: 0 0 8px 8px;
    }

    .product-list-lightbox-title {
        font-size: 18px;
        font-weight: 600;
        margin: 0 0 8px 0;
    }

    .product-list-lightbox-description {
        font-size: 14px;
        opacity: 0.9;
        margin: 0;
        line-height: 1.5;
    }
</style>

<script>
function productGalleryLightbox() {
    return {
        isOpen: false,
        currentIndex: 0,
        products: [],
        currentImage: {
            url: '',
            name: {},
            description: {},
            file_name: '',
            productName: '',
            productCode: ''
        },
        
        getLocale() {
            return window.Livewire?.find(document.querySelector('[wire\\:id]')?.getAttribute('wire:id'))?.activeLocale || 'en';
        },
        
        getLocalizedName() {
            const currentLocale = this.getLocale();
            if (!this.currentImage.name || typeof this.currentImage.name !== 'object') {
                return this.currentImage.file_name || '';
            }
            return this.currentImage.name[currentLocale] || this.currentImage.name['en'] || this.currentImage.file_name || '';
        },
        
        getLocalizedDescription() {
            const currentLocale = this.getLocale();
            if (!this.currentImage.description || typeof this.currentImage.description !== 'object') {
                return '';
            }
            return this.currentImage.description[currentLocale] || this.currentImage.description['en'] || '';
        },
        
        init() {
            const self = this;
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('product-image-trigger')) {
                    e.preventDefault();
                    e.stopPropagation();
                    self.openFromTable(e.target);
                }
            }, true);
            document.addEventListener('keydown', (e) => {
                if (this.isOpen) {
                    if (e.key === 'ArrowLeft') {
                        e.preventDefault();
                        this.previous();
                    } else if (e.key === 'ArrowRight') {
                        e.preventDefault();
                        this.next();
                    }
                }
            });
        },
        
        openFromTable(imageElement) {
            const rows = document.querySelectorAll('tbody tr');
            this.products = [];
            let clickedIndex = 0;
            
            rows.forEach((row, index) => {
                const img = row.querySelector('.fi-ta-image img');
                
                if (img) {
                    let imageName = {};
                    let imageDescription = {};
                    let fileName = '';
                    
                    try {
                        if (img.dataset.imageName) {
                            imageName = JSON.parse(img.dataset.imageName);
                        }
                        if (img.dataset.imageDescription) {
                            imageDescription = JSON.parse(img.dataset.imageDescription);
                        }
                        fileName = img.dataset.filename || '';
                    } catch (e) {
                        console.error('Error parsing image data:', e);
                    }
                    
                    const product = {
                        url: img.dataset.url || img.src,
                        name: imageName,
                        description: imageDescription,
                        file_name: fileName,
                        productCode: img.dataset.productCode || ''
                    };
                    
                    this.products.push(product);
                    
                    if (img === imageElement) {
                        clickedIndex = this.products.length - 1;
                    }
                }
            });
            
            if (this.products.length > 0) {
                this.currentIndex = clickedIndex;
                this.updateCurrentImage();
                this.open();
            }
        },
        
        open() {
            this.isOpen = true;
            document.body.style.overflow = 'hidden';
        },
        
        
        next() {
            if (this.products.length > 0) {
                this.currentIndex = (this.currentIndex + 1) % this.products.length;
                this.updateCurrentImage();
            }
        },
        
        previous() {
            if (this.products.length > 0) {
                this.currentIndex = (this.currentIndex - 1 + this.products.length) % this.products.length;
                this.updateCurrentImage();
            }
        },
        
        updateCurrentImage() {
            if (this.products[this.currentIndex]) {
                this.currentImage = this.products[this.currentIndex];
            }
        }
    };
}
</script>