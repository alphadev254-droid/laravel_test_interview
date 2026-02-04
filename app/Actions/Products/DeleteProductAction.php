<?php

namespace App\Actions\Products;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class DeleteProductAction
{
    public function execute(Product $product): bool
    {
        if ($product->thumbnail_path) {
            Storage::disk('public')->delete($product->thumbnail_path);
        }

        return $product->delete();
    }
}
