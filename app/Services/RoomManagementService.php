<?php

namespace App\Services;

use App\Repositories\RoomManagementRepository;
use Illuminate\Support\Facades\Log;

class RoomManagementService
{
    protected $repository;

    public function __construct(RoomManagementRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getDashboardData()
    {
        try {
            return $this->repository->getDashboardData();
        } catch (\Exception $e) {
            Log::error('RoomManagementService::getDashboardData', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Không thể tải dữ liệu quản lý phòng chiếu',
                'error' => $e->getMessage()
            ];
        }
    }

    public function getRoomDetail($roomId)
    {
        try {
            return $this->repository->getRoomDetail($roomId);
        } catch (\Exception $e) {
            Log::error('RoomManagementService::getRoomDetail', [
                'roomId' => $roomId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Không thể tải chi tiết phòng chiếu',
                'error' => $e->getMessage()
            ];
        }
    }

    public function getStatistics()
    {
        try {
            return $this->repository->getStatistics();
        } catch (\Exception $e) {
            Log::error('RoomManagementService::getStatistics', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Không thể tải thống kê',
                'error' => $e->getMessage()
            ];
        }
    }

    public function updateSeats($roomId, array $seats)
    {
        try {
            return $this->repository->updateSeats($roomId, $seats);
        } catch (\Exception $e) {
            Log::error('RoomManagementService::updateSeats', [
                'roomId' => $roomId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Không thể cập nhật sơ đồ ghế',
                'error' => $e->getMessage()
            ];
        }
    }
}