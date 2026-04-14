<?php

namespace App\Domain\ProductCatalog\Policies;

use App\Domain\ProductCatalog\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->vendor !== null;
    }

    public function view(User $user, Product $product): bool
    {
        return $user->vendor !== null
            && $product->vendor_id === $user->vendor->id;
    }

    public function create(User $user): bool
    {
        return $user->vendor !== null;
    }

    public function update(User $user, Product $product): bool
    {
        return $user->vendor !== null
            && $product->vendor_id === $user->vendor->id;
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->update($user, $product);
    }
}
