<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBannerRequest;
use App\Http\Resources\BannerResource;
use App\Services\BannerService;
use Cloudinary\Cloudinary;
use Illuminate\Support\Facades\Log;

/**
 * @group Banners
 * 
 * APIs for managing banners. Banners are displayed on the homepage and other pages.
 * These endpoints require authentication and Admin permissions for write operations.
 */
class BannerController extends Controller
{
    protected $bannerService;
    protected $cloudinary;

    public function __construct(BannerService $bannerService)
    {
        $this->bannerService = $bannerService;
        
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
     * Get all banners
     * 
     * Get a list of all banners in the system.
     * 
     * @unauthenticated
     * 
     * @response 200 [
     *   {
     *     "BannerId": 1,
     *     "Title": "Summer Movie Festival",
     *     "Description": "Get 20% off on all tickets",
     *     "ImageUrl": "https://res.cloudinary.com/.../banners/summer.jpg",
     *     "Link": "https://example.com/promotion",
     *     "Status": "Active",
     *     "Order": 1,
     *     "CreatedAt": "2024-01-01 10:00:00"
     *   }
     * ]
     */
    public function index()
    {
        $banners = $this->bannerService->getAll();
        return BannerResource::collection($banners);
    }

    /**
     * Create a new banner
     * 
     * Create a new banner with image upload to Cloudinary. (Admin only)
     * 
     * @authenticated
     * 
     * @bodyParam Title string required The banner title. Example: Summer Movie Festival
     * @bodyParam Description string The banner description. Example: Get 20% off on all tickets
     * @bodyParam ImageUrl file required The banner image file (jpg, png, webp). Max 2MB.
     * @bodyParam Link string The URL to redirect when clicked. Example: https://example.com/promotion
     * @bodyParam Status string The banner status. Enum: Active, Inactive. Example: Active
     * @bodyParam Order int The display order. Lower number appears first. Example: 1
     * 
     * @response 201 {
     *   "BannerId": 1,
     *   "Title": "Summer Movie Festival",
     *   "Description": "Get 20% off on all tickets",
     *   "ImageUrl": "https://res.cloudinary.com/.../banners/summer.jpg",
     *   "Link": "https://example.com/promotion",
     *   "Status": "Active",
     *   "Order": 1,
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "Title": ["The title field is required."],
     *     "ImageUrl": ["The image url field is required."]
     *   }
     * }
     * @response 500 {
     *   "message": "Upload ảnh thất bại: ..."
     * }
     */
    public function store(StoreBannerRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('ImageUrl')) {
            try {
                $file = $request->file('ImageUrl');
                $result = $this->cloudinary->uploadApi()->upload($file->getRealPath(), [
                    'folder' => 'banners',
                    'transformation' => [
                        'width' => 1200,
                        'height' => 500,
                        'crop' => 'fill',
                        'quality' => 'auto:best',
                        'fetch_format' => 'auto'
                    ]
                ]);
                $data['ImageUrl'] = $result['secure_url'];
            } catch (\Exception $e) {
                return response()->json(['message' => 'Upload ảnh thất bại: ' . $e->getMessage()], 500);
            }
        } else {
            $data['ImageUrl'] = $request->input('ImageUrl') ?? null;
        }

        $banner = $this->bannerService->create($data);
        return new BannerResource($banner);
    }

    /**
     * Get a specific banner
     * 
     * Get detailed information about a specific banner.
     * 
     * @unauthenticated
     * 
     * @urlParam BannerId int required The ID of the banner. Example: 1
     * 
     * @response 200 {
     *   "BannerId": 1,
     *   "Title": "Summer Movie Festival",
     *   "Description": "Get 20% off on all tickets",
     *   "ImageUrl": "https://res.cloudinary.com/.../banners/summer.jpg",
     *   "Link": "https://example.com/promotion",
     *   "Status": "Active",
     *   "Order": 1,
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 404 {
     *   "message": "Banner not found"
     * }
     */
    public function show($BannerId)
    {
        $banner = $this->bannerService->getById($BannerId);
        return new BannerResource($banner);
    }

    /**
     * Update a banner
     * 
     * Update an existing banner. Image upload is optional. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam BannerId int required The ID of the banner. Example: 1
     * @bodyParam Title string The banner title. Example: Updated Summer Festival
     * @bodyParam Description string The banner description. Example: Get 25% off on all tickets
     * @bodyParam ImageUrl file The banner image file (jpg, png, webp). Max 2MB.
     * @bodyParam Link string The URL to redirect. Example: https://example.com/new-promotion
     * @bodyParam Status string The banner status. Enum: Active, Inactive. Example: Inactive
     * @bodyParam Order int The display order. Example: 2
     * 
     * @response 200 {
     *   "BannerId": 1,
     *   "Title": "Updated Summer Festival",
     *   "Description": "Get 25% off on all tickets",
     *   "ImageUrl": "https://res.cloudinary.com/.../banners/updated.jpg",
     *   "Link": "https://example.com/new-promotion",
     *   "Status": "Inactive",
     *   "Order": 2,
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 404 {
     *   "message": "Banner not found"
     * }
     * @response 500 {
     *   "message": "Upload ảnh thất bại: ..."
     * }
     */
    public function update(StoreBannerRequest $request, $BannerId)
    {
        $data = $request->validated();
        $banner = $this->bannerService->getById($BannerId); // Lấy từ Service

        if ($request->hasFile('ImageUrl')) {
            if ($banner->ImageUrl && strpos($banner->ImageUrl, 'cloudinary.com') !== false) {
                try {
                    $publicId = $this->extractPublicId($banner->ImageUrl);
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
                    'folder' => 'banners',
                    'transformation' => [
                        'width' => 1200,
                        'height' => 500,
                        'crop' => 'fill',
                        'quality' => 'auto:best',
                        'fetch_format' => 'auto'
                    ]
                ]);
                $data['ImageUrl'] = $result['secure_url'];
            } catch (\Exception $e) {
                return response()->json(['message' => 'Upload ảnh thất bại: ' . $e->getMessage()], 500);
            }
        } else {
            $data['ImageUrl'] = $banner->ImageUrl;
        }

        $banner = $this->bannerService->update($BannerId, $data);
        return new BannerResource($banner);
    }

    /**
     * Delete a banner
     * 
     * Delete a banner and remove its image from Cloudinary. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam BannerId int required The ID of the banner. Example: 1
     * 
     * @response 200 {
     *   "message": "Banner deleted successfully"
     * }
     * @response 404 {
     *   "message": "Banner not found"
     * }
     */
    public function destroy($BannerId)
    {
        $banner = $this->bannerService->getById($BannerId);
        
        if ($banner->ImageUrl && strpos($banner->ImageUrl, 'cloudinary.com') !== false) {
            try {
                $publicId = $this->extractPublicId($banner->ImageUrl);
                if ($publicId) {
                    $this->cloudinary->uploadApi()->destroy($publicId);
                }
            } catch (\Exception $e) {
                Log::error('Xóa ảnh Cloudinary thất bại: ' . $e->getMessage());
            }
        }
        
        $this->bannerService->delete($BannerId);
        return response()->json(['message' => 'Banner deleted successfully']);
    }

    private function extractPublicId($url)
    {
        preg_match('/\/upload\/v\d+\/(.+)\.\w+$/', $url, $matches);
        return $matches[1] ?? null;
    }
}