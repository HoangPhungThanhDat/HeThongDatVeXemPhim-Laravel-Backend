<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests\StoreNewsRequest;
use App\Http\Resources\NewsResource;
use App\Http\Controllers\Controller;
use App\Services\NewsService;
use Cloudinary\Cloudinary;
use Illuminate\Support\Facades\Log;

/**
 * @group News
 * 
 * APIs for managing news articles and announcements.
 * News articles are displayed on the homepage and can be managed by staff/admin.
 */
class NewsController extends Controller
{
    protected $newsService;
    protected $cloudinary;

    public function __construct(NewsService $newsService)
    {
        $this->newsService = $newsService;
        
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
     * Get all news articles
     * 
     * Get a list of all news articles in the system.
     * 
     * @unauthenticated
     * 
     * @response 200 [
     *   {
     *     "NewsId": 1,
     *     "Title": "Summer Movie Festival 2024",
     *     "Content": "Join us for the biggest movie festival of the year...",
     *     "ImageUrl": "https://res.cloudinary.com/.../news/summer-festival.jpg",
     *     "Author": "Admin",
     *     "Status": "Published",
     *     "ViewCount": 1500,
     *     "PublishedAt": "2024-06-01 10:00:00",
     *     "CreatedAt": "2024-06-01 09:00:00"
     *   }
     * ]
     */
    public function index()
    {
        $newss = $this->newsService->getAll();
        return NewsResource::collection($newss);
    }

    /**
     * Create a new news article
     * 
     * Create a new news article with image upload to Cloudinary. (Staff/Admin only)
     * 
     * @authenticated
     * 
     * @bodyParam Title string required The news title. Example: Summer Movie Festival 2024
     * @bodyParam Content string required The news content. Example: Join us for the biggest movie festival...
     * @bodyParam ImageUrl file The news image (jpg, png, webp). Max 2MB.
     * @bodyParam Author string The author name. Example: Admin
     * @bodyParam Status string The status. Enum: Draft, Published, Archived. Example: Published
     * @bodyParam PublishedAt string The publication date and time. Example: 2024-06-01 10:00:00
     * 
     * @response 201 {
     *   "NewsId": 1,
     *   "Title": "Summer Movie Festival 2024",
     *   "Content": "Join us for the biggest movie festival...",
     *   "ImageUrl": "https://res.cloudinary.com/.../news/summer-festival.jpg",
     *   "Author": "Admin",
     *   "Status": "Published",
     *   "ViewCount": 0,
     *   "PublishedAt": "2024-06-01 10:00:00",
     *   "CreatedAt": "2024-06-01 09:00:00"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "Title": ["The title field is required."],
     *     "Content": ["The content field is required."]
     *   }
     * }
     * @response 500 {
     *   "message": "Upload ảnh thất bại: ..."
     * }
     */
    public function store(StoreNewsRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('ImageUrl')) {
            try {
                $file = $request->file('ImageUrl');
                $result = $this->cloudinary->uploadApi()->upload($file->getRealPath(), [
                    'folder' => 'news',
                    'transformation' => [
                        'width' => 800,
                        'height' => 450,
                        'crop' => 'fill',
                        'quality' => 'auto:best',
                        'fetch_format' => 'auto'
                    ]
                ]);
                $data['ImageUrl'] = $result['secure_url'];
            } catch (\Exception $e) {
                Log::error('Upload ảnh news thất bại: ' . $e->getMessage());
                return response()->json(['message' => 'Upload ảnh thất bại: ' . $e->getMessage()], 500);
            }
        } else {
            $data['ImageUrl'] = null;
        }

        $news = $this->newsService->create($data);
        return new NewsResource($news);
    }

