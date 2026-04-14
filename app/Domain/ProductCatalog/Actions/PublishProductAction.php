<?php

namespace App\Domain\ProductCatalog\Actions;

use App\Domain\ProductCatalog\Enums\ProductStatus;
use App\Domain\ProductCatalog\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class PublishProductAction
{
    public function execute(User $user, Product $product): Product
    {
        Gate::forUser($user)->authorize('update', $product);

        $product->update([
            'status' => ProductStatus::Active,
        ]);

        return $product->fresh();
    }
}
