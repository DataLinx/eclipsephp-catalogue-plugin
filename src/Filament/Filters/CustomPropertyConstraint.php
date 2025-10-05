<?php

namespace Eclipse\Catalogue\Filament\Filters;

use Eclipse\Catalogue\Filament\Filters\Operators\CustomPropertyContainsOperator;
use Eclipse\Catalogue\Filament\Filters\Operators\CustomPropertyEndsWithOperator;
use Eclipse\Catalogue\Filament\Filters\Operators\CustomPropertyEqualsOperator;
use Eclipse\Catalogue\Filament\Filters\Operators\CustomPropertyStartsWithOperator;
use Eclipse\Catalogue\Models\Property;
use Filament\Tables\Filters\QueryBuilder\Constraints\Constraint;

class CustomPropertyConstraint extends Constraint
{
    protected Property $property;

    /**
     * Create a new custom property constraint for a given property.
     *
     * @param  Property  $property  The property to create the constraint for.
     * @return static The created constraint.
     */
    public static function forProperty(Property $property): static
    {
        $static = parent::make("custom_property_{$property->id}");
        $static->property = $property;
        $label = $property->internal_name ?: (is_array($property->name)
            ? ($property->name[app()->getLocale()] ?? reset($property->name))
            : $property->name);
        $static->label($label);

        return $static;
    }

    /**
     * Set up the constraint.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->operators([
            CustomPropertyEqualsOperator::make(),
            CustomPropertyContainsOperator::make(),
            CustomPropertyStartsWithOperator::make(),
            CustomPropertyEndsWithOperator::make(),
        ]);
    }
}