    /**
     * Get a specific news article
     * 
     * Get detailed information about a specific news article.
     * 
     * @unauthenticated
     * 
     * @urlParam NewsId int required The ID of the news article. Example: 1
     * 
     * @response 200 {
     *   "NewsId": 1,
     *   "Title": "Summer Movie Festival 2024",
     *   "Content": "Join us for the biggest movie festival...",
     *   "ImageUrl": "https://res.cloudinary.com/.../news/summer-festival.jpg",
     *   "Author": "Admin",
     *   "Status": "Published",
     *   "ViewCount": 1500,
     *   "PublishedAt": "2024-06-01 10:00:00",
     *   "CreatedAt": "2024-06-01 09:00:00"
     * }
     * @response 404 {
     *   "message": "News not found"
     * }
     */
    public function show($NewsId)
    {
        $news = $this->newsService->getById($NewsId);
        return new NewsResource($news);
    }

    /**
     * Update a news article
     * 
     * Update an existing news article. Image upload is optional. (Staff/Admin only)
     * 
     * @authenticated
     * 
     * @urlParam NewsId int required The ID of the news article. Example: 1
     * @bodyParam Title string The news title. Example: Summer Movie Festival 2024 - Updated
     * @bodyParam Content string The news content. Example: Join us for the biggest movie festival...
     * @bodyParam ImageUrl file The news image (jpg, png, webp). Max 2MB.
     * @bodyParam Author string The author name. Example: Admin
     * @bodyParam Status string The status. Enum: Draft, Published, Archived. Example: Archived
     * @bodyParam PublishedAt string The publication date and time. Example: 2024-06-01 10:00:00
     * 
     * @response 200 {
     *   "NewsId": 1,
     *   "Title": "Summer Movie Festival 2024 - Updated",
     *   "Content": "Join us for the biggest movie festival...",
     *   "ImageUrl": "https://res.cloudinary.com/.../news/updated-image.jpg",
     *   "Status": "Archived"
     * }
     * @response 404 {
     *   "message": "News not found"
     * }
     * @response 500 {
     *   "message": "Upload ảnh thất bại: ..."
     * }
     */
    public function update(StoreNewsRequest $request, $NewsId)
    {
        $data = $request->validated();
        $news = $this->newsService->getById($NewsId); // Lấy từ Service

        if ($request->hasFile('ImageUrl')) {
            if ($news->ImageUrl && strpos($news->ImageUrl, 'cloudinary.com') !== false) {
                try {
                    $publicId = $this->extractPublicId($news->ImageUrl);
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
                    'folder' => 'news',
                    'transformation' => [
                        'width' => 800,
                        'height' => 450,
                        'crop' => 'fill',
                        'quality' => 'auto:best',
                        'fetch_format' => 'auto'
                    ]
                ]);
                $data['ImageUrl'] = $result['secure_url'];
            } catch (\Exception $e) {
                Log::error('Upload ảnh news thất bại: ' . $e->getMessage());
                return response()->json(['message' => 'Upload ảnh thất bại: ' . $e->getMessage()], 500);
            }
        } else {
            $data['ImageUrl'] = $news->ImageUrl;
        }

        $news = $this->newsService->update($NewsId, $data);
        return new NewsResource($news);
    }

    /**
     * Delete a news article
     * 
     * Delete a news article and remove its image from Cloudinary. (Staff/Admin only)
     * 
     * @authenticated
     * 
     * @urlParam NewsId int required The ID of the news article. Example: 1
     * 
     * @response 200 {
     *   "message": "News deleted successfully"
     * }
     * @response 404 {
     *   "message": "News not found"
     * }
     */
    public function destroy($NewsId)
    {
        $news = $this->newsService->getById($NewsId);
        
        if ($news && $news->ImageUrl && strpos($news->ImageUrl, 'cloudinary.com') !== false) {
            try {
                $publicId = $this->extractPublicId($news->ImageUrl);
                if ($publicId) {
                    $this->cloudinary->uploadApi()->destroy($publicId);
                }
            } catch (\Exception $e) {
                Log::error('Xóa ảnh Cloudinary thất bại: ' . $e->getMessage());
            }
        }
        
        $this->newsService->delete($NewsId);
        return response()->json(['message' => 'News deleted successfully']);
    }

    private function extractPublicId($url)
    {
        preg_match('/\/upload\/v\d+\/(.+)\.\w+$/', $url, $matches);
        return $matches[1] ?? null;
    }
}