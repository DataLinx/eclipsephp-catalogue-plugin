<?php

namespace Eclipse\Catalogue\Models;

use Eclipse\Catalogue\Casts\BackgroundCast;
use Eclipse\Catalogue\Factories\PropertyValueFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class PropertyValue extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia;

    protected $table = 'pim_property_value';

    protected $fillable = [
        'property_id',
        'value',
        'sort',
        'info_url',
        'image',
        'color',
    ];

    public array $translatable = [
        'value',
        'info_url',
        'image',
    ];

    protected $casts = [
        'sort' => 'integer',
        'property_id' => 'integer',
        'color' => BackgroundCast::class,
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'catalogue_product_has_property_value', 'property_value_id', 'product_id')
            ->withTimestamps();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'])
            ->useDisk('public');
    }

    protected static function newFactory(): PropertyValueFactory
    {
        return PropertyValueFactory::new();
    }

    protected static function booted(): void
    {
        static::addGlobalScope('orderBySort', function ($query) {
            $query->orderBy('sort');
        });
    }

    /**
     * Ensure Filament receives scalar values for form hydration.
     *
     * In particular, return the current-locale string (or null) for the
     * translatable `image` attribute instead of the full translations array.
     */
    public function attributesToArray(): array
    {
        $attributes = parent::attributesToArray();

        if (array_key_exists('image', $attributes) && is_array($attributes['image'])) {
            $translation = $this->getTranslation('image', app()->getLocale());
            $attributes['image'] = $translation !== '' ? $translation : null;
        }

        if (array_key_exists('color', $this->attributes)) {
            $raw = $this->attributes['color'];
            if (is_string($raw) && $raw !== '') {
                $decoded = json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $attributes['color'] = $decoded;
                }
            } elseif (is_object($this->getAttribute('color')) && method_exists($this->getAttribute('color'), 'toArray')) {
                $attributes['color'] = $this->getAttribute('color')->toArray();
            } else {
                $attributes['color'] = null;
            }
        }

        return $attributes;
    }

    /**
     * Get the color of the property value.
     */
    public function getColor(): ?string
    {
        if (! array_key_exists('color', $this->attributes)) {
            return null;
        }

        $bg = $this->getAttribute('color');
        if (is_object($bg)) {
            return (string) $bg;
        }

        return null;
    }
}
