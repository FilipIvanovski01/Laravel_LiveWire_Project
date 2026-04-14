<?php

namespace App\Domain\ProductCatalog\Models;

use App\Domain\ProductCatalog\Enums\ProductStatus;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    private const DEFAULT_IMAGE_PLACEHOLDER = 'https://placehold.co/640x480/F8F9FA/6C757D?text=No+Image';

    /** @use HasFactory<ProductFactory> */
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'vendor_id',
        'name',
        'description',
        'price',
        'stock_quantity',
        'image_url',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'status' => ProductStatus::class,
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', ProductStatus::Active);
    }

    public function scopeForVendor(Builder $query, ?string $vendorId): Builder
    {
        if (! filled($vendorId)) {
            return $query;
        }

        return $query->where('vendor_id', $vendorId);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! filled($term)) {
            return $query;
        }

        $search = trim($term);

        return $query->where(function (Builder $builder) use ($search): void {
            $builder->where('name', 'like', '%'.$search.'%');
        });
    }

    public function scopeMinPrice(Builder $query, ?float $minPrice): Builder
    {
        if ($minPrice === null) {
            return $query;
        }

        return $query->where('price', '>=', $minPrice);
    }

    public function scopeMaxPrice(Builder $query, ?float $maxPrice): Builder
    {
        if ($maxPrice === null) {
            return $query;
        }

        return $query->where('price', '<=', $maxPrice);
    }

    public function scopeMarketplaceVisible(Builder $query): Builder
    {
        return $query
            ->active()
            ->whereHas('vendor', fn (Builder $vendorQuery): Builder => $vendorQuery->where('is_active', true));
    }

    public function getImageUrlForDisplayAttribute(): string
    {
        if (! filled($this->image_url)) {
            return self::DEFAULT_IMAGE_PLACEHOLDER;
        }

        if (str_starts_with($this->image_url, 'http://') || str_starts_with($this->image_url, 'https://')) {
            return $this->image_url;
        }

        if (! Storage::disk('public')->exists($this->image_url)) {
            return self::DEFAULT_IMAGE_PLACEHOLDER;
        }

        return Storage::url($this->image_url);
    }

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }
}
