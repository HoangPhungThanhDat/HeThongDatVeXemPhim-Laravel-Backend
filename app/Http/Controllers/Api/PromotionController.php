<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePromotionRequest;
use App\Http\Resources\PromotionResource;
use App\Services\PromotionService;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Cloudinary\Cloudinary;
use Illuminate\Support\Facades\Log;

/**
 * @group Promotions
 * 
 * APIs for managing promotions and discount campaigns.
 * Promotions can be applied to orders to provide discounts via percentage or fixed amount.
 */
class PromotionController extends Controller
{
    protected $service;
    protected $cloudinary;

    public function __construct(PromotionService $service)
    {
        $this->service = $service;
        
        // Khởi tạo Cloudinary
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
     * Get all promotions
     * 
     * Get a list of all promotions in the system.
     * 
     * @unauthenticated
     * 
     * @response 200 [
     *   {
     *     "PromotionId": 1,
     *     "Title": "Summer Sale 2024",
     *     "Code": "SUMMER20",
     *     "Description": "Get 20% off on all tickets this summer",
     *     "DiscountType": "Percentage",
     *     "DiscountValue": 20,
     *     "MinOrderAmount": 100000,
     *     "StartDate": "2024-06-01 00:00:00",
     *     "EndDate": "2024-08-31 23:59:59",
     *     "ImageUrl": "https://res.cloudinary.com/.../promotions/summer.jpg",
     *     "Status": "Active",
     *     "UsageLimit": 1000,
     *     "UsedCount": 150,
     *     "CreatedAt": "2024-06-01 10:00:00"
     *   },
     *   {
     *     "PromotionId": 2,
     *     "Title": "New User Discount",
     *     "Code": "NEWUSER50",
     *     "Description": "Get 50,000 VND off for new users",
     *     "DiscountType": "Fixed",
     *     "DiscountValue": 50000,
     *     "MinOrderAmount": 0,
     *     "StartDate": "2024-01-01 00:00:00",
     *     "EndDate": "2024-12-31 23:59:59",
     *     "ImageUrl": null,
     *     "Status": "Active",
     *     "UsageLimit": 500,
     *     "UsedCount": 320,
     *     "CreatedAt": "2024-01-01 10:00:00"
     *   }
     * ]
     */
    public function index()
    {
        return PromotionResource::collection($this->service->getAll());
    }

    /**
     * Get paginated promotions
     * 
     * Get a paginated list of promotions with search and filter options. (Admin only)
     * 
     * @authenticated
     * 
     * @queryParam page int The page number. Default: 1. Example: 1
     * @queryParam limit int Number of items per page. Default: 10. Max: 100. Example: 10
     * @queryParam search string Search by title or code. Example: Summer
     * @queryParam status string Filter by status. Enum: Active, Inactive, Expired. Example: Active
     * 
     * @response 200 {
     *   "data": [
     *     {
     *       "PromotionId": 1,
     *       "Title": "Summer Sale 2024",
     *       "Code": "SUMMER20",
     *       "DiscountValue": 20,
     *       "Status": "Active"
     *     }
     *   ],
     *   "total": 100,
     *   "page": 1,
     *   "limit": 10,
     *   "totalPages": 10
     * }
     * @response 401 {
     *   "message": "Unauthenticated"
     * }
     * @response 403 {
     *   "message": "Forbidden - Admin only"
     * }
     */
    public function getPaged(Request $request)
    {
        $page   = max(1, (int) $request->query('page', 1));
        $limit  = min(100, max(1, (int) $request->query('limit', 10)));
        $search = $request->query('search', '');
        $status = $request->query('status', '');

        $query = Promotion::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('Title', 'like', "%{$search}%")
                  ->orWhere('Code', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('Status', $status);
        }

        $total      = $query->count();
        $totalPages = (int) ceil($total / $limit);
        $items      = $query->orderBy('PromotionId', 'desc')
                            ->skip(($page - 1) * $limit)
                            ->take($limit)
                            ->get();

        return response()->json([
            'data'       => PromotionResource::collection($items),
            'total'      => $total,
            'page'       => $page,
            'limit'      => $limit,
            'totalPages' => $totalPages,
        ]);
    }

    /**
     * Create a new promotion
     * 
     * Create a new promotion with image upload to Cloudinary. (Admin only)
     * 
     * @authenticated
     * 
     * @bodyParam Title string required The promotion title. Example: Summer Sale 2024
     * @bodyParam Code string required The promotion code (unique). Example: SUMMER20
     * @bodyParam Description string The promotion description. Example: Get 20% off on all tickets
     * @bodyParam DiscountType string required The discount type. Enum: Percentage, Fixed. Example: Percentage
     * @bodyParam DiscountValue numeric required The discount value. Example: 20
     * @bodyParam MinOrderAmount numeric The minimum order amount to apply. Example: 100000
     * @bodyParam StartDate string required The start date. Example: 2024-06-01 00:00:00
     * @bodyParam EndDate string required The end date. Example: 2024-08-31 23:59:59
     * @bodyParam ImageUrl file The promotion image (jpg, png, webp). Max 2MB.
     * @bodyParam Status string The status. Enum: Active, Inactive, Expired. Example: Active
     * @bodyParam UsageLimit int The maximum number of uses. Example: 1000
     * 
     * @response 201 {
     *   "PromotionId": 1,
     *   "Title": "Summer Sale 2024",
     *   "Code": "SUMMER20",
     *   "DiscountType": "Percentage",
     *   "DiscountValue": 20,
     *   "Status": "Active",
     *   "ImageUrl": "https://res.cloudinary.com/.../promotions/summer.jpg"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "Title": ["The title field is required."],
     *     "Code": ["The code field is required."],
     *     "DiscountType": ["The discount type field is required."],
     *     "DiscountValue": ["The discount value field is required."]
     *   }
     * }
     * @response 500 {
     *   "message": "Upload ảnh thất bại: ..."
     * }
     */
    public function store(StorePromotionRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('ImageUrl')) {
            try {
                $file = $request->file('ImageUrl');
                
                $result = $this->cloudinary->uploadApi()->upload($file->getRealPath(), [
                    'folder' => 'promotions',
                    'transformation' => [
                        'width' => 800,
                        'height' => 400,
                        'crop' => 'fill',
                        'quality' => 'auto:best',
                        'fetch_format' => 'auto'
                    ]
                ]);
                
                $data['ImageUrl'] = $result['secure_url'];
            } catch (\Exception $e) {
                Log::error('Upload ảnh promotion thất bại: ' . $e->getMessage());
                return response()->json([
                    'message' => 'Upload ảnh thất bại: ' . $e->getMessage()
                ], 500);
            }
        }

        $promotion = $this->service->create($data);
        return new PromotionResource($promotion);
    }

