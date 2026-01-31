<?php

namespace App\Services;

use App\Repositories\SeatRepository;
use App\Models\Room;
use App\Models\Seat;
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

    /**
     * Tạo ghế đơn lẻ
     */
    public function create(array $data)
    {
        $data['CreatedBy'] = Auth::user()->UserId;
        
        DB::beginTransaction();
        try {
            // Tạo ghế
            $seat = $this->seatRepository->create($data);
            
            // Cập nhật SeatCount của phòng
            $this->updateRoomSeatCount($data['RoomId']);
            
            DB::commit();
            
            Log::info("Created seat {$seat->SeatId} for Room {$data['RoomId']}");
            
            return $seat;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error creating seat: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Tạo nhiều ghế cùng lúc
     */
    public function bulkCreate(array $seats)
    {
        $userId = Auth::user()->UserId;
        $now = Carbon::now();

        // Thêm CreatedBy, UpdatedBy, CreatedAt, UpdatedAt cho mỗi ghế
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
            // Tạo tất cả ghế
            $createdSeats = $this->seatRepository->bulkCreate($seatsData);
            
            // Lấy danh sách RoomId bị ảnh hưởng (loại bỏ trùng lặp)
            $roomIds = array_unique(array_column($seatsData, 'RoomId'));
            
            // Cập nhật SeatCount cho tất cả các phòng liên quan
            foreach ($roomIds as $roomId) {
                $this->updateRoomSeatCount($roomId);
            }
            
            DB::commit();
            
            Log::info("Bulk created " . count($createdSeats) . " seats for rooms: " . implode(', ', $roomIds));
            
            return $createdSeats;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error bulk creating seats: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cập nhật ghế
     */
    public function update($id, array $data)
    {
        $data['UpdatedBy'] = Auth::user()->UserId;
        
        DB::beginTransaction();
        try {
            // Lấy thông tin ghế cũ
            $seat = $this->seatRepository->getById($id);
            $oldRoomId = $seat->RoomId;
            
            // Cập nhật ghế
            $updatedSeat = $this->seatRepository->update($id, $data);
            
            // Kiểm tra nếu ghế đổi phòng
            if (isset($data['RoomId']) && $oldRoomId != $data['RoomId']) {
                // Cập nhật cả phòng cũ và phòng mới
                $this->updateRoomSeatCount($oldRoomId);
                $this->updateRoomSeatCount($data['RoomId']);
                
                Log::info("Seat {$id} moved from Room {$oldRoomId} to Room {$data['RoomId']}");
            } else {
                // Chỉ cập nhật phòng hiện tại
                $this->updateRoomSeatCount($oldRoomId);
            }
            
            DB::commit();
            return $updatedSeat;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error updating seat {$id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Xóa ghế
     */
    public function delete($id)
    {
        DB::beginTransaction();
        try {
            // Lấy thông tin ghế trước khi xóa
            $seat = $this->seatRepository->getById($id);
            $roomId = $seat->RoomId;
            
            // Xóa ghế
            $this->seatRepository->delete($id);
            
            // Cập nhật SeatCount của phòng
            $this->updateRoomSeatCount($roomId);
            
            DB::commit();
            
            Log::info("Deleted seat {$id} from Room {$roomId}");
            
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error deleting seat {$id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Xóa tất cả ghế của một phòng
     */
    public function deleteByRoomId($roomId)
    {
        DB::beginTransaction();
        try {
            // Xóa tất cả ghế của phòng
            Seat::where('RoomId', $roomId)->delete();
            
            // Cập nhật SeatCount về 0
            $this->updateRoomSeatCount($roomId);
            
            DB::commit();
            
            Log::info("Deleted all seats from Room {$roomId}");
            
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error deleting seats from Room {$roomId}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cập nhật số ghế của phòng
     * @param int $roomId
     * @return void
     */
    private function updateRoomSeatCount($roomId)
    {
        try {
            $room = Room::find($roomId);
            
            if ($room) {
                // Đếm số ghế thực tế trong database
                $seatCount = Seat::where('RoomId', $roomId)->count();
                
                // Cập nhật SeatCount
                $room->SeatCount = $seatCount;
                $room->save();
                
                Log::info("Updated SeatCount for Room {$roomId}: {$seatCount} seats");
            } else {
                Log::warning("Room {$roomId} not found when updating SeatCount");
            }
        } catch (\Exception $e) {
            Log::error("Error updating SeatCount for Room {$roomId}: " . $e->getMessage());
            throw $e;
        }
    }
}