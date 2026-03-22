<?php

// namespace App\Services;

// use App\Repositories\ShowtimeRepository;
// use Illuminate\Support\Facades\Auth;
// class ShowtimeService
// {
//     protected $repository;

//     public function __construct(ShowtimeRepository $repository)
//     {
//         $this->repository = $repository;
//     }

//     public function getAll()
//     {
//         return $this->repository->all();
//     }

//     public function find($ShowtimeId)
//     {
//         return $this->repository->find($ShowtimeId);
//     }

//     public function create(array $data)
//     {
//         $data['CreatedBy'] = Auth::user()->UserId;
//         return $this->repository->create($data);
//     }

//     public function update($ShowtimeId, array $data)
//     {
//         $data['UpdatedBy'] = Auth::user()->UserId;
//         $showtime = $this->repository->find($ShowtimeId);
//         if (!$showtime) {
//             return null;
//         }
//         return $this->repository->update($showtime, $data);
//     }

//     public function delete($ShowtimeId)
//     {
//         $showtime = $this->repository->find($ShowtimeId);
//         if (!$showtime) {
//             return null;
//         }
//         return $this->repository->delete($showtime);
//     }
// }



















namespace App\Services;

use App\Repositories\ShowtimeRepository;
use Illuminate\Support\Facades\Auth;

class ShowtimeService
{
    protected $repository;
    protected $showtimeseatService; // ✅ THÊM

    public function __construct(
        ShowtimeRepository $repository,
        ShowtimeseatService $showtimeseatService // ✅ THÊM
    ) {
        $this->repository = $repository;
        $this->showtimeseatService = $showtimeseatService; // ✅ THÊM
    }

    public function getAll()
    {
        return $this->repository->all();
    }

    public function find($ShowtimeId)
    {
        return $this->repository->find($ShowtimeId);
    }

    public function create(array $data)
    {
        $data['CreatedBy'] = Auth::user()->UserId;
        $showtime = $this->repository->create($data);

        // ✅ THÊM: Auto tạo ghế cho suất chiếu mới
        $this->showtimeseatService->generateSeatsForShowtime(
            $showtime->ShowtimeId,
            $showtime->RoomId
        );

        return $showtime;
    }

    public function update($ShowtimeId, array $data)
    {
        $data['UpdatedBy'] = Auth::user()->UserId;
        $showtime = $this->repository->find($ShowtimeId);
        if (!$showtime) {
            return null;
        }
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