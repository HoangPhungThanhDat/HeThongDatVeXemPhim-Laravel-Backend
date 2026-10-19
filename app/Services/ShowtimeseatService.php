<?php

namespace App\Services;

use App\Repositories\ShowtimeseatRepository;
use Illuminate\Support\Facades\Auth;

class ShowtimeseatService
{
    protected $showtimeseatRepository;

    public function __construct(ShowtimeseatRepository $showtimeseatRepository)
    {
        $this->showtimeseatRepository = $showtimeseatRepository;
    }

    public function getAll()
    {
        return $this->showtimeseatRepository->getAll();
    }

    /**
     * ✅ Hàm phân trang
     */
    public function getPaged($page, $limit, $showtimeId = '', $status = '')
    {
        return $this->showtimeseatRepository->getPaged($page, $limit, $showtimeId, $status);
    }

    public function getById($id)
    {
        return $this->showtimeseatRepository->getById($id);
    }

    public function create(array $data)
    {
        if (Auth::check()) {
            $data['CreatedBy'] = Auth::user()->UserId;
        }
        $data['CreatedAt'] = now();
        $data['UpdatedAt'] = now();
        return $this->showtimeseatRepository->create($data);
    }

    public function update($id, array $data)
    {
        if (Auth::check()) {
            $data['UpdatedBy'] = Auth::user()->UserId;
        }
        $data['UpdatedAt'] = now();
        return $this->showtimeseatRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->showtimeseatRepository->delete($id);
    }

    public function getSeatsByShowtimeId($showtimeId)
    {
        return $this->showtimeseatRepository->getSeatsByShowtimeId($showtimeId);
    }

    public function generateSeatsForShowtime($showtimeId)
    {
        $showtime = $this->showtimeseatRepository->getShowtime($showtimeId);
        if (!$showtime) {
            return ['error' => true, 'message' => 'Không tìm thấy suất chiếu'];
        }

        $createdBy = Auth::check() ? Auth::user()->UserId : null;
        $this->showtimeseatRepository->generateSeatsForShowtime(
            $showtime->ShowtimeId,
            $showtime->RoomId,
            $createdBy
        );
        
        return ['success' => true];
    }
}