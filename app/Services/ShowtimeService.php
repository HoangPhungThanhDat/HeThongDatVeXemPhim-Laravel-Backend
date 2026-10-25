<?php

namespace App\Services;

use App\Repositories\ShowtimeRepository;
use App\Services\ShowtimeseatService;
use Illuminate\Support\Facades\Auth;

class ShowtimeService
{
    protected $repository;
    protected $showtimeseatService;

    public function __construct(
        ShowtimeRepository $repository,
        ShowtimeseatService $showtimeseatService
    ) {
        $this->repository = $repository;
        $this->showtimeseatService = $showtimeseatService;
    }

    public function getAll()
    {
        return $this->repository->all();
    }

    /**
     * ✅ Hàm phân trang (Dùng Version để tự động xóa cache cũ)
     */
    public function getPaged($page, $limit, $search = '', $status = '')
    {
        // Gọi hàm getPagedWithVersion mới
        return $this->repository->getPagedWithVersion($page, $limit, $search, $status);
    }

    public function find($ShowtimeId)
    {
        return $this->repository->find($ShowtimeId);
    }

    public function create(array $data)
    {
        if (Auth::check()) {
            $data['CreatedBy'] = Auth::user()->UserId;
        }
        $data['CreatedAt'] = now();
        $data['UpdatedAt'] = now();

        $showtime = $this->repository->create($data);

        // Tự động tạo ghế cho suất chiếu mới
        $this->showtimeseatService->generateSeatsForShowtime(
            $showtime->ShowtimeId,
            $showtime->RoomId
        );

        return $showtime;
    }

    public function update($ShowtimeId, array $data)
    {
        $showtime = $this->repository->find($ShowtimeId);
        if (!$showtime) {
            return null;
        }

        if (Auth::check()) {
            $data['UpdatedBy'] = Auth::user()->UserId;
        }
        $data['UpdatedAt'] = now();
        
        return $this->repository->update($showtime, $data);
    }

    public function delete($ShowtimeId)
    {
        $showtime = $this->repository->find($ShowtimeId);
        if (!$showtime) {
            return null;
        }
        return $this->repository->delete($showtime);
    }
}