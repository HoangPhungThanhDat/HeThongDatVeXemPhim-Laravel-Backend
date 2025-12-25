<?php

namespace App\Http\Controllers\Api;

use App\Models\Cinema;
use Illuminate\Http\Request;
use App\Http\Requests\StoreCinemaRequest;
use App\Http\Resources\CinemaResource;
use App\Http\Controllers\Controller;
use App\Services\CinemaService;

class CinemaController extends Controller
{
    protected $cinemaService;

    public function __construct(CinemaService $cinemaService)
    {
        $this->cinemaService = $cinemaService;
    }

    public function index()
    {
        $cinemas = $this->cinemaService->getAll();
        return CinemaResource::collection($cinemas);
    }

    public function store(StoreCinemaRequest $request)
    {
         $data = $request->validated();

         if ($request->hasFile('ImageUrl')) {
    $file = $request->file('ImageUrl');
    // Tạo hash từ nội dung file
    $hash = md5_file($file->getRealPath());
    $extension = $file->getClientOriginalExtension();
    $fileName = $hash . '.' . $extension;
    // Tạo folder nếu chưa tồn tại
    if (!file_exists(storage_path('app/public/uploads/cinemas'))) {
        mkdir(storage_path('app/public/uploads/cinemas'), 0775, true);
    }
    // Nếu file chưa tồn tại → lưu mới
    if (!file_exists(storage_path('app/public/uploads/cinemas/' . $fileName))) {
        $file->storeAs('uploads/cinemas', $fileName, 'public');
    }

    $data['ImageUrl'] = asset('storage/uploads/cinemas/' . $fileName);
} else {
    // Nếu tồn tại thì không đổi ảnh → giữ ảnh cũ
    $data['ImageUrl'] = $cinema->ImageUrl ?? null;
}
           $cinema = $this->cinemaService->create($data);
        return new CinemaResource($cinema);
    }

    public function show($CinemaId)
    {
        $cinema = $this->cinemaService->getById($CinemaId);
        return new CinemaResource($cinema);
    }

    public function update(StoreCinemaRequest $request, $CinemaId)
    {
         $data = $request->validated();
    $cinema = $this->cinemaService->getById($CinemaId);

         if ($request->hasFile('ImageUrl')) {
        $file = $request->file('ImageUrl');
        $hash = md5_file($file->getRealPath());
        $extension = $file->getClientOriginalExtension();
        $fileName = $hash . '.' . $extension;

        $folderPath = storage_path('app/public/uploads/cinemas');
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0775, true);
        }

        if (!file_exists($folderPath . '/' . $fileName)) {
            $file->storeAs('uploads/cinemas', $fileName, 'public');
        }

        // Ghi đè ImageUrl trong data
        $data['ImageUrl'] = asset('storage/uploads/cinemas/' . $fileName);
    } else {
        // Nếu không upload mới, giữ nguyên ảnh cũ
        $data['ImageUrl'] = $cinema->ImageUrl;
    }

       $cinema = $this->cinemaService->update($CinemaId, $data);
        return new CinemaResource($cinema);
    }

    public function destroy($CinemaId)
    {
        $this->cinemaService->delete($CinemaId);
return response()->json(['message' => 'cinema deleted successfully']);
    }
}