<?php

use Knuckles\Scribe\Config\AuthIn;
use Knuckles\Scribe\Extracting\Strategies;

return [
    /*
    |--------------------------------------------------------------------------
    | Title and Description
    |--------------------------------------------------------------------------
    */
    'title' => 'Gấu Phim API Documentation',
    'description' => 'API Documentation for Gấu Phim Movie Booking System',

    'intro_text' => <<<'INTRO'
        # GẤU PHIM API DOCUMENTATION

        Welcome to the Gấu Phim API documentation. This API powers the movie booking system.

        ## 🔐 Authentication

        Most endpoints require authentication using JWT Bearer token.

To get a token, use the `/api/login` endpoint.

## 📋 Base URL

All API endpoints are prefixed with `/api/`

## 📦 Response Format

All responses are in JSON format with appropriate HTTP status codes.

## 🔄 Rate Limiting

API requests are rate limited to 60 requests per minute per IP.

<aside>
💡 You can test endpoints directly using the "Try It Out" button on each endpoint.
</aside>
INTRO,

    'base_url' => config('app.url'),

    /*
|--------------------------------------------------------------------------
| Routes Configuration
|--------------------------------------------------------------------------
*/
    'routes' => [
        [
            'match' => [
                'prefixes' => ['api/*'],
                'domains' => ['*'],
            ],
            'include' => [],
            'exclude' => [
                'api/documentation',
                'api/docs',
            ],
        ],
    ],

    /*
|--------------------------------------------------------------------------
| Documentation Type & Theme
|--------------------------------------------------------------------------
*/
    'type' => 'laravel',
    'theme' => 'default',

    /*
|--------------------------------------------------------------------------
| Static Output (cho type = static)
|--------------------------------------------------------------------------
*/
    'static' => [
        'output_path' => 'public/docs',
    ],

    /*
|--------------------------------------------------------------------------
| Laravel Output (cho type = laravel)
|--------------------------------------------------------------------------
*/
    'laravel' => [
        'add_routes' => true,
        'docs_url' => '/docs',
        'assets_directory' => null,
        'middleware' => [],
        // ✅ THÊM DÒNG NÀY CHO CSS CUSTOM
        'custom_css' => '/css/custom.css',
    ],

    /*
|--------------------------------------------------------------------------
| Try It Out Feature
|--------------------------------------------------------------------------
*/
    'try_it_out' => [
        'enabled' => true,
        'base_url' => null,
        'use_csrf' => false,
        'csrf_url' => '/sanctum/csrf-cookie',
    ],

    /*
|--------------------------------------------------------------------------
| Authentication Configuration
|--------------------------------------------------------------------------
*/
    'auth' => [
        'enabled' => true,
        'default' => false,
        'in' => AuthIn::BEARER->value,
        'name' => 'Authorization',
        'use_value' => env('SCRIBE_AUTH_KEY'),
        'placeholder' => 'Bearer {your_jwt_token_here}',
        'extra_info' => <<<'INFO'
    To authenticate API requests, you need to include a JWT token in the `Authorization` header.

    **How to get your token:**
    1. Call the `/api/login` endpoint with your credentials
    2. Copy the `token` from the response
    3. Include it in subsequent requests as: `Authorization: Bearer {token}`

    **Note:** Tokens expire after 24 hours.
    INFO,
    ],

    /*
|--------------------------------------------------------------------------
| Example Languages
|--------------------------------------------------------------------------
*/
    'example_languages' => [
        'bash',
        'javascript',
        'php',
        'python',
    ],

    /*
|--------------------------------------------------------------------------
| Postman Collection
|--------------------------------------------------------------------------
*/
    'postman' => [
        'enabled' => true,
        'overrides' => [
            'info.version' => '1.0.0',
        ],
    ],

    /*
|--------------------------------------------------------------------------
| OpenAPI Specification
|--------------------------------------------------------------------------
*/
    'openapi' => [
        'enabled' => true,
        'version' => '3.0.3',
        'overrides' => [
            'info.version' => '1.0.0',
            'info.description' => 'Gấu Phim Movie Booking API',
        ],
        'generators' => [],
    ],

    /*
|--------------------------------------------------------------------------
| Groups Order
|--------------------------------------------------------------------------
*/
    'groups' => [
        'default' => 'Endpoints',
        'order' => [
            'Authentication',
            'Users',
            'Movies',
            'Cinemas',
            'Showtimes',
            'Seats',
            'Orders',
            'Tickets',
            'Payments',
            'Promotions',
            'Banners',
            'News',
            'Genres',
            'Rooms',
            'Staff',
            'Reviews',
            'Wishlists',
            'Notifications',
            'Contacts',
            'Audit Logs',
            'Login History',
            'Food & Drinks',
            'Memberships',
            'Distributors',
        ],
    ],

    /*
|--------------------------------------------------------------------------
| Logo
|--------------------------------------------------------------------------
*/
    'logo' => false,

    /*
|--------------------------------------------------------------------------
| Last Updated
|--------------------------------------------------------------------------
*/
    'last_updated' => 'Last updated: {date:F j, Y}',

    /*
|--------------------------------------------------------------------------
| Examples Configuration
|--------------------------------------------------------------------------
*/
    'examples' => [
        'faker_seed' => 1234,
        'models_source' => ['factoryCreate', 'factoryMake', 'databaseFirst'],
    ],

    /*
|--------------------------------------------------------------------------
| Extraction Strategies
|--------------------------------------------------------------------------
*/
    'strategies' => [
        'metadata' => [
            Strategies\Metadata\GetFromDocBlocks::class,
        ],
        'headers' => [
            Strategies\Headers\GetFromHeaderTag::class,
        ],
        'urlParameters' => [
            Strategies\UrlParameters\GetFromLaravelAPI::class,
            Strategies\UrlParameters\GetFromUrlParamTag::class,
        ],
        'queryParameters' => [
            Strategies\QueryParameters\GetFromQueryParamTag::class,
        ],
        'bodyParameters' => [
            Strategies\BodyParameters\GetFromFormRequest::class,
            Strategies\BodyParameters\GetFromBodyParamTag::class,
        ],
        'responses' => [
            Strategies\Responses\UseResponseAttributes::class,
            Strategies\Responses\UseResponseTag::class,
            Strategies\Responses\UseApiResourceTags::class,
        ],
        'responseFields' => [
            Strategies\ResponseFields\GetFromResponseFieldTag::class,
        ],
    ],

    /*
|--------------------------------------------------------------------------
| Database Transactions
|--------------------------------------------------------------------------
*/
    'database_connections_to_transact' => [config('database.default')],

    /*
|--------------------------------------------------------------------------
| Fractal Serializer
|--------------------------------------------------------------------------
*/
    'fractal' => [
        'serializer' => null,
    ],
];
