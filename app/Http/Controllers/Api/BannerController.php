<?php

namespace App\Http\Controllers\Api;

use App\Models\Banner;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBannerRequest;
use App\Http\Resources\BannerResource;
use App\Services\BannerService;

class BannerController extends Controller
{
    protected $bannerService;

    public function __construct(BannerService $bannerService)
    {
        $this->bannerService = $bannerService;
    }

    public function index()
    {
        $banners = $this->bannerService->getAll();
        return BannerResource::collection($banners);
    }

    public function store(StoreBannerRequest $request)
{
    $data = $request->validated();

   if ($request->hasFile('ImageUrl')) {
    $file = $request->file('ImageUrl');
    // Tạo hash từ nội dung file
    $hash = md5_file($file->getRealPath());
    $extension = $file->getClientOriginalExtension();
    $fileName = $hash . '.' . $extension;
    // Tạo folder nếu chưa tồn tại
    if (!file_exists(storage_path('app/public/uploads/banners'))) {
        mkdir(storage_path('app/public/uploads/banners'), 0775, true);
    }
    // Nếu file chưa tồn tại → lưu mới
    if (!file_exists(storage_path('app/public/uploads/banners/' . $fileName))) {
        $file->storeAs('uploads/banners', $fileName, 'public');
    }

    $data['ImageUrl'] = asset('storage/uploads/banners/' . $fileName);
} else {
    // Nếu tồn tại thì không đổi ảnh → giữ ảnh cũ
    $data['ImageUrl'] = $banner->ImageUrl ?? null;
}

    $banner = $this->bannerService->create($data);

    return new BannerResource($banner);
}


    public function show($BannerId)
    {
        $banner = $this->bannerService->getById($BannerId);
        return new BannerResource($banner);
    }

  public function update(StoreBannerRequest $request, $BannerId)
{
    $data = $request->validated();
    $banner = Banner::findOrFail($BannerId);

    if ($request->hasFile('ImageUrl')) {
        $file = $request->file('ImageUrl');
        $hash = md5_file($file->getRealPath());
        $extension = $file->getClientOriginalExtension();
        $fileName = $hash . '.' . $extension;

        $folderPath = storage_path('app/public/uploads/banners');
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0775, true);
        }

        if (!file_exists($folderPath . '/' . $fileName)) {
            $file->storeAs('uploads/banners', $fileName, 'public');
        }

        // Ghi đè ImageUrl trong data
        $data['ImageUrl'] = asset('storage/uploads/banners/' . $fileName);
    } else {
        // Nếu không upload mới, giữ nguyên ảnh cũ
        $data['ImageUrl'] = $banner->ImageUrl;
    }

    $banner = $this->bannerService->update($BannerId, $data);
    return new BannerResource($banner);
}


    public function destroy($BannerId)
    {
        $this->bannerService->delete($BannerId);
        return response()->json(['message' => 'Banner deleted successfully']);
    }
}