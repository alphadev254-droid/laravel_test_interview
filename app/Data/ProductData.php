<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class ProductData extends Data
{
    public function __construct(
        public string $title,
        public ?string $description,
        public string $category,
        public float $price,
        public ?float $discount_percentage,
        public ?float $rating,
        public int $stock,
        public ?string $thumbnail_path = null,
    ) {
    }

    public static function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'stock' => ['required', 'integer', 'min:0'],
        ];
    }
}
