<?php

namespace Eclipse\Catalogue\Models;

use Eclipse\Catalogue\Factories\PriceListFactory;
use Eclipse\World\Models\Currency;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PriceList extends Model
{
    use HasFactory, SoftDeletes;

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
     * Get the per-tenant data for the current Filament tenant (if any).
     * Falls back to the first data row when tenancy is disabled.
     */
    public function currentTenantData(): ?PriceListData
    {
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
        $tenantId = Filament::getTenant()?->id;

        if ($tenantFK && $tenantId) {
            return $this->priceListData()->where($tenantFK, $tenantId)->first();
        }

        return $this->priceListData()->first();
    }

    /**
     * Get per-tenant data for a specific tenant ID.
     * If no tenantId provided, the current Filament tenant is used.
     */
    public function getTenantData(?int $tenantId = null): ?PriceListData
    {
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
        $targetTenantId = $tenantId ?: Filament::getTenant()?->id;

        if ($tenantFK && $targetTenantId) {
            return $this->priceListData()->where($tenantFK, $targetTenantId)->first();
        }

        return $this->priceListData()->first();
    }

    /**
     * Accessor for the is_active attribute.
     * Reads from current tenant data; defaults to true when missing.
     */
    public function getIsActiveAttribute(): bool
    {
        if (isset($this->attributes['is_active'])) {
            return $this->attributes['is_active'];
        }

        $tenantData = $this->currentTenantData();

        return $tenantData ? $tenantData->is_active : true;
    }

    /**
     * Accessor for the is_default attribute.
     * Reads from current tenant data; defaults to false when missing.
     */
    public function getIsDefaultAttribute(): bool
    {
        if (isset($this->attributes['is_default'])) {
            return $this->attributes['is_default'];
        }

        $tenantData = $this->currentTenantData();

        return $tenantData ? $tenantData->is_default : false;
    }

    /**
     * Accessor for the is_default_purchase attribute.
     * Reads from current tenant data; defaults to false when missing.
     */
    public function getIsDefaultPurchaseAttribute(): bool
    {
        if (isset($this->attributes['is_default_purchase'])) {
            return $this->attributes['is_default_purchase'];
        }

        $tenantData = $this->currentTenantData();

        return $tenantData ? $tenantData->is_default_purchase : false;
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
     * Create a new price list together with its per-tenant settings.
     */
    public static function createWithTenantData(array $priceListData, array $tenantData = []): self
    {
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');

        // Create the main price list record
        $priceList = static::create($priceListData);

        if (! $tenantFK) {
            // No tenancy: create a single data row with provided or default flags
            $singleTenantData = $tenantData ?: [
                'is_active' => true,
                'is_default' => false,
                'is_default_purchase' => false,
            ];

            // Enforce invariants (cannot be both defaults, clear other defaults)
            $priceList->handleDefaultConstraints($singleTenantData, null);

            PriceListData::create([
                'price_list_id' => $priceList->id,
                ...$singleTenantData,
            ]);

            return $priceList;
        }

        // Tenancy enabled: create a data row for each tenant
        $tenantModel = config('eclipse-catalogue.tenancy.model');
        $tenants = $tenantModel::all();

        foreach ($tenants as $tenant) {
            $tenantId = $tenant->id;

            // Use provided data if available; otherwise apply safe defaults
            $tenantSpecificData = $tenantData[$tenantId] ?? [
                'is_active' => true,
                'is_default' => false,
                'is_default_purchase' => false,
            ];

            // Enforce invariants for this tenant
            $priceList->handleDefaultConstraints($tenantSpecificData, $tenantId);

            PriceListData::create([
                'price_list_id' => $priceList->id,
                $tenantFK => $tenantId,
                'is_active' => $tenantSpecificData['is_active'] ?? true,
                'is_default' => $tenantSpecificData['is_default'] ?? false,
                'is_default_purchase' => $tenantSpecificData['is_default_purchase'] ?? false,
            ]);
        }

        return $priceList;
    }

    /**
     * Update the base price list row and its per-tenant settings.
     */
    public function updateWithTenantData(array $priceListData = [], array $tenantData = []): self
    {
        // Update main record if data provided
        if (! empty($priceListData)) {
            $this->update($priceListData);
        }

        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');

        if (! $tenantFK) {
            // No tenancy: upsert a single data row
            if (! empty($tenantData)) {
                $this->handleDefaultConstraints($tenantData, null);

                PriceListData::updateOrCreate(
                    ['price_list_id' => $this->id],
                    $tenantData
                );
            }

            return $this;
        }

        // Tenancy enabled: update/create records for all tenants
        $tenantModel = config('eclipse-catalogue.tenancy.model');
        $tenants = $tenantModel::all();

        foreach ($tenants as $tenant) {
            $tenantId = $tenant->id;
            $tenantSpecificData = $tenantData[$tenantId] ?? [
                'is_active' => true,
                'is_default' => false,
                'is_default_purchase' => false,
            ];

            $this->handleDefaultConstraints($tenantSpecificData, $tenantId);

            PriceListData::updateOrCreate(
                [
                    'price_list_id' => $this->id,
                    $tenantFK => $tenantId,
                ],
                [
                    'is_active' => $tenantSpecificData['is_active'] ?? true,
                    'is_default' => $tenantSpecificData['is_default'] ?? false,
                    'is_default_purchase' => $tenantSpecificData['is_default_purchase'] ?? false,
                ]
            );
        }

        return $this;
    }

    /**
     * Enforce simple rules for defaults and keep data consistent.
     */
    public function handleDefaultConstraints(array &$tenantData, ?int $tenantId): void
    {
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');

        // Validate that a price list cannot be both default selling and purchase
        if (($tenantData['is_default'] ?? false) && ($tenantData['is_default_purchase'] ?? false)) {
            $errorKey = $tenantId ? "tenant_data.{$tenantId}" : '';
            throw \Illuminate\Validation\ValidationException::withMessages([
                $errorKey.'.is_default' => 'A price list cannot be both default selling and default purchase.',
                $errorKey.'.is_default_purchase' => 'A price list cannot be both default selling and default purchase.',
            ]);
        }

        // If setting as default selling, unset other defaults for this tenant
        if ($tenantData['is_default'] ?? false) {
            $query = PriceListData::where('is_default', true);

            // Exclude current record if it exists (for updates)
            if ($this->exists) {
                $query->where('price_list_id', '!=', $this->id);
            }

            if ($tenantFK && $tenantId) {
                $query->where($tenantFK, $tenantId);
            }

            $query->update(['is_default' => false]);
        }

        // If setting as default purchase, unset other defaults for this tenant
        if ($tenantData['is_default_purchase'] ?? false) {
            $query = PriceListData::where('is_default_purchase', true);

            // Exclude current record if it exists (for updates)
            if ($this->exists) {
                $query->where('price_list_id', '!=', $this->id);
            }

            if ($tenantFK && $tenantId) {
                $query->where($tenantFK, $tenantId);
            }

            $query->update(['is_default_purchase' => false]);
        }
    }

    /**
     * Validate tenant data constraints before saving.
     */
    public static function validateTenantDataConstraints(array $tenantData): void
    {
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');

        if (! $tenantFK) {
            // No tenancy - validate simple fields
            if (($tenantData['is_default'] ?? false) && ($tenantData['is_default_purchase'] ?? false)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'is_default' => __('eclipse-catalogue::price-list.validation.cannot_be_both_defaults'),
                    'is_default_purchase' => __('eclipse-catalogue::price-list.validation.cannot_be_both_defaults'),
                ]);
            }

            return;
        }

        // Validate tenant data
        $errors = [];
        $firstErrorTenantId = null;

        foreach ($tenantData as $tenantId => $tenantSpecificData) {
            if (
                ($tenantSpecificData['is_default'] ?? false) &&
                ($tenantSpecificData['is_default_purchase'] ?? false)
            ) {
                $tenantModel = config('eclipse-catalogue.tenancy.model');
                $tenant = $tenantModel::find($tenantId);
                $tenantName = $tenant ? $tenant->name : "Tenant {$tenantId}";

                if (! $firstErrorTenantId) {
                    $firstErrorTenantId = $tenantId;
                }

                $errors["tenant_data.{$tenantId}.is_default"] = __('eclipse-catalogue::price-list.validation.cannot_be_both_defaults_tenant', ['tenant' => $tenantName]);
                $errors["tenant_data.{$tenantId}.is_default_purchase"] = __('eclipse-catalogue::price-list.validation.cannot_be_both_defaults_tenant', ['tenant' => $tenantName]);
            }
        }

        if (! empty($errors)) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }
    }

    /**
     * Create a new factory instance for the model
     */
    protected static function newFactory(): PriceListFactory
    {
        return PriceListFactory::new();
    }
}