    /**
     * Get a specific promotion
     * 
     * Get detailed information about a specific promotion.
     * 
     * @unauthenticated
     * 
     * @urlParam PromotionId int required The ID of the promotion. Example: 1
     * 
     * @response 200 {
     *   "PromotionId": 1,
     *   "Title": "Summer Sale 2024",
     *   "Code": "SUMMER20",
     *   "Description": "Get 20% off on all tickets this summer",
     *   "DiscountType": "Percentage",
     *   "DiscountValue": 20,
     *   "MinOrderAmount": 100000,
     *   "StartDate": "2024-06-01 00:00:00",
     *   "EndDate": "2024-08-31 23:59:59",
     *   "ImageUrl": "https://res.cloudinary.com/.../promotions/summer.jpg",
     *   "Status": "Active",
     *   "UsageLimit": 1000,
     *   "UsedCount": 150
     * }
     * @response 404 {
     *   "message": "Promotion not found"
     * }
     */
    public function show($PromotionId)
    {
        $promotion = $this->service->find($PromotionId);
        if (!$promotion) {
            return response()->json(['message' => 'Promotion not found'], 404);
        }
        return new PromotionResource($promotion);
    }

    /**
     * Update a promotion
     * 
     * Update an existing promotion. Image upload is optional. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam PromotionId int required The ID of the promotion. Example: 1
     * @bodyParam Title string The promotion title. Example: Summer Sale 2024 - Extended
     * @bodyParam Code string The promotion code. Example: SUMMER25
     * @bodyParam Description string The promotion description.
     * @bodyParam DiscountType string The discount type. Enum: Percentage, Fixed.
     * @bodyParam DiscountValue numeric The discount value.
     * @bodyParam MinOrderAmount numeric The minimum order amount.
     * @bodyParam StartDate string The start date.
     * @bodyParam EndDate string The end date.
     * @bodyParam ImageUrl file The promotion image (jpg, png, webp). Max 2MB.
     * @bodyParam Status string The status. Enum: Active, Inactive, Expired.
     * @bodyParam UsageLimit int The maximum number of uses.
     * 
     * @response 200 {
     *   "PromotionId": 1,
     *   "Title": "Summer Sale 2024 - Extended",
     *   "Code": "SUMMER25",
     *   "DiscountValue": 25,
     *   "Status": "Active"
     * }
     * @response 404 {
     *   "message": "Promotion not found"
     * }
     * @response 500 {
     *   "message": "Upload ảnh thất bại: ..."
     * }
     */
    public function update(StorePromotionRequest $request, $PromotionId)
    {
        $promotion = Promotion::findOrFail($PromotionId);
        $data = $request->validated();

        if ($request->hasFile('ImageUrl')) {
            // Xóa ảnh cũ trên Cloudinary
            if ($promotion->ImageUrl && strpos($promotion->ImageUrl, 'cloudinary.com') !== false) {
                try {
                    $publicId = $this->extractPublicId($promotion->ImageUrl);
                    if ($publicId) {
                        $this->cloudinary->uploadApi()->destroy($publicId);
                    }
                } catch (\Exception $e) {
                    Log::error('Xóa ảnh Cloudinary thất bại: ' . $e->getMessage());
                }
            }
            
            // Upload ảnh mới
            try {
                $file = $request->file('ImageUrl');
                $result = $this->cloudinary->uploadApi()->upload($file->getRealPath(), [
                    'folder' => 'promotions',
                    'transformation' => [
                        'width' => 800,
                        'height' => 400,
                        'crop' => 'fill',
                        'quality' => 'auto:best',
                        'fetch_format' => 'auto'
                    ]
                ]);
                
                $data['ImageUrl'] = $result['secure_url'];
            } catch (\Exception $e) {
                Log::error('Upload ảnh promotion thất bại: ' . $e->getMessage());
                return response()->json([
                    'message' => 'Upload ảnh thất bại: ' . $e->getMessage()
                ], 500);
            }
        } else {
            $data['ImageUrl'] = $promotion->ImageUrl;
        }

        $promotion = $this->service->update($PromotionId, $data);
        return new PromotionResource($promotion);
    }

