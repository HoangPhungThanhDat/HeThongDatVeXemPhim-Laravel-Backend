<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFoodanddrinkRequest;
use App\Http\Resources\FoodanddrinkResource;
use App\Services\FoodanddrinkService;
use Cloudinary\Cloudinary;
use Illuminate\Support\Facades\Log;

/**
 * @group Food & Drinks
 * 
 * APIs for managing food and drink items available in the cinema.
 * These items can be ordered together with movie tickets.
 */
class FoodanddrinkController extends Controller
{
    protected $service;
    protected $cloudinary;

    public function __construct(FoodanddrinkService $service)
    {
        $this->service = $service;
        $this->middleware(['auth:api', 'checkrole:Admin'])->only(['store', 'update', 'destroy']);
        
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => config('cloudinary.cloud_name'),
                'api_key' => config('cloudinary.api_key'),
                'api_secret' => config('cloudinary.api_secret'),
            ],
            'url' => [
                'secure' => config('cloudinary.secure', true),
            ],
        ]);
    }

    /**
     * Get all food & drink items
     * 
     * Get a list of all food and drink items available in the cinema.
     * 
     * @unauthenticated
     * 
     * @response 200 [
     *   {
     *     "ItemId": 1,
     *     "Name": "Bắp rang bơ",
     *     "Description": "Bắp rang bơ thơm ngon",
     *     "Price": 45000,
     *     "Category": "Snacks",
     *     "ImageUrl": "https://res.cloudinary.com/.../foodanddrinks/popcorn.jpg",
     *     "Status": "Available",
     *     "CreatedAt": "2024-01-01 10:00:00"
     *   }
     * ]
     */
    public function index()
    {
        return FoodanddrinkResource::collection($this->service->getAll());
    }

    /**
     * Create a new food & drink item
     * 
     * Create a new food or drink item with image upload to Cloudinary. (Admin only)
     * 
     * @authenticated
     * 
     * @bodyParam Name string required The item name. Example: Bắp rang bơ
     * @bodyParam Description string The item description. Example: Bắp rang bơ thơm ngon
     * @bodyParam Price numeric required The item price in VND. Example: 45000
     * @bodyParam Category string The item category. Enum: Snacks, Beverages, Combos. Example: Snacks
     * @bodyParam ImageUrl file The item image (jpg, png, webp). Max 2MB.
     * @bodyParam Status string The status. Enum: Available, Unavailable. Example: Available
     * 
     * @response 201 {
     *   "ItemId": 1,
     *   "Name": "Bắp rang bơ",
     *   "Description": "Bắp rang bơ thơm ngon",
     *   "Price": 45000,
     *   "Category": "Snacks",
     *   "ImageUrl": "https://res.cloudinary.com/.../foodanddrinks/popcorn.jpg",
     *   "Status": "Available",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "Name": ["The name field is required."],
     *     "Price": ["The price field is required."]
     *   }
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Upload ảnh thất bại: ..."
     * }
     */
    public function store(StoreFoodanddrinkRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('ImageUrl')) {
            try {
                $file = $request->file('ImageUrl');
                $result = $this->cloudinary->uploadApi()->upload($file->getRealPath(), [
                    'folder' => 'foodanddrinks',
                    'transformation' => [
                        'width' => 400,
                        'height' => 400,
                        'crop' => 'fill',
                        'quality' => 'auto:best',
                        'fetch_format' => 'auto'
                    ]
                ]);
                $data['ImageUrl'] = $result['secure_url'];
            } catch (\Exception $e) {
                Log::error('Upload ảnh foodanddrink thất bại: ' . $e->getMessage());
                return response()->json(['success' => false, 'message' => 'Upload ảnh thất bại: ' . $e->getMessage()], 500);
            }
        } else {
            $data['ImageUrl'] = null;
        }

        $foodanddrink = $this->service->create($data);
        return new FoodanddrinkResource($foodanddrink);
    }

    /**
     * Get a specific food & drink item
     * 
     * Get detailed information about a specific food or drink item.
     * 
     * @unauthenticated
     * 
     * @urlParam ItemId int required The ID of the food/drink item. Example: 1
     * 
     * @response 200 {
     *   "ItemId": 1,
     *   "Name": "Bắp rang bơ",
     *   "Description": "Bắp rang bơ thơm ngon",
     *   "Price": 45000,
     *   "Category": "Snacks",
     *   "ImageUrl": "https://res.cloudinary.com/.../foodanddrinks/popcorn.jpg",
     *   "Status": "Available",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 404 {
     *   "message": "Food and drink item not found"
     * }
     */
    public function show($ItemId)
    {
        $foodanddrink = $this->service->find($ItemId);
        if (!$foodanddrink) {
            return response()->json(['message' => 'Food and drink item not found'], 404);
        }

        return new FoodanddrinkResource($foodanddrink);
    }

    /**
     * Update a food & drink item
     * 
     * Update an existing food or drink item. Image upload is optional. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam ItemId int required The ID of the food/drink item. Example: 1
     * @bodyParam Name string The item name. Example: Bắp rang bơ lớn
     * @bodyParam Description string The item description. Example: Bắp rang bơ thơm ngon, giòn rụm
     * @bodyParam Price numeric The price in VND. Example: 55000
     * @bodyParam Category string The category. Enum: Snacks, Beverages, Combos. Example: Snacks
     * @bodyParam ImageUrl file The item image (jpg, png, webp). Max 2MB.
     * @bodyParam Status string The status. Enum: Available, Unavailable. Example: Unavailable
     * 
     * @response 200 {
     *   "ItemId": 1,
     *   "Name": "Bắp rang bơ lớn",
     *   "Description": "Bắp rang bơ thơm ngon, giòn rụm",
     *   "Price": 55000,
     *   "Status": "Unavailable"
     * }
     * @response 404 {
     *   "message": "Food and drink item not found"
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Upload ảnh thất bại: ..."
     * }
     */
    public function update(StoreFoodanddrinkRequest $request, $ItemId)
    {
        $data = $request->validated();
        $foodanddrink = $this->service->find($ItemId);

        if (!$foodanddrink) {
            return response()->json(['message' => 'Food and drink item not found'], 404);
        }

        if ($request->hasFile('ImageUrl')) {
            if ($foodanddrink->ImageUrl && strpos($foodanddrink->ImageUrl, 'cloudinary.com') !== false) {
                try {
                    $publicId = $this->extractPublicId($foodanddrink->ImageUrl);
                    if ($publicId) {
                        $this->cloudinary->uploadApi()->destroy($publicId);
                    }
                } catch (\Exception $e) {
                    Log::error('Xóa ảnh Cloudinary thất bại: ' . $e->getMessage());
                }
            }
            
            try {
                $file = $request->file('ImageUrl');
                $result = $this->cloudinary->uploadApi()->upload($file->getRealPath(), [
                    'folder' => 'foodanddrinks',
                    'transformation' => [
                        'width' => 400,
                        'height' => 400,
                        'crop' => 'fill',
                        'quality' => 'auto:best',
                        'fetch_format' => 'auto'
                    ]
                ]);
                $data['ImageUrl'] = $result['secure_url'];
            } catch (\Exception $e) {
                Log::error('Upload ảnh foodanddrink thất bại: ' . $e->getMessage());
                return response()->json(['success' => false, 'message' => 'Upload ảnh thất bại: ' . $e->getMessage()], 500);
            }
        } else {
            $data['ImageUrl'] = $foodanddrink->ImageUrl;
        }

        $foodanddrink = $this->service->update($ItemId, $data);
        return new FoodanddrinkResource($foodanddrink);
    }

    /**
     * Delete a food & drink item
     * 
     * Delete a food or drink item and remove its image from Cloudinary. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam ItemId int required The ID of the food/drink item. Example: 1
     * 
     * @response 200 {
     *   "message": "Food and drink item deleted successfully"
     * }
     * @response 404 {
     *   "message": "Food and drink item not found"
     * }
     */
    public function destroy($ItemId)
    {
        $foodanddrink = $this->service->find($ItemId);
        
        if ($foodanddrink && $foodanddrink->ImageUrl && strpos($foodanddrink->ImageUrl, 'cloudinary.com') !== false) {
            try {
                $publicId = $this->extractPublicId($foodanddrink->ImageUrl);
                if ($publicId) {
                    $this->cloudinary->uploadApi()->destroy($publicId);
                }
            } catch (\Exception $e) {
                Log::error('Xóa ảnh Cloudinary thất bại: ' . $e->getMessage());
            }
        }
        
        $deleted = $this->service->delete($ItemId);
        if (!$deleted) {
            return response()->json(['message' => 'Food and drink item not found'], 404);
        }

        return response()->json(['message' => 'Food and drink item deleted successfully']);
    }

    private function extractPublicId($url)
    {
        preg_match('/\/upload\/v\d+\/(.+)\.\w+$/', $url, $matches);
        return $matches[1] ?? null;
    }
}