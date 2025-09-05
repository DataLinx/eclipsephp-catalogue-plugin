<?php

namespace Eclipse\Catalogue\Models;

use Eclipse\Catalogue\Enums\PropertyInputType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomPropertyValue extends Model
{
    protected $table = 'catalogue_product_has_custom_prop_value';

    protected $fillable = [
        'product_id',
        'property_id',
        'value',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function getValueAttribute($value)
    {
        if ($this->property && $this->property->supportsMultilang()) {
            return json_decode($value, true) ?? $value;
        }

        // Handle type casting based on property input_type
        if ($this->property && $this->property->isCustomType()) {
            return $this->castValue($value, $this->property->input_type);
        }

        return $value;
    }

    public function setValueAttribute($value)
    {
        if ($this->property && $this->property->supportsMultilang() && is_array($value)) {
            $this->attributes['value'] = json_encode($value);
        } else {
            $this->attributes['value'] = $value;
        }
    }

    public function getFormattedValue(): string
    {
        if ($this->property && $this->property->supportsMultilang()) {
            $value = $this->value;
            if (is_array($value)) {
                // Return the first non-empty value or empty string
                foreach ($value as $langValue) {
                    if (! empty($langValue)) {
                        return $langValue;
                    }
                }

                return '';
            }
        }

        return (string) $this->value;
    }

    protected function castValue($value, $inputType)
    {
        if ($value === null || $value === '') {
            return null;
        }

        switch ($inputType) {
            case PropertyInputType::INTEGER->value:
                return (int) $value;
            case PropertyInputType::DECIMAL->value:
                return (float) $value;
            case PropertyInputType::DATE->value:
            case PropertyInputType::DATETIME->value:
                return $value; // Keep as string for dates
            case PropertyInputType::STRING->value:
            case PropertyInputType::TEXT->value:
            case PropertyInputType::FILE->value:
            default:
                return (string) $value;
        }
    }
}
