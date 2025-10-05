<?php

namespace Eclipse\Catalogue\Values;

use Eclipse\Catalogue\Enums\BackgroundType;
use Eclipse\Catalogue\Enums\GradientDirection;
use Eclipse\Catalogue\Enums\GradientStyle;
use Stringable;

/**
 * Background value object for representing color and gradient styles.
 * Provides a compact serialization format and can render to CSS when cast to string.
 */
class Background implements Stringable
{
    public string $type = BackgroundType::NONE->value;

    public ?string $color = null;

    public ?string $color_start = null;

    public ?string $color_end = null;

    public ?string $gradient_direction = null;

    public ?string $gradient_style = null;

    /**
     * Create a new background value object with no type.
     */
    public static function none(): self
    {
        return new self;
    }

    /**
     * Create a new background value object with a solid color.
     *
     * @param  string  $hex  The hex color code.
     */
    public static function solid(string $hex): self
    {
        $bg = new self;
        $bg->type = BackgroundType::SOLID->value;
        $bg->color = $hex;

        return $bg;
    }

    /**
     * Create a new background value object with a gradient.
     *
     * @param  string  $start  The start color code.
     * @param  string  $end  The end color code.
     * @param  string  $direction  The gradient direction.
     * @param  string  $style  The gradient style.
     */
    public static function gradient(string $start, string $end, string $direction = GradientDirection::BOTTOM->value, string $style = GradientStyle::SHARP->value): self
    {
        $bg = new self;
        $bg->type = BackgroundType::GRADIENT->value;
        $bg->color_start = $start;
        $bg->color_end = $end;
        $bg->gradient_direction = $direction;
        $bg->gradient_style = $style;

        return $bg;
    }

    /**
     * Create a new background value object with multiple colors.
     */
    public static function multicolor(): self
    {
        $bg = new self;
        $bg->type = BackgroundType::MULTICOLOR->value;

        return $bg;
    }

    /**
     * Create a new background value object from form state.
     *
     * @param  array  $state  The form state.
     */
    public static function fromForm(array $state): self
    {
        $bg = new self;
        $bg->type = $state['type'] ?? BackgroundType::NONE->value;
        $bg->color = $state['color'] ?? null;
        $bg->color_start = $state['color_start'] ?? null;
        $bg->color_end = $state['color_end'] ?? null;
        $bg->gradient_direction = $state['gradient_direction'] ?? null;
        $bg->gradient_style = $state['gradient_style'] ?? null;

        return $bg;
    }

    /**
     * Convert the background value object to form state.
     */
    public function toForm(): array
    {
        return [
            'type' => $this->type,
            'color' => $this->color,
            'color_start' => $this->color_start,
            'color_end' => $this->color_end,
            'gradient_direction' => $this->gradient_direction,
            'gradient_style' => $this->gradient_style,
        ];
    }

    /**
     * Convert the background value object to CSS.
     */
    public function toCss(): string
    {
        return (string) $this;
    }

    /**
     * Check if the background value object has renderable CSS.
     */
    public function hasRenderableCss(): bool
    {
        if ($this->type === BackgroundType::SOLID->value) {
            return ! empty($this->color);
        }
        if ($this->type === BackgroundType::GRADIENT->value) {
            return ! empty($this->color_start) && ! empty($this->color_end);
        }

        return $this->type === BackgroundType::MULTICOLOR->value;
    }

    /**
     * Convert the background value object to a string.
     */
    public function __toString(): string
    {
        return match ($this->type) {
            BackgroundType::SOLID->value => $this->color ? "background-color: {$this->color};" : '',
            BackgroundType::GRADIENT->value => $this->renderGradient(),
            default => '',
        };
    }

    /**
     * Render the gradient CSS.
     */
    private function renderGradient(): string
    {
        $direction = $this->gradient_direction ?: GradientDirection::BOTTOM->value;
        $start = $this->color_start ?: '#000000';
        $end = $this->color_end ?: '#000000';

        if ($this->gradient_style === GradientStyle::SOFT->value) {
            return "background-image: linear-gradient(to {$direction}, {$start} 0%, {$end} 100%);";
        }

        return "background-image: linear-gradient(to {$direction}, {$start}, {$start} 50%, {$end} 50%, {$end});";
    }

    /**
     * Check if the background value object is a solid color.
     */
    public function isSolid(): bool
    {
        return $this->type === BackgroundType::SOLID->value;
    }

    /**
     * Check if the background value object is a gradient.
     */
    public function isGradient(): bool
    {
        return $this->type === BackgroundType::GRADIENT->value;
    }

    /**
     * Check if the background value object is a multicolor.
     */
    public function isMulticolor(): bool
    {
        return $this->type === BackgroundType::MULTICOLOR->value;
    }

    /**
     * Convert the background value object to an array.
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'color' => $this->color,
            'color_start' => $this->color_start,
            'color_end' => $this->color_end,
            'gradient_direction' => $this->gradient_direction,
            'gradient_style' => $this->gradient_style,
        ];
    }

    /**
     * Create a new background value object from an array.
     *
     * @param  array|null  $data  The array data.
     */
    public static function fromArray(?array $data): self
    {
        $bg = new self;
        if (! $data) {
            return $bg;
        }
        $bg->type = $data['type'] ?? BackgroundType::NONE->value;
        $bg->color = $data['color'] ?? null;
        $bg->color_start = $data['color_start'] ?? null;
        $bg->color_end = $data['color_end'] ?? null;
        $bg->gradient_direction = $data['gradient_direction'] ?? null;
        $bg->gradient_style = $data['gradient_style'] ?? null;

        return $bg;
    }
}
