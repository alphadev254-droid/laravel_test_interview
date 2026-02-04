<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Products\CreateProductAction;
use App\Actions\Products\DeleteProductAction;
use App\Actions\Products\UpdateProductAction;
use App\Actions\Products\UploadThumbnailAction;
use App\Data\ProductData;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ProductController extends Controller
{
    public function __construct(
        private readonly CreateProductAction $createAction,
        private readonly UpdateProductAction $updateAction,
        private readonly DeleteProductAction $deleteAction,
        private readonly UploadThumbnailAction $uploadThumbnailAction,
    ) {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Product::class);

        $perPage = min((int) $request->get('per_page', 15), 100);

        $products = QueryBuilder::for(Product::class)
            ->allowedFilters([
                AllowedFilter::exact('category'),
                AllowedFilter::callback('price_min', fn($query, $value) => $query->where('price', '>=', $value)),
                AllowedFilter::callback('price_max', fn($query, $value) => $query->where('price', '<=', $value)),
                AllowedFilter::callback('search', fn($query, $value) =>
                    $query->where('title', 'like', "%{$value}%")
                          ->orWhere('description', 'like', "%{$value}%")
                ),
            ])
            ->allowedSorts(['price', 'stock', 'title', 'created_at'])
            ->allowedIncludes(['creator'])
            ->paginate($perPage);

        return ProductResource::collection($products);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Product::class);

        $data = ProductData::from($request->validate(ProductData::rules()));

        $product = $this->createAction->execute($data, $request->user());

        return response()->json([
            'data' => new ProductResource($product),
        ], 201);
    }

    public function show(Product $product): JsonResponse
    {
        $this->authorize('view', $product);

        return response()->json([
            'data' => new ProductResource($product),
        ]);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $data = ProductData::from($request->validate(ProductData::rules()));

        $product = $this->updateAction->execute($product, $data);

        return response()->json([
            'data' => new ProductResource($product),
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->authorize('delete', $product);

        $this->deleteAction->execute($product);

        return response()->json([
            'message' => 'Product deleted successfully',
        ]);
    }

    public function uploadThumbnail(Request $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $request->validate([
            'thumbnail' => ['required', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
        ]);

        $product = $this->uploadThumbnailAction->execute($product, $request->file('thumbnail'));

        return response()->json([
            'data' => new ProductResource($product),
        ]);
    }
}
