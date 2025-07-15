<?php

namespace Eclipse\Catalogue\Models;

use Eclipse\Catalogue\Factories\CategoryFactory;
use Eclipse\Common\Foundation\Models\IsSearchable;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use SolutionForest\FilamentTree\Concern\ModelTree;
use Spatie\Translatable\HasTranslations;

class Category extends Model
{
    use HasFactory, HasTranslations, IsSearchable, ModelTree, SoftDeletes;

    protected $table = 'catalogue_categories';

    protected $fillable = [
        'name',
        'parent_id',
        'image',
        'sort',
        'is_active',
        'code',
        'recursive_browsing',
        'sef_key',
        'short_desc',
        'description',
        'site_id',
    ];

    public array $translatable = [
        'name',
        'sef_key',
        'short_desc',
        'description',
    ];

    public function determineOrderColumnName(): string
    {
        return 'sort';
    }

    public function determineTitleColumnName(): string
    {
        return 'name';
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public static function getHierarchicalOptions(): array
    {
        $options = static::selectArray(5);

        unset($options[static::defaultParentKey()]);

        foreach ($options as $key => $value) {
            if (str_starts_with($value, '---')) {
                $options[$key] = substr($value, 3);
            }
        }

        return $options;
    }

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'sef_key' => 'array',
            'short_desc' => 'array',
            'description' => 'array',
            'is_active' => 'boolean',
            'recursive_browsing' => 'boolean',
        ];
    }

    public function getFullPath(): string
    {
        $allNodes = static::allNodes()->keyBy('id');

        $path = [];
        $current = $this;

        while ($current) {
            $path[] = $current->name;
            $parentId = $current->{$this->determineParentColumnName()};

            if ($parentId && $parentId !== static::defaultParentKey() && isset($allNodes[$parentId])) {
                $current = $allNodes[$parentId];
            } else {
                $current = null;
            }
        }

        return implode(' > ', array_reverse($path));
    }

    protected static function newFactory(): CategoryFactory
    {
        return CategoryFactory::new();
    }

    protected static function booted(): void
    {
        static::creating(function (self $category): void {
            if (empty($category->site_id) && Filament::getTenant()) {
                $category->site_id = Filament::getTenant()->id;
            }
        });
    }

    public static function getTypesenseSettings(): array
    {
        return [
            'collection-schema' => [
                'fields' => [
                    [
                        'name' => 'id',
                        'type' => 'string',
                    ],
                    [
                        'name' => 'site_id',
                        'type' => 'string',
                    ],
                    [
                        'name' => 'parent_id',
                        'type' => 'string',
                        'optional' => true,
                    ],
                    [
                        'name' => 'code',
                        'type' => 'string',
                        'optional' => true,
                    ],
                    [
                        'name' => 'created_at',
                        'type' => 'int64',
                    ],
                    // Support both string and translation patterns
                    [
                        'name' => 'name',
                        'type' => 'string',
                        'optional' => true,
                    ],
                    [
                        'name' => 'name_.*', // For translations
                        'type' => 'string',
                        'optional' => true,
                    ],
                    [
                        'name' => 'sef_key_.*', // For translations
                        'type' => 'string',
                        'optional' => true,
                    ],
                    [
                        'name' => 'short_desc_.*', // For translations
                        'type' => 'string',
                        'optional' => true,
                    ],
                    [
                        'name' => 'description_.*', // For translations
                        'type' => 'string',
                        'optional' => true,
                    ],
                    [
                        'name' => 'is_active',
                        'type' => 'bool',
                    ],
                    [
                        'name' => 'sort',
                        'type' => 'int32',
                    ],
                    [
                        'name' => '__soft_deleted',
                        'type' => 'int32',
                        'optional' => true,
                    ],
                ],
            ],
            'search-parameters' => [
                'query_by' => implode(', ', [
                    'name_*',
                    'short_desc_*',
                    'description_*',
                    'code',
                    'sef_key_*',
                ]),
                'filter_by' => 'is_active:=true',
                'sort_by' => 'sort:asc',
            ],
        ];
    }
}
