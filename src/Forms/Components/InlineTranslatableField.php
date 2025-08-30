<?php

namespace Eclipse\Catalogue\Forms\Components;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class InlineTranslatableField
{
    protected string $name;

    protected string $label;

    protected string $type = 'string';

    protected array $rules = [];

    protected ?string $helperText = null;

    protected bool $required = false;

    protected ?int $maxLength = null;

    protected bool $multiple = false;

    protected int $maxFiles = 1;

    public static function make(string $name): static
    {
        $instance = new static;
        $instance->name = $name;

        return $instance;
    }

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function type(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function rules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function helperText(?string $helperText): static
    {
        $this->helperText = $helperText;

        return $this;
    }

    public function required(bool $required = true): static
    {
        $this->required = $required;

        return $this;
    }

    public function maxLength(?int $maxLength): static
    {
        $this->maxLength = $maxLength;

        return $this;
    }

    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;

        return $this;
    }

    public function maxFiles(int $maxFiles): static
    {
        $this->maxFiles = $maxFiles;

        return $this;
    }

    public function getComponent(): Component
    {
        return $this->createTabsLayout();
    }

    protected function createTabsLayout(): Tabs
    {
        $locales = $this->getAvailableLocales();
        $tabs = [];

        foreach ($locales as $locale) {
            $fieldName = "{$this->name}.{$locale}";
            $localePrefix = strtoupper($locale);

            $component = match ($this->type) {
                'textarea', 'text' => $this->createTextareaComponent($fieldName, $localePrefix),
                'file' => $this->createFileComponent($fieldName, $localePrefix),
                'string' => $this->createTextInputComponent($fieldName, $localePrefix),
                default => $this->createTextInputComponent($fieldName, $localePrefix),
            };

            $tabs[] = Tab::make($localePrefix)
                ->label($localePrefix)
                ->schema([$component]);
        }

        return Tabs::make($this->label)
            ->tabs($tabs)
            ->columnSpanFull();
    }

    protected function createTextInputComponent(string $fieldName, string $localePrefix): TextInput
    {
        $component = TextInput::make($fieldName)
            ->label($this->label)
            ->prefix($localePrefix)
            ->required($this->required);

        if ($this->maxLength) {
            $component->maxLength($this->maxLength);
        }

        if ($this->helperText) {
            $component->helperText($this->helperText);
        }

        if (! empty($this->rules)) {
            $component->rules($this->rules);
        }

        return $component;
    }

    protected function createTextareaComponent(string $fieldName, string $localePrefix): Textarea
    {
        $component = Textarea::make($fieldName)
            ->label($localePrefix.': '.$this->label)
            ->required($this->required)
            ->rows(3);

        if ($this->maxLength) {
            $component->maxLength($this->maxLength);
        }

        if ($this->helperText) {
            $component->helperText($this->helperText);
        }

        if (! empty($this->rules)) {
            $component->rules($this->rules);
        }

        return $component;
    }

    protected function createFileComponent(string $fieldName, string $localePrefix): FileUpload
    {
        $component = FileUpload::make($fieldName)
            ->label($this->label.' ('.$localePrefix.')')
            ->required($this->required);

        if ($this->multiple) {
            $component->multiple()->maxFiles($this->maxFiles);
        }

        if ($this->helperText) {
            $component->helperText($this->helperText);
        }

        if (! empty($this->rules)) {
            $component->rules($this->rules);
        }

        return $component;
    }

    /**
     * Get available locales for the application.
     */
    protected function getAvailableLocales(): array
    {
        if (class_exists(\Eclipse\Core\Models\Locale::class)) {
            return \Eclipse\Core\Models\Locale::getAvailableLocales()->pluck('id')->toArray();
        }

        return ['en'];
    }
}
