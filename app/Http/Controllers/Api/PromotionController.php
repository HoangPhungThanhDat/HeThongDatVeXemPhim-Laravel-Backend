<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePromotionRequest;
use App\Http\Resources\PromotionResource;
use App\Services\PromotionService;
use App\Models\Promotion;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    protected $service;

    public function __construct(PromotionService $service)
    {
        $this->service = $service;
        $this->middleware(['auth:api', 'checkrole:Admin'])->only(['store', 'update', 'destroy']);
    }

    // Lấy tất cả
    public function index()
    {
        return PromotionResource::collection($this->service->getAll());
    }

    // ✅ Phân trang server-side
    public function getPaged(Request $request)
    {
        $page   = max(1, (int) $request->query('page',  1));
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

    // Thêm
    public function store(StorePromotionRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('ImageUrl')) {
            $file      = $request->file('ImageUrl');
            $hash      = md5_file($file->getRealPath());
            $extension = $file->getClientOriginalExtension();
            $fileName  = $hash . '.' . $extension;

            $folderPath = storage_path('app/public/uploads/promotions');
            if (!file_exists($folderPath)) {
                mkdir($folderPath, 0775, true);
            }

            if (!file_exists($folderPath . '/' . $fileName)) {
                $file->storeAs('uploads/promotions', $fileName, 'public');
            }

            $data['ImageUrl'] = asset('storage/uploads/promotions/' . $fileName);
        }

        $promotion = $this->service->create($data);
        return new PromotionResource($promotion);
    }

    // Hiện 1
    public function show($PromotionId)
    {
        $promotion = $this->service->find($PromotionId);
        if (!$promotion) {
            return response()->json(['message' => 'Promotion not found'], 404);
        }
        return new PromotionResource($promotion);
    }

    // Cập nhật
    public function update(StorePromotionRequest $request, $PromotionId)
    {
        $promotion = Promotion::findOrFail($PromotionId);
        $data      = $request->validated();

        if ($request->hasFile('ImageUrl')) {
            $file      = $request->file('ImageUrl');
            $hash      = md5_file($file->getRealPath());
            $extension = $file->getClientOriginalExtension();
            $fileName  = $hash . '.' . $extension;

            $folderPath = storage_path('app/public/uploads/promotions');
            if (!file_exists($folderPath)) {
                mkdir($folderPath, 0775, true);
            }

            if (!file_exists($folderPath . '/' . $fileName)) {
                $file->storeAs('uploads/promotions', $fileName, 'public');
            }

            $data['ImageUrl'] = asset('storage/uploads/promotions/' . $fileName);
        } else {
            $data['ImageUrl'] = $promotion->ImageUrl;
        }

        $promotion = $this->service->update($PromotionId, $data);
        return new PromotionResource($promotion);
    }

    // Xoá
    public function destroy($PromotionId)
    {
        $deleted = $this->service->delete($PromotionId);
        if (!$deleted) {
            return response()->json(['message' => 'Promotion not found'], 404);
        }
        return response()->json(['message' => 'Promotion deleted successfully']);
    }
}