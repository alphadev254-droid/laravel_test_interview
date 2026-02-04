<?php

namespace App\Actions\Products;

use App\Data\ProductData;
use App\Models\Product;

class UpdateProductAction
{
    public function execute(Product $product, ProductData $data): Product
    {
        $product->update([
            'title' => $data->title,
            'description' => $data->description,
            'category' => $data->category,
            'price' => $data->price,
            'discount_percentage' => $data->discount_percentage,
            'rating' => $data->rating,
            'stock' => $data->stock,
        ]);

        return $product->fresh();
    }
}
