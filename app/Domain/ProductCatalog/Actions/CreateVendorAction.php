<?php

namespace App\Domain\ProductCatalog\Actions;

use App\Domain\ProductCatalog\DTOs\CreateVendorDTO;
use App\Domain\ProductCatalog\Models\Vendor;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateVendorAction
{
    /**
     * Create a vendor profile for an authenticated user.
     */
    public function execute(User $user, CreateVendorDTO $data): Vendor
    {
        if ($user->vendor !== null) {
            throw ValidationException::withMessages([
                'store_name' => __('You already have a vendor profile.'),
            ]);
        }

        return DB::transaction(function () use ($user, $data): Vendor {
            return Vendor::query()->create([
                'user_id' => $user->id,
                'store_name' => $data->storeName,
                'slug' => $this->generateUniqueSlug($data->storeName),
                'description' => $data->description,
                'is_active' => true,
            ]);
        });
    }

    private function generateUniqueSlug(string $storeName): string
    {
        $baseSlug = Str::slug($storeName);
        $slug = $baseSlug;
        $suffix = 1;

        while (Vendor::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
