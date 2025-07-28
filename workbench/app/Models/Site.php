<?php

namespace Workbench\App\Models;

use Eclipse\Catalogue\Models\Category;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Workbench\Database\Factories\SiteFactory;

class Site extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'domain',
    ];

    protected static function newFactory(): SiteFactory
    {
        return SiteFactory::new();
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }
}
