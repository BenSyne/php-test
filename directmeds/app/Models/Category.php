<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'therapeutic_class',
        'atc_code',
        'parent_id',
        'sort_order',
        'is_active',
        'requires_prescription'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requires_prescription' => 'boolean'
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
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')
                    ->where('is_active', true)
                    ->orderBy('sort_order');
    }

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

    public function scopeRoot(Builder $query): void
    {
        $query->whereNull('parent_id');
    }

    public function scopeChildren(Builder $query): void
    {
        $query->whereNotNull('parent_id');
    }

    public function scopePrescription(Builder $query): void
    {
        $query->where('requires_prescription', true);
    }

    public function scopeOtc(Builder $query): void
    {
        $query->where('requires_prescription', false);
    }

    public function scopeByTherapeuticClass(Builder $query, string $class): void
    {
        $query->where('therapeutic_class', $class);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Computed Attributes
     */
    protected function isRoot(): Attribute
    {
        return Attribute::make(
            get: fn () => is_null($this->parent_id)
        );
    }

    protected function hasChildren(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->children()->count() > 0
        );
    }

    protected function level(): Attribute
    {
        return Attribute::make(
            get: function () {
                $level = 0;
                $parent = $this->parent;
                while ($parent) {
                    $level++;
                    $parent = $parent->parent;
                }
                return $level;
            }
        );
    }

    protected function breadcrumb(): Attribute
    {
        return Attribute::make(
            get: function () {
                $breadcrumb = [];
                $category = $this;
                
                while ($category) {
                    array_unshift($breadcrumb, $category->name);
                    $category = $category->parent;
                }
                
                return $breadcrumb;
            }
        );
    }

    protected function fullPath(): Attribute
    {
        return Attribute::make(
            get: fn () => implode(' > ', $this->breadcrumb)
        );
    }

    /**
     * Helper Methods
     */
    public function getAllDescendants(): array
    {
        $descendants = [];
        $this->loadMissing('children');
        
        foreach ($this->children as $child) {
            $descendants[] = $child;
            $descendants = array_merge($descendants, $child->getAllDescendants());
        }
        
        return $descendants;
    }

    public function getAllDescendantIds(): array
    {
        return array_map(fn($category) => $category->id, $this->getAllDescendants());
    }

    public function getProductsIncludingDescendants(): HasMany
    {
        $categoryIds = array_merge([$this->id], $this->getAllDescendantIds());
        
        return Product::whereIn('category_id', $categoryIds)
                     ->where('is_active', true)
                     ->where('is_available', true)
                     ->where('is_discontinued', false);
    }

    public function getPath(): array
    {
        $path = [];
        $category = $this;
        
        while ($category) {
            array_unshift($path, $category);
            $category = $category->parent;
        }
        
        return $path;
    }

    public function getTherapeuticClassOptions(): array
    {
        return [
            'Cardiovascular' => 'Cardiovascular System',
            'Respiratory' => 'Respiratory System',
            'CNS' => 'Central Nervous System',
            'GI' => 'Gastrointestinal System',
            'Endocrine' => 'Endocrine System',
            'Musculoskeletal' => 'Musculoskeletal System',
            'Dermatological' => 'Dermatological',
            'Ophthalmological' => 'Ophthalmological',
            'Anti-infective' => 'Anti-infective',
            'Oncology' => 'Oncology',
            'Hematology' => 'Hematology',
            'Immunology' => 'Immunology',
            'Nutrition' => 'Nutrition & Metabolism',
            'Genitourinary' => 'Genitourinary System',
            'Hormones' => 'Hormones & Related',
            'Vaccines' => 'Vaccines & Biologicals'
        ];
    }

    /**
     * Static Methods
     */
    public static function getRootCategories()
    {
        return static::root()
                    ->active()
                    ->ordered()
                    ->with(['children' => function ($query) {
                        $query->active()->ordered();
                    }])
                    ->get();
    }

    public static function buildHierarchy($categories = null, $parentId = null)
    {
        if ($categories === null) {
            $categories = static::active()->ordered()->get();
        }

        $branch = [];

        foreach ($categories as $category) {
            if ($category->parent_id == $parentId) {
                $children = static::buildHierarchy($categories, $category->id);
                if ($children) {
                    $category->children = $children;
                }
                $branch[] = $category;
            }
        }

        return $branch;
    }

    /**
     * Search functionality
     */
    public static function search(string $query): Builder
    {
        return static::query()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('therapeutic_class', 'like', "%{$query}%");
            })
            ->active();
    }
}
