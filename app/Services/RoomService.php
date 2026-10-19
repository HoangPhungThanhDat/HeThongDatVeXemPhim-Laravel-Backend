<?php

namespace App\Services;

use App\Repositories\RoomRepository;
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
        if (Auth::check()) {
            $data['CreatedBy'] = Auth::user()->UserId;
        }
        $data['SeatCount'] = 0;
        $data['CreatedAt'] = now();
        $data['UpdatedAt'] = now();
        
        return $this->roomRepository->create($data);
    }

    public function update($id, array $data)
    {
        if (Auth::check()) {
            $data['UpdatedBy'] = Auth::user()->UserId;
        }
        $data['UpdatedAt'] = now();
        
        // Không cho phép cập nhật SeatCount thủ công
        unset($data['SeatCount']);
        
        return $this->roomRepository->update($id, $data);
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            // ✅ Gọi xuống Repository để xóa ghế (tuân thủ MVC)
            $this->roomRepository->deleteSeatsByRoomId($id);
            
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