    /**
     * Delete a promotion
     * 
     * Delete a promotion and remove its image from Cloudinary. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam PromotionId int required The ID of the promotion. Example: 1
     * 
     * @response 200 {
     *   "message": "Promotion deleted successfully"
     * }
     * @response 404 {
     *   "message": "Promotion not found"
     * }
     */
    public function destroy($PromotionId)
    {
        $promotion = Promotion::find($PromotionId);
        
        if ($promotion) {
            // Xóa ảnh trên Cloudinary
            if ($promotion->ImageUrl && strpos($promotion->ImageUrl, 'cloudinary.com') !== false) {
                try {
                    $publicId = $this->extractPublicId($promotion->ImageUrl);
                    if ($publicId) {
                        $this->cloudinary->uploadApi()->destroy($publicId);
                    }
                } catch (\Exception $e) {
                    Log::error('Xóa ảnh Cloudinary thất bại: ' . $e->getMessage());
                }
            }
        }
        
        $deleted = $this->service->delete($PromotionId);
        if (!$deleted) {
            return response()->json(['message' => 'Promotion not found'], 404);
        }
        return response()->json(['message' => 'Promotion deleted successfully']);
    }

    /**
     * Extract public ID from Cloudinary URL
     * 
     * @param string $url
     * @return string|null
     */
    private function extractPublicId($url)
    {
        preg_match('/\/upload\/v\d+\/(.+)\.\w+$/', $url, $matches);
        return $matches[1] ?? null;
    }
}