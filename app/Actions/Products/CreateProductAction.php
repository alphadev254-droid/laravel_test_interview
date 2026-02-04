<?php

namespace App\Actions\Products;

use App\Data\ProductData;
use App\Models\Product;
use App\Models\User;

class CreateProductAction
{
    public function execute(ProductData $data, User $user): Product
    {
        return Product::create([
            'title' => $data->title,
            'description' => $data->description,
            'category' => $data->category,
            'price' => $data->price,
            'discount_percentage' => $data->discount_percentage,
            'rating' => $data->rating,
            'stock' => $data->stock,
            'thumbnail_path' => $data->thumbnail_path,
            'created_by' => $user->id,
        ]);
    }
}
