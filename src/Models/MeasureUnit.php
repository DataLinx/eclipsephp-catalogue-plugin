<?php

namespace Eclipse\Catalogue\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class MeasureUnit extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pim_measure_units';

    protected $fillable = [
        'name',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // If this unit is being set as default, unset all other defaults
            if ($model->is_default) {
                static::where('is_default', true)
                    ->where('id', '!=', $model->id)
                    ->update(['is_default' => false]);
            }
        });

        static::deleting(function ($model) {
            // Prevent deletion of default unit
            if ($model->is_default) {
                throw ValidationException::withMessages([
                    'is_default' => 'Cannot delete the default unit of measure.',
                ]);
            }
        });
    }

    /**
     * Get the default measure unit
     */
    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->first();
    }

    /**
     * Check if this is the default unit
     */
    public function isDefault(): bool
    {
        return $this->is_default;
    }

    protected static function newFactory()
    {
        return \Eclipse\Catalogue\Factories\MeasureUnitFactory::new();
    }
}
