# Laravel Products API

A versioned REST API for managing products with authentication, CRUD operations, filtering/sorting, and file uploads.

## Technical Stack

- **Laravel 11** - PHP Framework
- **MySQL** - Database
- **Laravel Sanctum** - API Authentication
- **Spatie Laravel Query Builder** - Filtering & Sorting
- **Spatie Laravel Data** - DTOs
- **Pest** - Testing Framework

## Features

- Token-based authentication (Login/Logout/Me)
- Product CRUD operations with authorization
- Advanced filtering and sorting
- Pagination with meta information
- Product thumbnail uploads
- Comprehensive test suite
- Clean architecture with Action classes and DTOs

## Project Structure

```
app/
├── Actions/          # Business logic classes
│   ├── Auth/
│   └── Products/
├── Data/             # DTOs (Data Transfer Objects)
├── Http/
│   ├── Controllers/
│   │   └── Api/V1/   # Versioned API controllers
│   └── Resources/    # API Response formatting
├── Models/           # Eloquent models
└── Policies/         # Authorization policies
```

## Setup Instructions

### Prerequisites

- PHP 8.2+
- Composer
- MySQL (port 3307)
- Git

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd laravel_interview
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   ```

   Update `.env` with your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3307
   DB_DATABASE=laravel_interview_api
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Generate application key**
   ```bash
   php artisan key:generate
   ```

5. **Create database**
   Create a database named `laravel_interview_api` in phpMyAdmin or via CLI:
   ```bash
   mysql -u root -P 3307 -e "CREATE DATABASE IF NOT EXISTS laravel_interview_api;"
   ```

6. **Run migrations and seeders**
   ```bash
   php artisan migrate --seed
   ```

7. **Create storage symlink**
   ```bash
   php artisan storage:link
   ```

8. **Start the development server**
   ```bash
   php artisan serve
   ```

   API will be available at: `http://localhost:8000/api/v1`

## Test Users

After seeding, the following users are available:

- **Admin**
  - Email: `admin@example.com`
  - Password: `password`
  - Role: `admin`

- **Normal User**
  - Email: `user@example.com`
  - Password: `password`
  - Role: `user`

## Running Tests

Run all tests:
```bash
php artisan test
```

Run specific test suite:
```bash
php artisan test --filter=Auth
php artisan test --filter=Product
```

Run with coverage:
```bash
php artisan test --coverage
```

## API Documentation & Testing

### Swagger/OpenAPI Documentation

Access the interactive API documentation at:
```
http://localhost:8000/api/documentation
```

The Swagger specification is available at: `public/swagger.yaml`

### Postman Collection

A complete Postman collection is included in the project root:
- **File**: `Laravel_Products_API.postman_collection.json`
- **Import**: Open Postman → Import → Select the JSON file
- **Pre-configured**: 
  - Base URL: `http://localhost:8000/api/v1`
  - Admin login: `admin@example.com` / `password`
  - User login: `user@example.com` / `password`
  - Auto token management for both admin and user
  - Complete test scenarios including authorization restrictions
  - Error response examples

### Testing with cURL

**Login Example:**
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'
```

### Base URL

```
http://localhost:8000/api/v1
```

### Authentication

#### POST /auth/login

Login and receive authentication token.

**Request:**
```json
{
  "email": "admin@example.com",
  "password": "password"
}
```

**Response (200):**
```json
{
  "data": {
    "token": "1|abc123...",
    "token_type": "Bearer",
    "user": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@example.com",
      "role": "admin"
    }
  }
}
```

#### POST /auth/logout

Logout and revoke current token (requires authentication).

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "message": "Successfully logged out"
}
```

#### GET /auth/me

