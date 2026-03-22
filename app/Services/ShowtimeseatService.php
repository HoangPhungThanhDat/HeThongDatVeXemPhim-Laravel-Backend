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

    public function getById($id)
    {
        return $this->showtimeseatRepository->getById($id);
    }

    public function create(array $data)
    {
        $data['CreatedBy'] = Auth::user()->UserId;
        return $this->showtimeseatRepository->create($data);
    }

    public function update($id, array $data)
    {
        $data['UpdatedBy'] = Auth::user()->UserId;
        return $this->showtimeseatRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->showtimeseatRepository->delete($id);
    }

    // ✅ THÊM MỚI: Lấy ghế theo ShowtimeId
    public function getSeatsByShowtimeId($showtimeId)
    {
        return $this->showtimeseatRepository->getSeatsByShowtimeId($showtimeId);
    }

    // ✅ THÊM MỚI: Auto-generate ghế khi tạo showtime
    public function generateSeatsForShowtime($showtimeId, $roomId)
    {
        $createdBy = Auth::check() ? Auth::user()->UserId : null;
        $this->showtimeseatRepository->generateSeatsForShowtime($showtimeId, $roomId, $createdBy);
    }
}