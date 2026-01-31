<?php

namespace App\Http\Controllers\Api;

use App\Models\News;
use Illuminate\Http\Request;
use App\Http\Requests\StoreNewsRequest;
use App\Http\Resources\NewsResource;
use App\Http\Controllers\Controller;
use App\Services\NewsService;

class NewsController extends Controller
{
    protected $newsService;

    public function __construct(NewsService $newsService)
    {
        $this->newsService = $newsService;
    }

    public function index()
    {
        $newss = $this->newsService->getAll();
        return NewsResource::collection($newss);
    }

   public function store(StoreNewsRequest $request)
{
    $data = $request->validated();

    if ($request->hasFile('ImageUrl')) {
        $file = $request->file('ImageUrl');

        // Tạo hash từ nội dung file
        $hash = md5_file($file->getRealPath());
        $extension = $file->getClientOriginalExtension();
        $fileName = $hash . '.' . $extension;

        // Tạo folder nếu chưa tồn tại
        if (!file_exists(storage_path('app/public/uploads/news'))) {
            mkdir(storage_path('app/public/uploads/news'), 0775, true);
        }

        // Nếu file chưa tồn tại → lưu mới
        if (!file_exists(storage_path('app/public/uploads/news/' . $fileName))) {
            $file->storeAs('uploads/news', $fileName, 'public');
        }

        $data['ImageUrl'] = asset('storage/uploads/news/' . $fileName);
    } else {
        $data['ImageUrl'] = null;
    }

    $news = $this->newsService->create($data);

    return new NewsResource($news);
}


    public function show($NewsId)
    {
        $news = $this->newsService->getById($NewsId);
        return new NewsResource($news);
    }

  public function update(StoreNewsRequest $request, $NewsId)
{
    $data = $request->validated();
    $news = News::findOrFail($NewsId);

    if ($request->hasFile('ImageUrl')) {
        $file = $request->file('ImageUrl');

        // Tạo hash từ nội dung file
        $hash = md5_file($file->getRealPath());
        $extension = $file->getClientOriginalExtension();
        $fileName = $hash . '.' . $extension;

        $folderPath = storage_path('app/public/uploads/news');
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0775, true);
        }

        // Nếu file chưa tồn tại → lưu mới
        if (!file_exists($folderPath . '/' . $fileName)) {
            $file->storeAs('uploads/news', $fileName, 'public');
        }

        // Ghi đè ImageUrl trong data
        $data['ImageUrl'] = asset('storage/uploads/news/' . $fileName);
    } else {
        // Không upload mới → giữ ảnh cũ
        $data['ImageUrl'] = $news->ImageUrl;
    }

    $news = $this->newsService->update($NewsId, $data);

    return new NewsResource($news);
}

    public function destroy($NewsId)
    {
        $this->newsService->delete($NewsId);
        return response()->json(['message' => 'news deleted successfully']);
    }
}
