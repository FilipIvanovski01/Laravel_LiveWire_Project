<?php

namespace App\Domain\ProductCatalog\Actions;

use App\Domain\ProductCatalog\DTOs\UpdateProductDTO;
use App\Domain\ProductCatalog\Models\Product;

class UpdateProductAction
{
    public function execute(Product $product, UpdateProductDTO $data): Product
    {
        $product->update([
            'name' => $data->name,
            'description' => $data->description,
            'price' => $data->price,
            'stock_quantity' => $data->stockQuantity,
            'image_url' => $data->imageUrl,
            'status' => $data->status,
        ]);

        return $product->fresh();
    }
}
