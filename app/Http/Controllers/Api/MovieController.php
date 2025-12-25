<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMovieRequest;
use App\Http\Resources\MovieResource;
use App\Services\MovieService;

class MovieController extends Controller
{
    protected $service;

    public function __construct(MovieService $service)
    {
        $this->service = $service;
        $this->middleware(['auth:api','checkrole:Admin'])->only(['store','update','destroy']);
    }


    //lấy tất cả 
    public function index()
    {
        return MovieResource::collection($this->service->getAll());
    }
    //thêm

   public function store(StoreMovieRequest $request)
{
    // Lấy dữ liệu validate
    $data = $request->validated();

    // ✅ Nếu có file PosterUrl thì xử lý trước
    if ($request->hasFile('PosterUrl')) {
        $file = $request->file('PosterUrl');
        $hash = md5_file($file->getRealPath());
        $extension = $file->getClientOriginalExtension();
        $fileName = $hash . '.' . $extension;

        // Tạo thư mục nếu chưa có
        $folderPath = storage_path('app/public/uploads/movies');
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0775, true);
        }

        // Nếu chưa có file thì lưu mới
        if (!file_exists($folderPath . '/' . $fileName)) {
            $file->storeAs('uploads/movies', $fileName, 'public');
        }

        // ✅ Gán lại đường dẫn public vào $data
        $data['PosterUrl'] = asset('storage/uploads/movies/' . $fileName);
    } else {
        $data['PosterUrl'] = null;
    }

    // Tính trạng thái phim dựa vào ngày phát hành
    $today = now()->startOfDay();
    $releaseDate = \Carbon\Carbon::parse($data['ReleaseDate'])->startOfDay();

    if ($releaseDate->gt($today)) {
        $data['Status'] = 'ComingSoon';
    } else {
        $data['Status'] = 'NowShowing';
    }

    // ✅ Bây giờ mới tạo movie với dữ liệu chính xác
    $movie = $this->service->create($data);

     // ✅ Nếu muốn kiểm tra suất chiếu sau khi tạo
    $hasShowtime = $movie->showtimes()->where('StartTime', '>=', now())->exists();
    if (!$hasShowtime && $releaseDate->lt($today)) {
        $movie->Status = 'Ended';
        $movie->save();
    }


    return new MovieResource($movie);
}

   //hiện 1
    public function show($MovieId)
    {
        $movie = $this->service->find($MovieId);
        if (!$movie) {
            return response()->json(['message' => 'Movie not found'], 404);
        }
        return new MovieResource($movie);
    }
  //cập nhật
   public function update(StoreMovieRequest $request, $MovieId)
{
    // Lấy dữ liệu đã validated
    $data = $request->validated();

    // Tìm movie cần cập nhật
    $movie = $this->service->find($MovieId);
    if (!$movie) {
        return response()->json(['message' => 'Movie not found'], 404);
    }

    // ✅ Xử lý upload Poster mới nếu có
if ($request->hasFile('PosterUrl')) {
        $file = $request->file('PosterUrl');
        $hash = md5_file($file->getRealPath());
        $extension = $file->getClientOriginalExtension();
        $fileName = $hash . '.' . $extension;

        $folderPath = storage_path('app/public/uploads/movies');
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0775, true);
        }

        // Nếu file chưa tồn tại → lưu mới
        if (!file_exists($folderPath . '/' . $fileName)) {
            $file->storeAs('uploads/movies', $fileName, 'public');
        }

        // Gán đường dẫn ảnh mới vào $data
        $data['PosterUrl'] = asset('storage/uploads/movies/' . $fileName);
    } else {
        // Nếu không upload mới → giữ nguyên ảnh cũ
        $data['PosterUrl'] = $movie->PosterUrl;
    }

    // ✅ Tính lại trạng thái dựa vào ngày phát hành mới
    $today = now()->startOfDay();
    $releaseDate = \Carbon\Carbon::parse($data['ReleaseDate'])->startOfDay();

    if ($releaseDate->gt($today)) {
        $data['Status'] = 'ComingSoon';
    } else {
        $data['Status'] = 'NowShowing';
    }

    // ✅ Cập nhật phim
    $movie->update($data);

    // ✅ Nếu không còn suất chiếu tương lai & ngày phát hành < hôm nay → kết thúc
    $hasShowtime = $movie->showtimes()->where('StartTime', '>=', now())->exists();
    if (!$hasShowtime && $releaseDate->lt($today)) {
        $movie->Status = 'Ended';
        $movie->save();
    }

    return new MovieResource($movie);
}

   //xoá
    public function destroy($MovieId)
    {
        $deleted = $this->service->delete($MovieId);
        if (!$deleted) {
            return response()->json(['message' => 'Movie not found'], 404);
        }
        return response()->json(['message' => 'Movie deleted successfully']);
    }
}
