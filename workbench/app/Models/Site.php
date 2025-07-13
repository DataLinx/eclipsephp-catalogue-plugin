<?php

namespace Workbench\App\Models;

use Eclipse\Catalogue\Models\Category;
use Eclipse\Core\Models\User\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Workbench\Database\Factories\SiteFactory;

class Site extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['domain', 'name', 'is_active', 'is_secure'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'is_active' => 'boolean',
            'is_secure' => 'boolean',
        ];
    }

    protected static function newFactory(): SiteFactory
    {
        return SiteFactory::new();
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /** @return HasMany<Role, self> */
    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }
}
