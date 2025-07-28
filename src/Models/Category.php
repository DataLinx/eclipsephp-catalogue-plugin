<?php

namespace Eclipse\Catalogue\Models;

use Eclipse\Catalogue\Factories\CategoryFactory;
use Eclipse\Common\Foundation\Models\IsSearchable;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
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

    public function site(): BelongsTo
    {
        $tenantModel = config('eclipse-catalogue.tenancy.model');
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key', 'site_id');

        return $this->belongsTo($tenantModel, $tenantFK);
    }

    protected static function formatTreeName(string $value): array
    {
        if (! str_starts_with($value, '-')) {
            return ['name' => $value, 'level' => 0];
        }

        $dashCount = 0;
        while ($dashCount < strlen($value) && $value[$dashCount] === '-') {
            $dashCount++;
        }

        $level = intval($dashCount / 3);
        $cleanName = ltrim($value, '-');

        return ['name' => $cleanName, 'level' => $level];
    }

    protected static function getTreePrefix(int $level): string
    {
        $indent = str_repeat('. . . . ', $level);
        $connector = $level > 0 ? '└─ ' : '';

        return $indent.$connector;
    }

    public static function getHierarchicalOptions(): array
    {
        $options = static::selectArray(5);

        unset($options[static::defaultParentKey()]);

        foreach ($options as $key => $value) {
            $formatted = self::formatTreeName($value);
            $options[$key] = self::getTreePrefix($formatted['level']).$formatted['name'];
        }

        return $options;
    }

    public function getTreeFormattedName(): string
    {
        $selectArray = static::selectArray();
        $formattedName = $selectArray[$this->id] ?? $this->name;

        $formatted = self::formatTreeName($formattedName);

        return self::getTreePrefix($formatted['level']).e($formatted['name']);
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
        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantModel = config('eclipse-catalogue.tenancy.model');
            $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
            $tenant = Filament::getTenant();

            if ($tenantModel && $tenantFK && $tenant) {
                $builder->where($tenantFK, $tenant->id);
            }
        });

        static::creating(function (self $category): void {
            $tenantModel = config('eclipse-catalogue.tenancy.model');
            $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
            $tenant = Filament::getTenant();

            if ($tenantModel && $tenantFK && empty($category->{$tenantFK})) {
                if ($tenant) {
                    $category->{$tenantFK} = $tenant->id;
                } else {
                    $siteModel = app($tenantModel);
                    $firstSite = $siteModel::first();
                    if ($firstSite) {
                        $category->{$tenantFK} = $firstSite->id;
                    }
                }
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
