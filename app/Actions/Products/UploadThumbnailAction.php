<?php

namespace App\Actions\Products;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadThumbnailAction
{
    public function execute(Product $product, UploadedFile $file): Product
    {
        if ($product->thumbnail_path) {
            Storage::disk('public')->delete($product->thumbnail_path);
        }

        $extension = $file->getClientOriginalExtension();
        $path = $file->storeAs(
            "products/{$product->id}",
            "thumbnail.{$extension}",
            'public'
        );

        $product->update(['thumbnail_path' => $path]);

        return $product->fresh();
    }
}
