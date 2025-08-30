<?php

namespace Eclipse\Catalogue\Models;

use Eclipse\Catalogue\Factories\PropertyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Property extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $table = 'pim_property';

    protected $fillable = [
        'code',
        'name',
        'description',
        'internal_name',
        'is_active',
        'is_global',
        'max_values',
        'enable_sorting',
        'is_filter',
        'type',
        'input_type',
        'is_multilang',
    ];

    public array $translatable = [
        'name',
        'description',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'is_active' => 'boolean',
        'is_global' => 'boolean',
        'enable_sorting' => 'boolean',
        'is_filter' => 'boolean',
        'max_values' => 'integer',
        'is_multilang' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Property $property) {
            if ($property->code) {
                $property->code = strtolower($property->code);
            }
        });

        static::updating(function (Property $property) {
            if ($property->isDirty('code') && $property->code) {
                $property->code = strtolower($property->code);
            }
        });

        static::created(function (Property $property) {
            if ($property->is_global) {
                $property->assignToAllProductTypes();
            }
        });

        static::updated(function (Property $property) {
            if ($property->wasChanged('is_global') && $property->is_global) {
                $property->assignToAllProductTypes();
            }
        });

        static::deleting(function (Property $property) {
            if ($property->isForceDeleting()) {
                // Force delete related values
                $property->values()->forceDelete();
                // Delete pivot rows
                $property->productTypes()->detach();
            }
        });
    }

    public function values(): HasMany
    {
        return $this->hasMany(PropertyValue::class);
    }

    public function productTypes(): BelongsToMany
    {
        return $this->belongsToMany(ProductType::class, 'pim_product_type_has_property')
            ->withPivot('sort')
            ->withTimestamps()
            ->orderByPivot('sort');
    }

    public function assignToAllProductTypes(): void
    {
        $existingTypeIds = $this->productTypes()->pluck('pim_product_types.id')->toArray();
        $allTypeIds = ProductType::pluck('id')->toArray();
        $newTypeIds = array_diff($allTypeIds, $existingTypeIds);

        if (! empty($newTypeIds)) {
            $attachData = [];
            foreach ($newTypeIds as $typeId) {
                $attachData[$typeId] = ['sort' => 0];
            }
            $this->productTypes()->attach($attachData);
        }
    }

    public function isCustomType(): bool
    {
        return $this->type === 'custom';
    }

    public function isListType(): bool
    {
        return $this->type === 'list';
    }

    public function supportsMultilang(): bool
    {
        return $this->is_multilang && in_array($this->input_type, ['string', 'text', 'file']);
    }

    public function getInputValidationRules(): array
    {
        if (! $this->isCustomType()) {
            return [];
        }

        $rules = [];

        switch ($this->input_type) {
            case 'string':
                $rules[] = 'string|max:255';
                break;
            case 'text':
                $rules[] = 'string|max:65535';
                break;
            case 'integer':
                $rules[] = 'integer';
                break;
            case 'decimal':
                $rules[] = 'numeric';
                break;
            case 'date':
                $rules[] = 'date';
                break;
            case 'datetime':
                $rules[] = 'date';
                break;
            case 'file':
                $rules[] = 'file';
                if ($this->max_values > 1) {
                    $rules[] = 'array';
                }
                break;
        }

        return $rules;
    }

    public function getFormFieldType(): string
    {
        $valueCount = $this->values()->count();

        if ($this->max_values === 1) {
            return $valueCount < 4 ? 'radio' : 'select';
        } else {
            return $valueCount < 4 ? 'checkbox' : 'multiselect';
        }
    }

    protected static function newFactory(): PropertyFactory
    {
        return PropertyFactory::new();
    }
}
