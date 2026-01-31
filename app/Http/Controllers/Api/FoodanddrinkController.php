<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFoodanddrinkRequest;
use App\Http\Resources\FoodanddrinkResource;
use App\Services\FoodanddrinkService;
use App\Models\Foodanddrink;

class FoodanddrinkController extends Controller
{
    protected $service;

    public function __construct(FoodanddrinkService $service)
    {
        $this->service = $service;
        $this->middleware(['auth:api', 'checkrole:Admin'])->only(['store', 'update', 'destroy']);
    }

    // lấy tất cả
    public function index()
    {
        return FoodanddrinkResource::collection($this->service->getAll());
    }
    // thêm

    public function store(StoreFoodanddrinkRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('ImageUrl')) {
            $file = $request->file('ImageUrl');
            // Tạo hash từ nội dung file
            $hash = md5_file($file->getRealPath());
            $extension = $file->getClientOriginalExtension();
            $fileName = $hash.'.'.$extension;
            // Tạo folder nếu chưa tồn tại
            if (! file_exists(storage_path('app/public/uploads/foodanddrinks'))) {
                mkdir(storage_path('app/public/uploads/foodanddrinks'), 0775, true);
            }
            // Nếu file chưa tồn tại → lưu mới
            if (! file_exists(storage_path('app/public/uploads/foodanddrinks/'.$fileName))) {
                $file->storeAs('uploads/foodanddrinks', $fileName, 'public');
            }

            $data['ImageUrl'] = asset('storage/uploads/foodanddrinks/'.$fileName);
        } else {
            // Nếu tồn tại thì không đổi ảnh → giữ ảnh cũ
            $data['ImageUrl'] = $foodanddrink->ImageUrl ?? null;
        }

        $foodanddrink = $this->service->create($data);

        return new FoodanddrinkResource($foodanddrink);
    }

    // hiện 1
    public function show($ItemId)
    {
        $foodanddrink = $this->service->find($ItemId);
        if (! $foodanddrink) {
            return response()->json(['message' => 'Food and drink item not found'], 404);
        }

        return new FoodanddrinkResource($foodanddrink);
    }

    // cập nhật
    public function update(StoreFoodanddrinkRequest $request, $ItemId)
    {
        $data = $request->validated();
        $foodanddrink = Foodanddrink::findOrFail($ItemId);

        if ($request->hasFile('ImageUrl')) {
            $file = $request->file('ImageUrl');
            $hash = md5_file($file->getRealPath());
            $extension = $file->getClientOriginalExtension();
            $fileName = $hash.'.'.$extension;

            $folderPath = storage_path('app/public/uploads/foodanddrinks');
            if (! file_exists($folderPath)) {
                mkdir($folderPath, 0775, true);
            }

            if (! file_exists($folderPath.'/'.$fileName)) {
                $file->storeAs('uploads/foodanddrinks', $fileName, 'public');
            }

            // Ghi đè ImageUrl trong data
            $data['ImageUrl'] = asset('storage/uploads/foodanddrinks/'.$fileName);
        } else {
            // Nếu không upload mới, giữ nguyên ảnh cũ
            $data['ImageUrl'] = $foodanddrink->ImageUrl;
        }

        $foodanddrink = $this->service->update($ItemId, $data);

        return new FoodanddrinkResource($foodanddrink);
    }

    // xoá
    public function destroy($ItemId)
    {
        $deleted = $this->service->delete($ItemId);
        if (! $deleted) {
            return response()->json(['message' => 'Audit log not found'], 404);
        }

        return response()->json(['message' => 'Audit log deleted successfully']);
    }
}
