<?php

namespace App\Services;

use App\Repositories\RoomRepository;
use App\Models\Seat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoomService
{
    protected $roomRepository;

    public function __construct(RoomRepository $roomRepository)
    {
        $this->roomRepository = $roomRepository;
    }

    public function getAll()
    {
        return $this->roomRepository->getAll();
    }

    public function getById($id)
    {
        return $this->roomRepository->getById($id);
    }

    public function create(array $data)
    {
        $data['CreatedBy'] = Auth::user()->UserId;
        // SeatCount mặc định là 0 khi tạo phòng mới
        $data['SeatCount'] = 0;
        
        return $this->roomRepository->create($data);
    }

    public function update($id, array $data)
    {
        $data['UpdatedBy'] = Auth::user()->UserId;
        
        // Không cho phép cập nhật SeatCount thủ công
        // SeatCount sẽ được tự động cập nhật khi thêm/xóa ghế
        unset($data['SeatCount']);
        
        return $this->roomRepository->update($id, $data);
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            // Xóa tất cả ghế của phòng trước
            Seat::where('RoomId', $id)->delete();
            
            // Sau đó xóa phòng
            $this->roomRepository->delete($id);
            
            DB::commit();
            
            Log::info("Deleted room {$id} and all its seats");
            
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error deleting room {$id}: " . $e->getMessage());
            throw $e;
        }
    }
}