<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Manufacturer extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'website_url',
        'contact_phone',
        'contact_email',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Relationships
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function activeProducts(): HasMany
    {
        return $this->products()
                    ->where('is_active', true)
                    ->where('is_available', true)
                    ->where('is_discontinued', false);
    }

    /**
     * Scopes
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeByCountry(Builder $query, string $country): void
    {
        $query->where('country', $country);
    }

    public function scopeByState(Builder $query, string $state): void
    {
        $query->where('state', $state);
    }

    public function scopeWithProducts(Builder $query): void
    {
        $query->whereHas('products');
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('name');
    }

    /**
     * Computed Attributes
     */
    protected function fullAddress(): Attribute
    {
        return Attribute::make(
            get: function () {
                $addressParts = array_filter([
                    $this->address,
                    $this->city,
                    $this->state,
                    $this->zip_code,
                    $this->country
                ]);
                
                return implode(', ', $addressParts);
            }
        );
    }

    protected function formattedPhone(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->contact_phone) {
                    return null;
                }
                
                // Format US phone numbers
                $phone = preg_replace('/[^0-9]/', '', $this->contact_phone);
                if (strlen($phone) === 10) {
                    return sprintf('(%s) %s-%s', 
                        substr($phone, 0, 3),
                        substr($phone, 3, 3),
                        substr($phone, 6, 4)
                    );
                }
                
                return $this->contact_phone;
            }
        );
    }

    protected function productCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->products()->count()
        );
    }

    protected function activeProductCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->activeProducts()->count()
        );
    }

    /**
     * Helper Methods
     */
    public function getContactInfo(): array
    {
        return [
            'phone' => $this->formatted_phone,
            'email' => $this->contact_email,
            'website' => $this->website_url,
            'address' => $this->full_address
        ];
    }

    public function hasActiveProducts(): bool
    {
        return $this->activeProducts()->exists();
    }

    public function getProductsByCategory(): array
    {
        return $this->activeProducts()
                    ->with('category')
                    ->get()
                    ->groupBy('category.name')
                    ->toArray();
    }

    public function getControlledSubstances(): HasMany
    {
        return $this->activeProducts()
                    ->where('is_controlled_substance', true);
    }

    public function getPrescriptionMedications(): HasMany
    {
        return $this->activeProducts()
                    ->where('requires_prescription', true);
    }

    public function getOtcMedications(): HasMany
    {
        return $this->activeProducts()
                    ->where('is_otc', true);
    }

    /**
     * Static Methods
     */
    public static function getActiveManufacturers()
    {
        return static::active()
                    ->withProducts()
                    ->ordered()
                    ->get();
    }

    public static function getMajorManufacturers()
    {
        return static::active()
                    ->withCount('products')
                    ->having('products_count', '>=', 10)
                    ->orderBy('products_count', 'desc')
                    ->get();
    }

    public static function getByCountry(string $country)
    {
        return static::active()
                    ->byCountry($country)
                    ->ordered()
                    ->get();
    }

    /**
     * Search functionality
     */
    public static function search(string $query): Builder
    {
        return static::query()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('code', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('city', 'like', "%{$query}%")
                  ->orWhere('state', 'like', "%{$query}%");
            })
            ->active();
    }

    /**
     * Validation Rules
     */
    public static function validationRules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:manufacturers,name',
            'code' => 'nullable|string|max:10|unique:manufacturers,code',
            'description' => 'nullable|string',
            'website_url' => 'nullable|url',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:10',
            'zip_code' => 'nullable|string|max:20',
            'country' => 'required|string|max:100',
            'is_active' => 'boolean'
        ];
    }
}
