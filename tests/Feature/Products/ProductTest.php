<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('product list is paginated', function () {
    $admin = User::factory()->admin()->create();
    Product::factory()->count(25)->create(['created_by' => $admin->id]);

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/products?per_page=10');

    $response->assertStatus(200)
        ->assertJsonCount(10, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'category', 'price', 'stock'],
            ],
            'meta',
        ]);
});

test('filter by category works', function () {
    $admin = User::factory()->admin()->create();
    Product::factory()->create(['category' => 'beauty', 'created_by' => $admin->id]);
    Product::factory()->create(['category' => 'electronics', 'created_by' => $admin->id]);

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/products?filter[category]=beauty');

    $response->assertStatus(200);

    $data = $response->json('data');
    expect($data)->toHaveCount(1)
        ->and($data[0]['category'])->toBe('beauty');
});

test('search filter works', function () {
    $admin = User::factory()->admin()->create();
    Product::factory()->create([
        'title' => 'Essence Mascara',
        'description' => 'Great product',
        'created_by' => $admin->id,
    ]);
    Product::factory()->create([
        'title' => 'Other Product',
        'description' => 'Something else',
        'created_by' => $admin->id,
    ]);

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/products?filter[search]=Mascara');

    $response->assertStatus(200);

    $data = $response->json('data');
    expect($data)->toHaveCount(1)
        ->and($data[0]['title'])->toContain('Mascara');
});

test('sorting works', function () {
    $admin = User::factory()->admin()->create();
    Product::factory()->create(['price' => 50, 'created_by' => $admin->id]);
    Product::factory()->create(['price' => 20, 'created_by' => $admin->id]);
    Product::factory()->create(['price' => 100, 'created_by' => $admin->id]);

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/products?sort=price');

    $response->assertStatus(200);

    $prices = collect($response->json('data'))->pluck('price')->toArray();
    expect($prices)->toBe(['20.00', '50.00', '100.00']);
});

test('admin can create product', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/products', [
            'title' => 'New Product',
            'description' => 'Test description',
            'category' => 'beauty',
            'price' => 29.99,
            'stock' => 50,
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.title', 'New Product');

    $this->assertDatabaseHas('products', [
        'title' => 'New Product',
        'created_by' => $admin->id,
    ]);
});

test('normal user cannot create product', function () {
    $user = User::factory()->create(['role' => 'user']);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/products', [
            'title' => 'New Product',
            'description' => 'Test description',
            'category' => 'beauty',
            'price' => 29.99,
            'stock' => 50,
        ]);

    $response->assertStatus(403);
});

test('admin can update any product', function () {
    $admin = User::factory()->admin()->create();
    $otherUser = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $otherUser->id]);

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/v1/products/{$product->id}", [
            'title' => 'Updated Title',
            'description' => $product->description,
            'category' => $product->category,
            'price' => $product->price,
            'stock' => $product->stock,
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.title', 'Updated Title');
});

test('creator can update own product', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->putJson("/api/v1/products/{$product->id}", [
            'title' => 'Updated by Creator',
            'description' => $product->description,
            'category' => $product->category,
            'price' => $product->price,
            'stock' => $product->stock,
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.title', 'Updated by Creator');
});

test('non-creator non-admin cannot update product', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $otherUser->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->putJson("/api/v1/products/{$product->id}", [
            'title' => 'Trying to Update',
            'description' => $product->description,
            'category' => $product->category,
            'price' => $product->price,
            'stock' => $product->stock,
        ]);

    $response->assertStatus(403);
});

test('admin can delete product', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create(['created_by' => $admin->id]);

    $response = $this->actingAs($admin, 'sanctum')
        ->deleteJson("/api/v1/products/{$product->id}");

    $response->assertStatus(200);

    $this->assertSoftDeleted('products', ['id' => $product->id]);
});

test('non-admin cannot delete product', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/products/{$product->id}");

    $response->assertStatus(403);
});

test('upload thumbnail stores file and returns url', function () {
    Storage::fake('public');

    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create(['created_by' => $admin->id]);

    $file = UploadedFile::fake()->image('product.jpg');

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson("/api/v1/products/{$product->id}/thumbnail", [
            'thumbnail' => $file,
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $product->id);

    Storage::disk('public')->assertExists("products/{$product->id}/thumbnail.jpg");
});

test('unauthorized requests fail', function () {
    $response = $this->getJson('/api/v1/products');

    $response->assertStatus(401);
});
