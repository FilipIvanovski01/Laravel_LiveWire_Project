<?php

namespace App\Domain\ProductCatalog\Services;

use App\Domain\ProductCatalog\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MarketplaceSearchService
{
    /**
     * Search marketplace-visible products with optional filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function search(array $filters, int $perPage = 12): LengthAwarePaginator
    {
        $vendorId = isset($filters['vendor_id']) ? (string) $filters['vendor_id'] : null;
        $search = isset($filters['search']) ? (string) $filters['search'] : null;
        $minPrice = is_numeric($filters['min_price'] ?? null) ? (float) $filters['min_price'] : null;
        $maxPrice = is_numeric($filters['max_price'] ?? null) ? (float) $filters['max_price'] : null;

        return Product::query()
            ->with('vendor')
            ->marketplaceVisible()
            ->forVendor($vendorId)
            ->search($search)
            ->minPrice($minPrice)
            ->maxPrice($maxPrice)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
}
