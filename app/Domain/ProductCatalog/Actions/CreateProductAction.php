<?php

namespace App\Domain\ProductCatalog\Actions;

use App\Domain\ProductCatalog\DTOs\CreateProductDTO;
use App\Domain\ProductCatalog\Models\Product;
use App\Domain\ProductCatalog\Models\Vendor;

class CreateProductAction
{
    public function execute(Vendor $vendor, CreateProductDTO $data): Product
    {
        return Product::query()->create([
            'vendor_id' => $vendor->id,
            'name' => $data->name,
            'description' => $data->description,
            'price' => $data->price,
            'stock_quantity' => $data->stockQuantity,
            'image_url' => $data->imageUrl,
            'status' => $data->status,
        ]);
    }
}
