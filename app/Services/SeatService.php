<?php

namespace App\Services;

use App\Repositories\SeatRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SeatService
{
    protected $seatRepository;

    public function __construct(SeatRepository $seatRepository)
    {
        $this->seatRepository = $seatRepository;
    }

    public function getAll()
    {
        return $this->seatRepository->getAll();
    }

    public function getById($id)
    {
        return $this->seatRepository->getById($id);
    }

    public function create(array $data)
    {
        if (Auth::check()) {
            $data['CreatedBy'] = Auth::user()->UserId;
        }
        $data['CreatedAt'] = now();
        $data['UpdatedAt'] = now();
        
        DB::beginTransaction();
        try {
            $seat = $this->seatRepository->create($data);
            
            // Gọi xuống Repository để cập nhật số ghế
            $this->seatRepository->updateRoomSeatCount($data['RoomId']);
            
            DB::commit();
            return $seat;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function bulkCreate(array $seats)
    {
        $userId = Auth::check() ? Auth::user()->UserId : null;
        $now = Carbon::now();

        $seatsData = array_map(function ($seat) use ($userId, $now) {
            return [
                'RoomId' => $seat['RoomId'],
                'Row' => $seat['Row'],
                'Number' => $seat['Number'],
                'SeatType' => $seat['SeatType'],
                'Status' => $seat['Status'] ?? 'Available',
                'CreatedBy' => $userId,
                'UpdatedBy' => $userId,
                'CreatedAt' => $now,
                'UpdatedAt' => $now,
            ];
        }, $seats);

        DB::beginTransaction();
        try {
            $createdSeats = $this->seatRepository->bulkCreate($seatsData);
            
            $roomIds = array_unique(array_column($seatsData, 'RoomId'));
            foreach ($roomIds as $roomId) {
                $this->seatRepository->updateRoomSeatCount($roomId);
            }
            
            DB::commit();
            return $createdSeats;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update($id, array $data)
    {
        if (Auth::check()) {
            $data['UpdatedBy'] = Auth::user()->UserId;
        }
        $data['UpdatedAt'] = now();
        
        DB::beginTransaction();
        try {
            $seat = $this->seatRepository->getById($id);
            $oldRoomId = $seat->RoomId;
            
            $updatedSeat = $this->seatRepository->update($id, $data);
            
            if (isset($data['RoomId']) && $oldRoomId != $data['RoomId']) {
                $this->seatRepository->updateRoomSeatCount($oldRoomId);
                $this->seatRepository->updateRoomSeatCount($data['RoomId']);
            } else {
                $this->seatRepository->updateRoomSeatCount($oldRoomId);
            }
            
            DB::commit();
            return $updatedSeat;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $seat = $this->seatRepository->getById($id);
            $roomId = $seat->RoomId;
            
            $this->seatRepository->delete($id);
            
            // Cập nhật lại số ghế phòng
            $this->seatRepository->updateRoomSeatCount($roomId);
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteByRoomId($roomId)
    {
        DB::beginTransaction();
        try {
            // Gọi xuống Repository để xóa
            $this->seatRepository->deleteByRoomId($roomId);
            
            // Cập nhật lại số ghế phòng
            $this->seatRepository->updateRoomSeatCount($roomId);
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}