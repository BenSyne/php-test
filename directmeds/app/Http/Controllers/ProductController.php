<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Manufacturer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a paginated listing of products with search and filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()
            ->with(['manufacturer', 'category'])
            ->active()
            ->available();

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function (Builder $q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('brand_name', 'like', "%{$searchTerm}%")
                  ->orWhere('generic_name', 'like', "%{$searchTerm}%")
                  ->orWhere('ndc_number', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // NDC specific search
        if ($request->filled('ndc')) {
            $query->searchByNdc($request->ndc);
        }

        // Category filtering
        if ($request->filled('category')) {
            $query->whereHas('category', function (Builder $q) use ($request) {
                $q->where('slug', $request->category)
                  ->orWhere('id', $request->category);
            });
        }

        // Manufacturer filtering
        if ($request->filled('manufacturer')) {
            $query->whereHas('manufacturer', function (Builder $q) use ($request) {
                $q->where('name', 'like', "%{$request->manufacturer}%")
                  ->orWhere('id', $request->manufacturer);
            });
        }

        // DEA Schedule filtering
        if ($request->filled('dea_schedule')) {
            $query->byDeaSchedule($request->dea_schedule);
        }

        // Prescription type filtering
        if ($request->filled('prescription_type')) {
            switch ($request->prescription_type) {
                case 'prescription':
                    $query->prescription();
                    break;
                case 'otc':
                    $query->otc();
                    break;
                case 'controlled':
                    $query->controlled();
                    break;
            }
        }

        // Stock status filtering
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'in_stock':
                    $query->inStock();
                    break;
                case 'low_stock':
                    $query->lowStock();
                    break;
                case 'out_of_stock':
                    $query->where('quantity_on_hand', '<=', 0);
                    break;
            }
        }

        // Price range filtering
        if ($request->filled('min_price')) {
            $query->where('retail_price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('retail_price', '<=', $request->max_price);
        }

        // Sorting
        $sortField = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');
        
        $allowedSortFields = [
            'name', 'brand_name', 'generic_name', 'retail_price', 
            'quantity_on_hand', 'created_at', 'updated_at'
        ];
        
        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('name', 'asc');
        }

        // Pagination
        $perPage = min($request->get('per_page', 15), 100); // Max 100 items per page
        $products = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $products,
            'filters' => [
                'categories' => Category::active()->ordered()->get(['id', 'name', 'slug']),
                'manufacturers' => Manufacturer::active()->ordered()->get(['id', 'name']),
                'dea_schedules' => ['CII', 'CIII', 'CIV', 'CV'],
                'prescription_types' => [
                    'prescription' => 'Prescription Only',
                    'otc' => 'Over-the-Counter',
                    'controlled' => 'Controlled Substances'
                ]
            ]
        ]);
    }

    /**
     * Display the specified product by slug or ID.
     */
    public function show(string $identifier): JsonResponse
    {
        $product = Product::with([
                'manufacturer', 
                'category', 
                'brandEquivalent', 
                'genericEquivalents'
            ])
            ->where(function (Builder $query) use ($identifier) {
                $query->where('slug', $identifier)
                      ->orWhere('id', $identifier)
                      ->orWhere('ndc_number', $identifier);
            })
            ->active()
            ->first();

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $product,
            'related_products' => $this->getRelatedProducts($product)
        ]);
    }

    /**
     * Search products by various criteria.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2',
            'type' => 'sometimes|in:name,ndc,category,manufacturer',
            'limit' => 'sometimes|integer|min:1|max:50'
        ]);

        $query = $request->query;
        $type = $request->get('type', 'name');
        $limit = $request->get('limit', 10);

        $results = Product::with(['manufacturer', 'category'])
            ->active()
            ->available();

        switch ($type) {
            case 'ndc':
                $results->searchByNdc($query);
                break;
            case 'category':
                $results->whereHas('category', function (Builder $q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('therapeutic_class', 'like', "%{$query}%");
                });
                break;
            case 'manufacturer':
                $results->whereHas('manufacturer', function (Builder $q) use ($query) {
                    $q->where('name', 'like', "%{$query}%");
                });
                break;
            default:
                $results->searchByName($query);
                break;
        }

        $products = $results->limit($limit)->get();

        return response()->json([
            'status' => 'success',
            'data' => $products,
            'query' => $query,
            'type' => $type,
            'count' => $products->count()
        ]);
    }

    /**
     * Get drug interaction information for a product.
     */
    public function interactions(string $identifier): JsonResponse
    {
        $product = Product::where(function (Builder $query) use ($identifier) {
                $query->where('slug', $identifier)
                      ->orWhere('id', $identifier)
                      ->orWhere('ndc_number', $identifier);
            })
            ->active()
            ->first();

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'product' => [
                    'id' => $product->id,
                    'name' => $product->getDisplayName(),
                    'ndc_number' => $product->ndc_number
                ],
                'interactions' => $product->drug_interactions ?? [],
                'warnings' => $product->warnings,
                'contraindications' => $product->contraindications,
                'side_effects' => $product->side_effects
            ]
        ]);
    }

    /**
     * Check for interactions between multiple drugs.
     */
    public function checkInteractions(Request $request): JsonResponse
    {
        $request->validate([
            'products' => 'required|array|min:2',
            'products.*' => 'exists:products,id'
        ]);

        $products = Product::whereIn('id', $request->products)
            ->active()
            ->get();

        $interactions = [];
        $productNames = $products->pluck('generic_name', 'id');

        foreach ($products as $product1) {
            foreach ($products as $product2) {
                if ($product1->id !== $product2->id) {
                    if ($product1->hasInteractionWith($product2->generic_name)) {
                        $interactions[] = [
                            'drug1' => [
                                'id' => $product1->id,
                                'name' => $product1->getDisplayName()
                            ],
                            'drug2' => [
                                'id' => $product2->id,
                                'name' => $product2->getDisplayName()
                            ],
                            'severity' => 'moderate', // This would come from a more sophisticated system
                            'description' => 'Potential drug interaction detected'
                        ];
                    }
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'has_interactions' => !empty($interactions),
                'interaction_count' => count($interactions),
                'interactions' => $interactions,
                'products_checked' => $productNames
            ]
        ]);
    }

    /**
     * Get products by category.
     */
    public function byCategory(string $categorySlug): JsonResponse
    {
        $category = Category::where('slug', $categorySlug)
            ->active()
            ->first();

        if (!$category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Category not found'
            ], 404);
        }

        $products = $category->getProductsIncludingDescendants()
            ->with(['manufacturer', 'category'])
            ->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $products,
            'category' => $category,
            'breadcrumb' => $category->breadcrumb
        ]);
    }

    /**
     * Get products by manufacturer.
     */
    public function byManufacturer(string $manufacturerId): JsonResponse
    {
        $manufacturer = Manufacturer::findOrFail($manufacturerId);

        $products = $manufacturer->activeProducts()
            ->with(['manufacturer', 'category'])
            ->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $products,
            'manufacturer' => $manufacturer
        ]);
    }

    /**
     * Get low stock products (for pharmacy management).
     */
    public function lowStock(): JsonResponse
    {
        $products = Product::with(['manufacturer', 'category'])
            ->active()
            ->lowStock()
            ->orderBy('quantity_on_hand', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $products,
            'count' => $products->count()
        ]);
    }

    /**
     * Get expiring products (for pharmacy management).
     */
    public function expiring(Request $request): JsonResponse
    {
        $days = $request->get('days', 30);
        
        $products = Product::with(['manufacturer', 'category'])
            ->active()
            ->expiringSoon($days)
            ->orderBy('expiration_date', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $products,
            'count' => $products->count(),
            'days' => $days
        ]);
    }

    /**
     * Helper method to get related products.
     */
    private function getRelatedProducts(Product $product): array
    {
        $related = [];

        // Same manufacturer products
        $related['same_manufacturer'] = Product::where('manufacturer_id', $product->manufacturer_id)
            ->where('id', '!=', $product->id)
            ->active()
            ->available()
            ->limit(5)
            ->get();

        // Same category products
        $related['same_category'] = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->active()
            ->available()
            ->limit(5)
            ->get();

        // Generic equivalents if this is a brand
        if (!$product->is_generic && $product->genericEquivalents()->exists()) {
            $related['generic_equivalents'] = $product->genericEquivalents()
                ->active()
                ->available()
                ->get();
        }

        // Brand equivalent if this is a generic
        if ($product->is_generic && $product->brandEquivalent) {
            $related['brand_equivalent'] = $product->brandEquivalent;
        }

        return $related;
    }
}
