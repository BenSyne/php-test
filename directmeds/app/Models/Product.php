<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'brand_name',
        'generic_name',
        'ndc_number',
        'upc_code',
        'lot_number',
        'dea_schedule',
        'is_controlled_substance',
        'requires_prescription',
        'is_otc',
        'dosage_form',
        'strength',
        'route_of_administration',
        'package_size',
        'package_type',
        'manufacturer_id',
        'category_id',
        'cost_price',
        'retail_price',
        'insurance_price',
        'quantity_on_hand',
        'minimum_stock_level',
        'maximum_stock_level',
        'expiration_date',
        'storage_requirements',
        'storage_temperature_min',
        'storage_temperature_max',
        'active_ingredients',
        'inactive_ingredients',
        'warnings',
        'side_effects',
        'contraindications',
        'drug_interactions',
        'dosage_instructions',
        'fda_approval_number',
        'fda_approval_date',
        'therapeutic_equivalence_code',
        'is_generic',
        'brand_equivalent_id',
        'is_active',
        'is_available',
        'is_discontinued',
        'discontinuation_date',
        'discontinuation_reason',
        'meta_title',
        'meta_description',
        'image_url',
        'images'
    ];

    protected $casts = [
        'is_controlled_substance' => 'boolean',
        'requires_prescription' => 'boolean',
        'is_otc' => 'boolean',
        'cost_price' => 'decimal:4',
        'retail_price' => 'decimal:2',
        'insurance_price' => 'decimal:2',
        'storage_temperature_min' => 'decimal:2',
        'storage_temperature_max' => 'decimal:2',
        'expiration_date' => 'date',
        'fda_approval_date' => 'date',
        'is_generic' => 'boolean',
        'is_active' => 'boolean',
        'is_available' => 'boolean',
        'is_discontinued' => 'boolean',
        'discontinuation_date' => 'date',
        'images' => 'array',
        'drug_interactions' => 'array',
        'active_ingredients' => 'array',
        'inactive_ingredients' => 'array'
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Relationships
     */
    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brandEquivalent(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'brand_equivalent_id');
    }

    public function genericEquivalents(): HasMany
    {
        return $this->hasMany(Product::class, 'brand_equivalent_id');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scopes
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeAvailable(Builder $query): void
    {
        $query->where('is_available', true)
              ->where('is_discontinued', false);
    }

    public function scopeInStock(Builder $query): void
    {
        $query->where('quantity_on_hand', '>', 0);
    }

    public function scopePrescription(Builder $query): void
    {
        $query->where('requires_prescription', true);
    }

    public function scopeOtc(Builder $query): void
    {
        $query->where('is_otc', true);
    }

    public function scopeControlled(Builder $query): void
    {
        $query->where('is_controlled_substance', true);
    }

    public function scopeByDeaSchedule(Builder $query, string $schedule): void
    {
        $query->where('dea_schedule', $schedule);
    }

    public function scopeSearchByName(Builder $query, string $search): void
    {
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('brand_name', 'like', "%{$search}%")
              ->orWhere('generic_name', 'like', "%{$search}%");
        });
    }

    public function scopeSearchByNdc(Builder $query, string $ndc): void
    {
        $query->where('ndc_number', 'like', "%{$ndc}%");
    }

    public function scopeExpiringSoon(Builder $query, int $days = 30): void
    {
        $query->whereDate('expiration_date', '<=', now()->addDays($days));
    }

    public function scopeLowStock(Builder $query): void
    {
        $query->whereRaw('quantity_on_hand <= minimum_stock_level');
    }

    /**
     * Computed Attributes
     */
    protected function isExpired(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->expiration_date && $this->expiration_date->isPast()
        );
    }

    protected function isExpiringSoon(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->expiration_date && $this->expiration_date->diffInDays(now()) <= 30
        );
    }

    protected function isLowStock(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->quantity_on_hand <= $this->minimum_stock_level
        );
    }

    protected function stockStatus(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->quantity_on_hand <= 0) return 'out_of_stock';
                if ($this->is_low_stock) return 'low_stock';
                return 'in_stock';
            }
        );
    }

    protected function deaScheduleDescription(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match($this->dea_schedule) {
                    'CI' => 'Schedule I - No accepted medical use',
                    'CII' => 'Schedule II - High potential for abuse',
                    'CIII' => 'Schedule III - Moderate potential for abuse',
                    'CIV' => 'Schedule IV - Low potential for abuse',
                    'CV' => 'Schedule V - Lowest potential for abuse',
                    default => null
                };
            }
        );
    }

    protected function formattedPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => '$' . number_format($this->retail_price, 2)
        );
    }

    /**
     * Helper Methods
     */
    public function hasInteractionWith(string $drugName): bool
    {
        if (!$this->drug_interactions) {
            return false;
        }

        $interactions = is_array($this->drug_interactions) 
            ? $this->drug_interactions 
            : json_decode($this->drug_interactions, true);

        return in_array(strtolower($drugName), array_map('strtolower', $interactions ?? []));
    }

    public function getActiveIngredientsList(): array
    {
        if (!$this->active_ingredients) {
            return [];
        }

        return is_array($this->active_ingredients) 
            ? $this->active_ingredients 
            : json_decode($this->active_ingredients, true) ?? [];
    }

    public function needsReorder(): bool
    {
        return $this->quantity_on_hand <= $this->minimum_stock_level;
    }

    public function canDispense(int $quantity = 1): bool
    {
        return $this->is_active 
            && $this->is_available 
            && !$this->is_discontinued 
            && !$this->is_expired
            && $this->quantity_on_hand >= $quantity;
    }

    public function getDisplayName(): string
    {
        return $this->brand_name ?: $this->generic_name ?: $this->name;
    }

    /**
     * Search functionality
     */
    public static function search(string $query): Builder
    {
        return static::query()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('brand_name', 'like', "%{$query}%")
                  ->orWhere('generic_name', 'like', "%{$query}%")
                  ->orWhere('ndc_number', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->active()
            ->available();
    }
}
