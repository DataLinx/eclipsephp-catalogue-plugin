<?php

namespace Eclipse\Catalogue\Models;

use Eclipse\Catalogue\Factories\PriceListFactory;
use Eclipse\Catalogue\Traits\HasTenantScopedData;
use Eclipse\World\Models\Currency;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PriceList extends Model
{
    use HasFactory, HasTenantScopedData, SoftDeletes;

    protected $table = 'pim_price_lists';

    protected $fillable = [
        'currency_id',
        'name',
        'code',
        'tax_included',
        'notes',
    ];

    protected $appends = [
        'is_active',
        'is_default',
        'is_default_purchase',
    ];

    protected static string $tenantDataRelation = 'priceListData';

    protected static string $tenantDataModel = PriceListData::class;

    protected static array $tenantFlags = ['is_active', 'is_default', 'is_default_purchase'];

    protected static array $mutuallyExclusiveFlagSets = [['is_default', 'is_default_purchase']];

    protected static array $uniqueFlagsPerTenant = ['is_default', 'is_default_purchase'];

    /**
     * Get the currency for the price list.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Get all per-tenant data rows for this price list.
     */
    public function priceListData(): HasMany
    {
        return $this->hasMany(PriceListData::class);
    }

    /**
     * Accessor for the is_active attribute.
     * Reads from current tenant data; defaults to true when missing.
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->getTenantFlagValue('is_active');
    }

    /**
     * Accessor for the is_default attribute.
     * Reads from current tenant data; defaults to false when missing.
     */
    public function getIsDefaultAttribute(): bool
    {
        return $this->getTenantFlagValue('is_default');
    }

    /**
     * Accessor for the is_default_purchase attribute.
     * Reads from current tenant data; defaults to false when missing.
     */
    public function getIsDefaultPurchaseAttribute(): bool
    {
        return $this->getTenantFlagValue('is_default_purchase');
    }

    /**
     * Find the default selling price list for a tenant.
     * If tenantId is omitted, the current Filament tenant is used.
     */
    public static function getDefaultSelling(?int $tenantId = null): ?self
    {
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
        $currentTenantId = $tenantId ?: Filament::getTenant()?->id;

        $query = static::whereHas('priceListData', function ($q) use ($tenantFK, $currentTenantId) {
            $q->where('is_default', true);
            if ($tenantFK && $currentTenantId) {
                $q->where($tenantFK, $currentTenantId);
            }
        });

        return $query->first();
    }

    /**
     * Find the default purchase price list for a tenant.
     * If tenantId is omitted, the current Filament tenant is used.
     */
    public static function getDefaultPurchase(?int $tenantId = null): ?self
    {
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
        $currentTenantId = $tenantId ?: Filament::getTenant()?->id;

        $query = static::whereHas('priceListData', function ($q) use ($tenantFK, $currentTenantId) {
            $q->where('is_default_purchase', true);
            if ($tenantFK && $currentTenantId) {
                $q->where($tenantFK, $currentTenantId);
            }
        });

        return $query->first();
    }

    protected function casts(): array
    {
        return [
            'tax_included' => 'boolean',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'is_default_purchase' => 'boolean',
        ];
    }

    /**
     * Create a new factory instance for the model
     */
    protected static function newFactory(): PriceListFactory
    {
        return PriceListFactory::new();
    }
}