Get authenticated user information (requires authentication).

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "data": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "role": "admin"
  }
}
```

### Products

All product endpoints require authentication.

#### GET /products

Get paginated list of products with filtering, sorting, and includes.

**Query Parameters:**
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 15, max: 100)
- `filter[category]` - Filter by category
- `filter[price_min]` - Minimum price
- `filter[price_max]` - Maximum price
- `filter[search]` - Search in title and description
- `sort` - Sort by fields (e.g., `price`, `-stock`, `title`)
- `include` - Include relationships (e.g., `creator`)

**Example Request:**
```
GET /products?page=1&per_page=10&filter[category]=beauty&sort=-price&include=creator
```

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Essence Mascara Lash Princess",
      "description": "...",
      "category": "beauty",
      "price": "9.99",
      "discount_percentage": "7.17",
      "rating": "4.94",
      "stock": 99,
      "thumbnail": "http://localhost:8000/storage/products/1/thumbnail.jpg",
      "created_at": "2026-02-04T12:00:00.000000Z",
      "updated_at": "2026-02-04T12:00:00.000000Z",
      "creator": {
        "id": 1,
        "name": "Admin User",
        "email": "admin@example.com",
        "role": "admin"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 50,
    "last_page": 5
  }
}
```

#### GET /products/{id}

Get a single product.

**Response (200):**
```json
{
  "data": {
    "id": 1,
    "title": "Essence Mascara Lash Princess",
    "description": "...",
    "category": "beauty",
    "price": "9.99",
    "stock": 99,
    "thumbnail": "http://localhost:8000/storage/products/1/thumbnail.jpg",
    "created_at": "2026-02-04T12:00:00.000000Z",
    "updated_at": "2026-02-04T12:00:00.000000Z"
  }
}
```

#### POST /products

Create a new product (Admin only).

**Request:**
```json
{
  "title": "New Product",
  "description": "Product description",
  "category": "beauty",
  "price": 29.99,
  "discount_percentage": 10.00,
  "rating": 4.5,
  "stock": 50
}
```

**Response (201):**
```json
{
  "data": {
    "id": 51,
    "title": "New Product",
    ...
  }
}
```

#### PUT/PATCH /products/{id}

Update a product (Admin or Creator only).

**Request:**
```json
{
  "title": "Updated Product",
  "description": "Updated description",
  "category": "beauty",
  "price": 39.99,
  "stock": 75
}
```

**Response (200):**
```json
{
  "data": {
    "id": 1,
    "title": "Updated Product",
    ...
  }
}
```

#### DELETE /products/{id}

Delete a product (Admin only).

**Response (200):**
```json
{
  "message": "Product deleted successfully"
}
```

#### POST /products/{id}/thumbnail

Upload product thumbnail image (Admin or Creator only).

**Request:**
- Content-Type: `multipart/form-data`
- Field: `thumbnail` (file)
- Allowed types: jpeg, png, webp
- Max size: 2MB

**Response (200):**
```json
{
  "data": {
    "id": 1,
    "thumbnail": "http://localhost:8000/storage/products/1/thumbnail.jpg",
    ...
  }
}
```

## Authorization Rules

- **ViewAny/View**: All authenticated users
- **Create**: Admin only
- **Update**: Admin OR product creator
- **Delete**: Admin only

## Error Responses

**401 Unauthorized:**
```json
{
  "message": "Unauthenticated."
}
```

**403 Forbidden:**
```json
{
  "message": "This action is unauthorized."
}
```

**422 Validation Error:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

## Architecture Notes

### Action Classes

Business logic is encapsulated in Action classes for maintainability:
- `LoginAction` - Handles user authentication
- `LogoutAction` - Revokes user tokens
- `CreateProductAction` - Creates products with creator assignment
- `UpdateProductAction` - Updates product fields
- `DeleteProductAction` - Deletes products and associated files
- `UploadThumbnailAction` - Handles file uploads and storage

### DTOs (Data Transfer Objects)

Laravel Data is used for type-safe DTOs with validation:
- `LoginData` - Login credentials
- `ProductData` - Product data with validation rules
- `UserData` - User information

### API Resources

Consistent JSON responses via Laravel Resources:
- `UserResource` - User data formatting
- `ProductResource` - Product data with conditional includes

## License

MIT License

## Support

For issues or questions, contact: hr@aliensoft.co.ke
"# laravel_test_interview" 
